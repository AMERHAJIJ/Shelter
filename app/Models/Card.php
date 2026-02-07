<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CardMember;


class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_name',
        'qr_code',
        'has_pet',
        'pet_type',
        'balance',
    ];

    public function members()
    {
        return $this->hasMany(CardMember::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
