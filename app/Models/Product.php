<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $attributes = [
        'gender' => 'Unisex',
    ];
    protected $fillable = [
        'name',
        'description',
        'type',
        'gender',
        'price',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id', 'id');
    }
    public function inCart(){
        return $this->hasMany(ShoppingCartItem::class, 'product_id', 'id');
    }
}
