<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable=[
        'userID',
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

    public function levels()
    {
        return $this->belongsToMany(Level::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function bookRequest()
    {
        return $this->hasMany(BookRequest::class, 'studentID');
    }


}
