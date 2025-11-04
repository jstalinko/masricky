<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'balance_after',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function updateMutationIn($userId, $amount, $description, $balanceAfter)
    {
        return self::create([
            'user_id' => $userId,
            'amount' => $amount,
            'type' => 'credit',
            'description' => $description,
            'balance_after' => $balanceAfter,
        ]);
    }

    public static function updateMutationOut($userId, $amount, $description, $balanceAfter)
    {
        return self::create([
            'user_id' => $userId,
            'amount' => $amount,
            'type' => 'debit',
            'description' => $description,
            'balance_after' => $balanceAfter,
        ]);
    }
}
