<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookRequest extends Model
{
    protected $fillable =[
        'studentID',
        'curriculumID'
    ];

    public function student(){
        return $this->belongsTo(Student::class, "studentID");
    }

    public function curriculum(){
        return $this->belongsTo(Curriculum::class, "curriculumID");
    }

}
