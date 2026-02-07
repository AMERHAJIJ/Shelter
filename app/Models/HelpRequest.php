<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpRequest extends Model
{
    protected $fillable = [
    'user_id',
    'gathering_point_id',
    'type',
    'note',
    'status',
    'priority'
];
public function gatheringPoint()
{
    return $this->belongsTo(\App\Models\GatheringPoint::class);
}

public function shelter()
{
    return $this->hasOneThrough(
        \App\Models\Shelter::class,
        \App\Models\GatheringPoint::class,
        'id',          // gathering_points.id
        'id',   
        'card_id',       // shelters.id
        'gathering_point_id',
        'shelter_id'
    );
}
public function transportRequest()
{
    return $this->hasOne(\App\Models\TransportRequest::class);
}
public function card()
{
    return $this->belongsTo(\App\Models\Card::class);
}




}

