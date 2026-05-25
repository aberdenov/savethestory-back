<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory;
class MemorialPhoto extends Model { use HasFactory; protected $fillable=['memorial_id','image','caption','sort_order']; public function memorial(){return $this->belongsTo(Memorial::class);} }
