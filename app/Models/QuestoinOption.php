<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestoinOption extends Model
{
    protected $fillable = [
        'questionID',
        'option'
    ];

    public function questions(){
        return $this->belongsTo(Question::class, 'questionID');
    }
}
