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
}
