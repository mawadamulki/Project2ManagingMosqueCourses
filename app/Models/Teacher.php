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

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class,'teacherID');
    }

}
