<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelter extends Model
{
    protected $fillable = [
        'name',
        'district',
        'address',
        'lat',
        'lng',
        'capacity_total',
        'capacity_current',
        'status',
        'has_children_area',
        'has_animals_area',
        'has_medical_service',
        'has_prayer_room',
        'internal_map',
    ];

    public function secondaryShelters()
    {
        return $this->hasMany(SecondaryShelter::class, 'main_shelter_id');
    }
    public function areas()
{
    return $this->hasMany(ShelterArea::class);
}
}
