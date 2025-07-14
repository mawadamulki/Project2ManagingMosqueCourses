<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'userProfiles';

    protected $fillable = [
        'userID',
        'profile_image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
