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
    // Pastikan content dalam bentuk array
    $content = $this->content;
    if (is_string($content)) {
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $content = $decoded;
        } else {
            // kalau format JSON rusak, gagal
            return false;
        }
    }

    $found = false;
    foreach ($content as &$item) {
        if (!isset($item['product_key'])) {
            continue;
        }

        // hanya ubah kalau belum 'used'
        if ($item['product_key'] === $key && (($item['product_status'] ?? '') !== 'used')) {
            $item['product_status'] = 'used';
            $found = true;
            break;
        }
    }
    unset($item); // penting: lepas reference

    if (! $found) {
        // key tidak ditemukan atau sudah used
        return false;
    }

    // Simpan content kembali (jika semula JSON, simpan sebagai JSON; jika array, simpan array)
    $this->content = is_string($this->content) ? json_encode($content) : $content;

    // Hitung sisa kunci yang belum digunakan sebagai stock
    $availableKeysCount = 0;
    foreach ($content as $item) {
        if (($item['product_status'] ?? '') !== 'used') {
            $availableKeysCount++;
        }
    }
    $this->stock = $availableKeysCount;

    return $this->save();
}
}
