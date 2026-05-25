<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemorialLifeSection extends Model
{
    use HasFactory;

    protected $table = 'memorial_life_sections';
    
    protected $fillable = [
        'memorial_id',
        'title',
        'text',
        'sort_order',
    ];

    public function memorial()
    {
        return $this->belongsTo(Memorial::class);
    }
}