<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'subject',
        'open',
        'receiverID',
        'senderID',
        'content',
    ];


    public function sender()
    {
        return $this->belongsTo(User::class, 'senderID');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiverID');
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parentID');
    }
}
