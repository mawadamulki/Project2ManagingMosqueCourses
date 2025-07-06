<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'userID',
        'studyOrCareer',
        'magazeh',
        'PreviousExperience',
    ];
 public function subjects(){
    return $this->hasMany(Subject::class,'teacherID');
 }

}
