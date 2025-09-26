<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory;
  
    protected $fillable = [
        'link_id',
        'type',
        'browser',
        'referer',
        'ip',
        'device',
        'country',
        'user_agent',
        'description'
    ];

    public function link()
    {{
        return $this->belongsTo(Link::class);
    }}
}
