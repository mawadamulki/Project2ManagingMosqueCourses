<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $fillable = [
        'subjectID',
        'studentID',
        'date'
    ];

    public function subject(){
        return $this->belongsTo(Subject::class, 'subjectID');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'studentID');
    }

}
