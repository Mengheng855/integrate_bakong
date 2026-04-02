<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'image',
        'price',
        'currency',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}