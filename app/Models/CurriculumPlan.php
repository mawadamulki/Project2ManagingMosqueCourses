<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumPlan extends Model
{
    protected $table="curriculumPlans";
    protected $fillable=[
        'levelID',
        'sessionDate',
        'sessionContent'
    ];

    public function level()
    {
        return $this->belongsTo(Level::class, 'levelID');
    }

}
