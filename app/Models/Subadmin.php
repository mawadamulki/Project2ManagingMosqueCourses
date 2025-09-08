<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subadmin extends Model
{
 protected $fillable = [
        'userID',
        'studyOrCareer',
        'magazeh',
        'PreviousExperience',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

}
