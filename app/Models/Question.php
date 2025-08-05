<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'wordsheetID',
        'type',
        'question'
    ];

    public function worksheets(){
        return $this->belongsTo(Worksheet::class ,'worksheetID');
    }

    public function questionOptions(){
        return $this->hasMany(QuestionOption::class, 'questionID');
    }

}
