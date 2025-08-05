<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worksheet extends Model
{
    protected $fillable = [
        'subjectID',
        'worksheetName'
    ];

    public function subject(){
        return $this->belongsTo(Subject::class ,'subjectID');
    }

    public function questions(){
        return $this->hasMany(Question::class, 'worksheetID');
    }
}
