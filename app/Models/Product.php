<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        "name",
        "slug",
        "content",
        "price",
        "type",
        "status",
        "unlimited_stock",
        "stock",
        "category_id",
        "active"
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
