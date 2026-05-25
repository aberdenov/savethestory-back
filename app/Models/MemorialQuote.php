<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Factories\HasFactory;
class MemorialQuote extends Model { use HasFactory; protected $fillable=['memorial_id','author_name','author_relation','text','sort_order']; public function memorial(){return $this->belongsTo(Memorial::class);} }
