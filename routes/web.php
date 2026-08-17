<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


// DEBUG ROUTE POUR TESTER LES NOTIFICATIONS FCM
Route::get('/test-notif', function () {
    $token = "YOUR_FCM_DEVICE_TOKEN_HERE (devant le nom de l'utilisateur dans la base de données)";
    $messaging = app('firebase.messaging');
    $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
        'token' => $token,
        'notification' => [
            'title' => 'Test Direct Laravel',
            'body' => 'Laravel est bien configuré !'
        ]
    ]);

    try {
        $messaging->send($message);
        return "Message envoyé !";
    } catch (\Exception $e) {
        return "Erreur : " . $e->getMessage();
    }
});
