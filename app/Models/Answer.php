<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'teacherID',
        'studentID',
        'answer'
    ];

    public function students(){
        return $this->belongsTo(Student::class,'studentID');
    }

    public function teachers(){
        return $this->belongsTo(Teacher::class,'teacherID');
    }


}
