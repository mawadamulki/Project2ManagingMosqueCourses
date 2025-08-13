<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'courseID',
        'levelName'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseID');
    }

    public function subject()
    {
        return $this->hasMany(Subject::class);
    }

    public function student()
    {
        return $this->belongsToMany(Student::class ,'level_student_pivot', 'levelID', 'studentID');
    }

    public function curriculumPlan()
    {
        return $this->hasMany(CurriculumPlan::class, 'levelID');
    }


}
