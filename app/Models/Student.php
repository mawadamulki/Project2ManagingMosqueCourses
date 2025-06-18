<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable=[
        'user_id',
        'firstAndLastName',
        'fatherName',
        'phoneNumber',
        'password',
        'birthDate',
        'address',
        'studyOrCareer',
        'magazeh',
        'PreviousCoursesInOtherPlace',
        'isPreviousStudent',
        'previousCourses'
    ];



     public function courses()
{
    return $this->belongsToMany(Course::class);
}

public function marks()
{
    return $this->hasMany(Mark::class);
}
}
