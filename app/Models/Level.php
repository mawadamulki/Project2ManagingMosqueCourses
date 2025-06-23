<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    public function course()
{
    return $this->belongsTo(Course::class,"course_id");
}
public function subjects(){
    return $this->hasMany(Subject::class);
}

}
