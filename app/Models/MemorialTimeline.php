<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MemorialTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'memorial_id', 
        'period', 
        'description', 
        'sort_order'
    ];
    
    public function memorial()
    {
        return $this->belongsTo(Memorial::class);
    }
}

