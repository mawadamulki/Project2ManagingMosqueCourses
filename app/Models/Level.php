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
        return $this->belongsTo(Course::class,"courseID");
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

public function curriculumPlans()
{
    return $this->hasMany(CurriculumPlan::class);
}

}
