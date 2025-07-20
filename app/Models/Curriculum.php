<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $fillable = [
        'subjectID',
        'curriculumFile',
        'curriculumName'
    ];

    public function subject(){
        return $this->belongsTo(Subject::class, 'subjectID');
    }

    public function bookRequest(){
        return $this->hasMany(BookRequest::class, 'curriculumID');
    }

}
