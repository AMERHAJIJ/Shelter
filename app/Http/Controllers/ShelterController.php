<?php

namespace App\Http\Controllers;

use App\Models\Shelter;
use App\Models\SecondaryShelter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShelterController extends Controller
{
    /* =====================================================
       TÜM MAIN SIĞINAKLAR
    ===================================================== */
    public function index()
    {
        $shelters = Shelter::with('areas')->get();

        $statusLabels = [
            'open' => 'Açık',
            'closed' => 'Kapalı',
            'preparing' => 'Hazırlanıyor'
        ];

        return response()->json([
            'success' => true,
            'count' => $shelters->count(),
            'shelters' => $shelters->map(function ($shelter) use ($statusLabels) {
                return [
                    'id' => $shelter->id,
                    'name' => $shelter->name,
                    'district' => $shelter->district,
                    'address' => $shelter->address,

                    'status' => [
                        'key' => $shelter->status,
                        'label' => $statusLabels[$shelter->status] ?? 'Bilinmiyor'
                    ],

                    'capacity' => [
                        'total' => $shelter->capacity_total,
                        'current' => $shelter->capacity_current,
                        'percentage' => $shelter->capacity_total == 0
                            ? "0%"
                            : round(($shelter->capacity_current / $shelter->capacity_total) * 100) . "%"
                    ],

                    'areas_count' => $shelter->areas->count(),
                    'available_areas' => $shelter->areas
                        ->pluck('type')
                        ->unique()
                        ->values(),
                ];
            })
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /* =====================================================
       EN YAKIN AÇIK SIĞINAK (MAIN + SECONDARY)
    ===================================================== */
    public function nearby(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->lng;

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Konum bilgisi eksik.'
            ], 400);
        }

        // MAIN shelters
        $mainShelters = Shelter::where('status', 'open')
            ->select(
                'id',
                'name',
                'district',
                'address',
                'lat',
                'lng',
                DB::raw("'main' as shelter_type"),
                DB::raw("NULL as parent_id"),
                DB::raw("
                    (6371 * acos(
                        cos(radians($lat)) * cos(radians(lat)) *
                        cos(radians(lng) - radians($lng)) +
                        sin(radians($lat)) * sin(radians(lat))
                    )) as distance
                ")
            );

        // SECONDARY shelters
        $secondaryShelters = SecondaryShelter::where('status', 'open')
            ->select(
                'id',
                'name',
                'district',
                'address',
                'lat',
                'lng',
                DB::raw("'secondary' as shelter_type"),
                'main_shelter_id as parent_id',
                DB::raw("
                    (6371 * acos(
                        cos(radians($lat)) * cos(radians(lat)) *
                        cos(radians(lng) - radians($lng)) +
                        sin(radians($lat)) * sin(radians(lat))
                    )) as distance
                ")
            );

        $nearest = $mainShelters
            ->unionAll($secondaryShelters)
            ->orderBy('distance')
            ->first();

        if (!$nearest) {
            return response()->json([
                'success' => false,
                'message' => 'Yakında açık sığınak bulunamadı.'
            ], 404);
        }

        $parent = null;

        if ($nearest->shelter_type === 'secondary') {
            $parent = Shelter::find($nearest->parent_id);
        }

        return response()->json([
            'success' => true,
            'id' => $nearest->id,
            'name' => $nearest->name,
            'district' => $nearest->district,
            'address' => $nearest->address,
            'lat' => (float) $nearest->lat,
            'lng' => (float) $nearest->lng,

            'type' => $nearest->shelter_type,

            'parent' => $parent ? [
                'id' => $parent->id,
                'name' => $parent->name,
                'lat' => (float) $parent->lat,
                'lng' => (float) $parent->lng,
            ] : null,

            'status' => 'open',
            'status_label' => 'Açık',
            'is_open' => true,
            'distance_km' => round($nearest->distance, 2),
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /* =====================================================
       MAIN SIĞINAK DETAY
    ===================================================== */
    public function show($id)
    {
        $shelter = Shelter::with('areas')->find($id);

        if (!$shelter) {
            return response()->json([
                'success' => false,
                'message' => 'Sığınak bulunamadı.'
            ], 404);
        }

        $statusLabels = [
            'open' => 'Açık',
            'closed' => 'Kapalı',
            'preparing' => 'Hazırlanıyor'
        ];

        return response()->json([
            'success' => true,
            'shelter' => [
                'id' => $shelter->id,
                'name' => $shelter->name,
                'district' => $shelter->district,
                'address' => $shelter->address,

                'status' => [
                    'key' => $shelter->status,
                    'label' => $statusLabels[$shelter->status] ?? 'Bilinmiyor',
                ],

                'capacity' => [
                    'total' => $shelter->capacity_total,
                    'current' => $shelter->capacity_current,
                    'percentage' => $shelter->capacity_total == 0
                        ? "0%"
                        : round(($shelter->capacity_current / $shelter->capacity_total) * 100) . "%"
                ],

                'areas_count' => $shelter->areas->count(),

                'areas' => $shelter->areas->map(function ($area) {
                    return [
                        'id' => $area->id,
                        'name' => $area->name,
                        'type' => $area->type,
                        'capacity' => [
                            'total' => $area->capacity_total,
                            'current' => $area->capacity_current,
                        ],
                        'is_active' => (bool) $area->is_active,
                    ];
                }),
            ]
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
