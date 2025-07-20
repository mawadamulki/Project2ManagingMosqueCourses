<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    protected $fillable = [
        'subjectID',
        'extensionFile',
        'extensionName'
    ];

    public function subject(){
        return $this->belongsTo(Subject::class, "subjectID");
    }
}
