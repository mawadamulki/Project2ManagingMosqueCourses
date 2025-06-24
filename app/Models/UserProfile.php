<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{

protected $table = 'userProfiles';
    protected $fillable = [
        'user_id',
        'profile_image'

    ];
public function user()
{
    return $this->belongsTo(User::class);
}


}
