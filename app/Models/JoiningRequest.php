<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoiningRequest extends Model
{
     protected $fillable = [
        'studentID',
        'courseID',
        'status'

    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }
}
