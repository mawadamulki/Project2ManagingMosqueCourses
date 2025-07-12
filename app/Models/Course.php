<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
protected $fillable=[
    'courseName',
    'courseImage',
    'status'
];

public function users()
{
    return $this->belongsToMany(User::class);
}

// public function subjects()
// {
//     return $this->hasMany(Subject::class);
// }

public function levels(){
    return $this->hasMany(Level::class, 'courseID');
}

}
