<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shelter;

class ShelterSeeder extends Seeder
{
    public function run(): void
    {
        // KÜÇÜKÇEKMECE
        Shelter::create([
            'name' => 'Küçükçekmece Merkez Sığınak',
            'district' => 'Küçükçekmece',
            'address' => 'Merkez Mah.',
            'lat' => 41.004,
            'lng' => 28.780,
            'capacity_total' => 500,
            'capacity_current' => 0,
            'status' => 'open',
            'has_children_area' => true,
            'has_animals_area' => true,
            'has_medical_service' => true,
            'has_prayer_room' => true,
        ]);

        // ESENYURT
        Shelter::create([
            'name' => 'Esenyurt Merkez Sığınağı',
            'district' => 'Esenyurt',
            'address' => 'Mehterçeşme Mah.',
            'lat' => 41.033,
            'lng' => 28.675,
            'capacity_total' => 600,
            'capacity_current' => 0,
            'status' => 'closed',
            'has_children_area' => true,
            'has_animals_area' => true,
            'has_medical_service' => true,
            'has_prayer_room' => true,
        ]);

        // BÜYÜKÇEKMECE
        Shelter::create([
            'name' => 'Büyükçekmece Merkez Sığınak',
            'district' => 'Büyükçekmece',
            'address' => 'Merkez Mah.',
            'lat' => 40.976,
            'lng' => 28.630,
            'capacity_total' => 550,
            'capacity_current' => 0,
            'status' => 'closed',
            'has_children_area' => true,
            'has_animals_area' => true,
            'has_medical_service' => true,
            'has_prayer_room' => true,
        ]);

        // SULTANÇİFTLİĞİ
        Shelter::create([
            'name' => 'Sultançiftliği Merkez Sığınak',
            'district' => 'Gaziosmanpaşa',
            'address' => 'Sultançiftliği Mah.',
            'lat' => 41.086,
            'lng' => 28.912,
            'capacity_total' => 520,
            'capacity_current' => 0,
            'status' => 'closed',
            'has_children_area' => true,
            'has_animals_area' => true,
            'has_medical_service' => true,
            'has_prayer_room' => true,
        ]);
    }
}
