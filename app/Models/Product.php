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

    protected $casts = [
        'content' => 'array',
        'unlimited_stock' => 'boolean',
        'active' => 'boolean',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
     /**
     * Ambil semua product_key yang statusnya masih available
     */
      /**
     * Ambil semua product_key yang statusnya masih available
     */
    public function getAvailableKeys(): array
    {
        if (!is_array($this->content)) {
            return [];
        }

        return collect($this->content)
            ->where('product_status', 'available')
            ->pluck('product_key')
            ->toArray();
    }

    /**
     * Ambil product_key pertama yang statusnya available
     */
    public function getFirstAvailableKey(): ?string
    {
        return collect($this->content)
            ->firstWhere('product_status', 'available')['product_key'] ?? null;
    }

    /**
     * Update product_status jadi 'used' berdasarkan product_key
     */
    public function markAsUsed(string $key): bool
    {
        $content = $this->content;

        foreach ($content as &$item) {
            if ($item['product_key'] === $key) {
                $item['product_status'] = 'used';
                break;
            }
        }
        $this->stock = count($this->getAvailableKeys())-1;
        $this->content = $content;
        return $this->save();
    }
}
