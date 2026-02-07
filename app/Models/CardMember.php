<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardMember extends Model
{
    use HasFactory;

    protected $table = 'card_members';

    protected $fillable = [
        'card_id',
        'name',
        'age',
        'gender',
        'health_status',
        'status',
        'last_location',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
