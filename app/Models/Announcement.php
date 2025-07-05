<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table="announcementsCourses";
     protected $fillable = [
        'description',
        'announcementCourseImage'
    ];
}
