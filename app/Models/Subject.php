<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable=[
        'subjectName',
        'course_id'
    ];
    public function course()
{
    return $this->belongsTo(Course::class);
}

public function marks()
{
    return $this->hasMany(Mark::class);
}

}
