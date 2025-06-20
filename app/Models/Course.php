<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
protected $fillable=[
    'courseName',
    'description'
];

public function users()
{
    return $this->belongsToMany(User::class);
}

public function subjects()
{
    return $this->hasMany(Subject::class);
}
public function supervisor()
{
    return $this->belongsTo(User::class, 'supervisor_id');
}


}
