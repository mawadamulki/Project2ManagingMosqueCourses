<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    protected $fillable=[
        'userID',
        'subject_id',
        'mark'
    ];
public function user()
{
    return $this->belongsTo(User::class);
}

public function subject()
{
    return $this->belongsTo(Subject::class);
}
}
