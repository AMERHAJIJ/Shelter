<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\CardMember;  // DÜZELTİLDİ
use App\Models\WalletTransaction;

class CardController extends Controller
{
    /**
     * Aile kartı oluştur
     */
    public function store(Request $request)
    {
        // Doğrulama
        $validated = $request->validate([
            'family_name' => 'required|string|max:255',
            'has_pet' => 'required|boolean',
            'pet_type' => 'nullable|string|max:255',

            'members' => 'required|array|min:1',
            'members.*.name' => 'required|string|max:255',
            'members.*.age' => 'required|integer|min:0',
            'members.*.gender' => 'required|string',
            'members.*.health_status' => 'nullable|string'
        ]);

        // Üye sayısı
        $memberCount = count($request->members);

        // Kişi başı 50 TL → toplam bakiye
        $balance = $memberCount * 50;

        // Kart oluştur
        $card = Card::create([
            'family_name' => $request->family_name,
            'qr_code' => 'QR' . uniqid(),
            'has_pet' => $request->has_pet,
            'pet_type' => $request->pet_type,
            'balance' => $balance,
        ]);

        // Her aile üyesini kaydet
        $members = [];
        foreach ($request->members as $member) {
            $members[] = CardMember::create([
                'card_id' => $card->id,
                'name' => $member['name'],
                'age' => $member['age'],
                'gender' => $member['gender'],
                'health_status' => $member['health_status'] ?? 'unknown',
                'status' => 'inside',
                'last_location' => null
            ]);
        }

        return response()->json([
            "status" => "success",
            "message" => "Aile kartı oluşturuldu",

            "card" => [
                "id" => $card->id,
                "family_name" => $card->family_name,
                "qr_code" => $card->qr_code,
                "has_pet" => $card->has_pet,
                "pet_type" => $card->pet_type,
                "balance" => $card->balance,
                "member_count" => $memberCount,
                "created_at" => $card->created_at,
                "updated_at" => $card->updated_at,
            ],

            "members" => $members

        ], 201);
    }


