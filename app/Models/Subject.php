<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable=[
        'subjectName',
        'teacherID',
        'levelID'
    ];

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function level(){
        return $this->belongsTo(Level::class,"levelID");
    }

    public function teacher(){
        return $this->belongsTo(Teacher::class,"teacherID");
    }

}
