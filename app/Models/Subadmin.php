<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subadmin extends Model
{
 protected $fillable = [
        'user_id',
        'studyOrCareer',
        'magazeh',
        'PreviousExperience',
    ];
}
