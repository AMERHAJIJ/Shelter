<?php

namespace App\Http\Controllers;

use App\Models\HelpRequest;
use Illuminate\Http\Request;

class HelpRequestController extends Controller
{
    /**
     * TÜM YARDIM TALEPLERİNİ GETİR
     */
    public function index()
    {
        $requests = HelpRequest::orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                               ->orderBy('created_at', 'desc')
                               ->get();

        return response()->json([
            'success' => true,
            'count' => $requests->count(),
            'data' => $requests
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * YARDIM TALEBİ OLUŞTUR
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
    'user_id' => 'nullable|integer',
    'gathering_point_id' => 'required|exists:gathering_points,id',
    'type' => 'required|string|in:injured,trapped,needs_transport,other',
    'note' => 'nullable|string',
    'card_id' => 'nullable|exists:cards,id',
     ]);


        // TYPE → PRIORITY otomatik belirleme
        $priority = match($validated['type']) {
            'injured', 'trapped' => 'high',
            'needs_transport' => 'medium',
            default => 'low'
        };

        // CREATE
        $helpRequest = HelpRequest::create([
           'user_id' => $validated['user_id'] ?? null,
           'gathering_point_id' => $validated['gathering_point_id'],
           'type' => $validated['type'],
           'note' => $validated['note'] ?? null,
           'status' => 'pending',
           'priority' => $priority,
           'card_id' => $validated['card_id'] ?? null,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Yardım talebi oluşturuldu',
            'data' => $helpRequest
        ], 201, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * TEK YARDIM TALEBİ GETİR
     */
    public function show($id)
    {
        $helpRequest = HelpRequest::find($id);

        if (!$helpRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Yardım talebi bulunamadı'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'success' => true,
            'data' => $helpRequest
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * DURUM VE PRIORITY GÜNCELLEME
     */
    public function update(Request $request, $id)
    {
        $helpRequest = HelpRequest::find($id);

        if (!$helpRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Yardım talebi bulunamadı'
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $request->validate([
            'status'   => 'nullable|string|in:pending,in_progress,resolved',
            'priority' => 'nullable|string|in:low,medium,high',
        ]);

        if ($request->status) {
            $helpRequest->status = $request->status;
        }

        if ($request->priority) {
            $helpRequest->priority = $request->priority;
        }

        $helpRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Talep güncellendi',
            'data' => $helpRequest
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    public function details($id)
{
    $helpRequest = HelpRequest::with('gatheringPoint.shelter')->find($id);

    if (!$helpRequest) {
        return response()->json([
            'success' => false,
            'message' => 'Help request not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'help_request' => [
            'id' => $helpRequest->id,
            'type' => $helpRequest->type,
            'status' => $helpRequest->status,
            'location' => $helpRequest->location,
        ],
        'gathering_point' => $helpRequest->gatheringPoint ? [
            'id' => $helpRequest->gatheringPoint->id,
            'name' => $helpRequest->gatheringPoint->name,
            'district' => $helpRequest->gatheringPoint->district,
        ] : null,
        'shelter' => $helpRequest->gatheringPoint && $helpRequest->gatheringPoint->shelter ? [
            'id' => $helpRequest->gatheringPoint->shelter->id,
            'name' => $helpRequest->gatheringPoint->shelter->name,
            'capacity' => $helpRequest->gatheringPoint->shelter->capacity_total,
        ] : null,
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}


    /**
     * FİLTRELEME + PRIORITY SIRALAMASI
     */
    public function filter(Request $request)
    {
        $status   = $request->status;
        $type     = $request->type;
        $userId   = $request->user_id;
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        // Sorgu
        $query = HelpRequest::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Priority → created_at sıralaması
        $results = $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json([
            'success' => true,
            'count'   => $results->count(),
            'data'    => $results
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
