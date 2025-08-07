<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'teacherID',
        'studentID',
        'questionID',
        'answer'
    ];

    public function student(){
        return $this->belongsTo(Student::class,'studentID');
    }

    public function teacher(){
        return $this->belongsTo(Teacher::class,'teacherID');
    }

    public function questions(){
        return $this->belongsTo(Question::class, 'questionID');
    }


}
