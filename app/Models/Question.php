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

    public function worksheet(){
        return $this->belongsTo(Worksheet::class ,'worksheetID');
    }

}
