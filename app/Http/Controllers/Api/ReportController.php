<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('user')->orderBy('created_at', 'desc')->get();

        $reports->each(function ($report) {
            if ($report->user) {
                $report->authorPoints = $report->user->totalPoints;
            }
        });

        return response()->json($reports);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $report = Report::create([
            'title' => $request->title,
            'description' => $request->description,
            'categoryId' => $request->categoryId,
            'imageUri' => $request->imageUri,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'priority' => $request->priority,
            'authorName' => $user->nom,
            'authorPoints' => $user->totalPoints,
            'user_id' => $user->id,
            'timestamp' => round(microtime(true) * 1000),
        ]);

        $user->increment('totalPoints', 50);

        return response()->json($report, 201);
    }

    // MISE À JOUR + NOTIFICATION AUTOMATIQUE
    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $oldStatus = $report->status;

        $report->update($request->all());

        // SI LE STATUT CHANGE -> ENVOI NOTIF
        if ($oldStatus !== $report->status) {
            $user = $report->user;
            if ($user && $user->fcm_token) {
                $messaging = app('firebase.messaging');

                $message = CloudMessage::fromArray([
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => 'Mise à jour de signalement',
                        'body' => "Bonjour {$report->authorName} ! votre incident '{$report->title}' est désormais : {$report->status}",
                    ],
                    'data' => [
                        'report_id' => (string) $id,
                    ],
                ]);

                try {
                    $messaging->send($message);
                } catch (\Exception $e) {
                    Log::error("Erreur FCM : " . $e->getMessage());
                }
            }
        }

        return response()->json($report);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        if ($report->user_id == auth('api')->id() || auth('api')->user()->role == 'ADMIN') {
            $report->delete();
            return response()->json(['message' => 'Supprimé']);
        }
        return response()->json(['error' => 'Non autorisé'], 403);
    }
}
