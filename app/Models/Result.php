<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
        'subjectID',
        'studentID',
        'test',
        'exam',
        'presenceMark',
        'total',
        'status'
    ];

    public function student(){
        return $this->belongsTo(Student::class, 'studentID');
    }
}
