<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
 protected $fillable = [
        'user_id',
        'studyOrCareer',
        'magazeh',
        'PreviousExperience',
    ];
}
