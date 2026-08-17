<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'title',
        'description',
        'categoryId',
        'imageUri',
        'latitude',
        'longitude',
        'address',
        'priority',
        'status',
        'user_id',
        'authorName',
        'authorPoints',
        'timestamp'
    ];

    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}