    /**
     * Kart detay
     */
    public function show($id)
    {
        $card = Card::with('members')->find($id);

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Kart bulunamadı'
            ], 404, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return response()->json([
            'success' => true,

            'card' => [
                'id' => $card->id,
                'family_name' => $card->family_name,
                'qr_code' => $card->qr_code,
                'has_pet' => $card->has_pet,
                'pet_type' => $card->pet_type,
                'balance' => $card->balance,
                'member_count' => $card->members->count(),
                'created_at' => $card->created_at,
            ],

            'members' => $card->members->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'age' => $m->age,
                    'gender' => $m->gender,
                    'health_status' => $m->health_status,
                    'status' => $m->status,
                    'last_location' => $m->last_location,
                    'updated_at' => $m->updated_at,
                ];
            }),

        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    public function update(Request $request, $id)
{
    $card = Card::with('members')->find($id);

    if (!$card) {
        return response()->json([
            'success' => false,
            'message' => 'Kart bulunamadı'
        ], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // Güncellenebilir alanlar
    $card->update([
        'family_name' => $request->family_name ?? $card->family_name,
        'has_pet' => $request->has_pet ?? $card->has_pet,
        'pet_type' => $request->pet_type ?? $card->pet_type,
    ]);

    $updatedMembers = [];

    // Eğer üyeler gönderildiyse
    if ($request->has('members')) {

        foreach ($request->members as $memberData) {

            // Eğer ID varsa → mevcut üyeyi güncelle
            if (isset($memberData['id'])) {

                $member = CardMember::where('card_id', $card->id)
                                      ->where('id', $memberData['id'])
                                      ->first();

                if ($member) {
                    $member->update([
                        'name' => $memberData['name'] ?? $member->name,
                        'age' => $memberData['age'] ?? $member->age,
                        'gender' => $memberData['gender'] ?? $member->gender,
                        'health_status' => $memberData['health_status'] ?? $member->health_status,
                    ]);
                }

                $updatedMembers[] = $member;

            } else {
                // ID yoksa → yeni üye ekle
                $newMember = CardMember::create([
                    'card_id' => $card->id,
                    'name' => $memberData['name'],
                    'age' => $memberData['age'],
                    'gender' => $memberData['gender'],
                    'health_status' => $memberData['health_status'] ?? 'healthy',
                    'status' => 'inside',
                ]);

                $updatedMembers[] = $newMember;
            }
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Kart başarıyla güncellendi',
        'card' => $card,
        'members' => $updatedMembers
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
 public function updateMemberStatus(Request $request, $card_id, $member_id)
{
    // İstek doğrulaması
    $request->validate([
        'status' => 'required|string'
    ]);

    // Üyeyi bul
    $member = \App\Models\CardMember::where('card_id', $card_id)
                ->where('id', $member_id)
                ->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Üye bulunamadı'
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    // Güncelle
    $member->status = $request->status;
    $member->save();

    return response()->json([
        'success' => true,
        'message' => 'Durum güncellendi',
        'member' => $member
    ], 200, [], JSON_UNESCAPED_UNICODE);
}
public function updateMemberLocation(Request $request, $card_id, $member_id)
{
    // Doğrulama
    $validated = $request->validate([
        'location' => 'required|string'
    ]);

    // Üye var mı?
    $member = CardMember::where('card_id', $card_id)
                        ->where('id', $member_id)
                        ->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Üye bulunamadı'
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    // Lokasyon güncelle
    $member->last_location = $request->location;
    $member->save();

    return response()->json([
        'success' => true,
        'message' => 'Konum güncellendi',
        'member' => $member
    ], 200, [], JSON_UNESCAPED_UNICODE);
}
public function showByQr($qr)
{
    // Kartı QR koduna göre bul
    $card = Card::with('members')->where('qr_code', $qr)->first();

    // Kart yoksa
    if (!$card) {
        return response()->json([
            'success' => false,
            'message' => 'QR koduna ait kart bulunamadı'
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    // Kart varsa detayları döndür
    return response()->json([
        'success' => true,
        'card' => [
            'id' => $card->id,
            'family_name' => $card->family_name,
            'qr_code' => $card->qr_code,
            'balance' => $card->balance,
            'has_pet' => $card->has_pet,
            'pet_type' => $card->pet_type,
            'member_count' => $card->members->count(),
        ],
        'members' => $card->members->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'age' => $m->age,
                'gender' => $m->gender,
                'health_status' => $m->health_status,
                'status' => $m->status,
                'last_location' => $m->last_location,
                'updated_at' => $m->updated_at,
            ];
        }),
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
public function addBalance(Request $request, $id)
{
    $request->validate([
        'amount' => 'required|numeric|min:1'
    ]);

    $card = Card::find($id);

    if (!$card) {
        return response()->json([
            'success' => false,
            'message' => 'Kart bulunamadı'
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    // Bakiyeyi artır
    $card->balance += $request->amount;
    $card->save();

    return response()->json([
        'success' => true,
        'message' => 'Bakiye eklendi',
        'new_balance' => $card->balance
    ], 200, [], JSON_UNESCAPED_UNICODE);
}
public function addMember(Request $request, $id)
{
    // Kart var mı?
    $card = Card::find($id);

    if (!$card) {
        return response()->json([
            'success' => false,
            'message' => 'Kart bulunamadı'
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    // Doğrulama
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'age' => 'required|integer|min:0',
        'gender' => 'required|string',
        'health_status' => 'nullable|string'
    ]);

    // Yeni üye oluştur
    $member = CardMember::create([
        'card_id' => $card->id,
        'name' => $request->name,
        'age' => $request->age,
        'gender' => $request->gender,
        'health_status' => $request->health_status ?? 'healthy',
        'status' => 'safe',
        'last_location' => null,
    ]);

    // Yeni bakiye → kişi başı 50 TL
    $card->balance += 50;
    $card->save();

    return response()->json([
        'success' => true,
        'message' => 'Üye eklendi',
        'member' => $member,
        'new_balance' => $card->balance
    ], 201, [], JSON_UNESCAPED_UNICODE);
}
public function deleteMember($card_id, $member_id)
{
    // Kart var mı?
    $card = Card::find($card_id);

    if (!$card) {
        return response()->json([
            'success' => false,
            'message' => 'Kart bulunamadı'
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    // Üye var mı?
    $member = CardMember::where('card_id', $card_id)
                        ->where('id', $member_id)
                        ->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Üye bulunamadı'
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    // Üyeyi sil
    $member->delete();

    // Bakiyeden 50 TL düş → en az 0 olsun
    $card->balance = max(0, $card->balance - 50);
    $card->save();

    return response()->json([
        'success' => true,
        'message' => 'Üye karttan silindi',
        'removed_member_id' => $member_id,
        'new_balance' => $card->balance
    ], 200, [], JSON_UNESCAPED_UNICODE);
}
public function spend(Request $request, $id)
{
    $request->validate([
        'amount' => 'required|numeric|min:1',
        'description' => 'required|string|max:255',
    ]);

    $card = Card::find($id);

    if (!$card) {
        return response()->json([
            'success' => false,
            'message' => 'Card not found'
        ], 404);
    }

    // تحقق من الرصيد
    if ($card->balance < $request->amount) {
        return response()->json([
            'success' => false,
            'message' => 'Insufficient balance'
        ], 422);
    }

    // تسجيل العملية
    WalletTransaction::create([
    'card_id' => $card->id,
    'amount' => $request->amount,
    'type' => 'expense',
    'note' => $request->description,
   ]);


    // خصم الرصيد
    $card->balance -= $request->amount;
    $card->save();

    return response()->json([
        'success' => true,
        'message' => 'Expense completed successfully',
        'new_balance' => $card->balance
    ]);
}


    /**
     * Tüm kartları listele (Opsiyonel)
     */
    public function index()
    {
        return Card::with('members')->get();
    }
}
