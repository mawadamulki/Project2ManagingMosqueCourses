<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $fillable = [
        'subjectID',
        'curriculumFile'
    ];

    public function subject(){
        return $this->belongsTo(Subject::class, 'subjectID');
    }

}
