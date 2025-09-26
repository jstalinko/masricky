<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Link extends Model
{
    use HasFactory;
 
     protected $fillable = [
      'user_id',
      'domain',
        'slug',
        'cloaking_method',
        'template',
        'target_url',
        'cloaking_url',
        'lock_country',
        'lock_platform',
        'lock_referer',
        'active',
        'meta_id',
        'click'
     ];
     protected $casts = [
      'lock_country' => 'array'
     ];
     public function logs()
     {
        return $this->hasMany(Log::class);
     }
}
