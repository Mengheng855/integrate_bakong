<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'product_id',
        'reference',
        'md5',
        'qr',
        'amount',
        'currency',
        'status',
        'payer_account',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}