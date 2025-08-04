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
}
