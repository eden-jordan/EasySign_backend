<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action_emargement extends Model
{
    protected $fillable = ['presence_id','type_action','timestamp'];

    public function presence()
    {
        return $this->belongsTo(Presence::class);
    }
}

