<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkout_id',
        'product_id',
        'quantity',
        'price',
        'sub_total',
    ];

    public $timestamps = false;

    public function checkout()
    {
        return $this->belongsTo(Checkout::class);
    }
}
