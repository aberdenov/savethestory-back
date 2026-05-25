<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memorial extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'slug', 
        'full_name', 
        'birth_date', 
        'death_date', 
        'main_photo', 
        'short_description', 
        'biography', 
        'status', 
        'facts', 
        'qualities', 
        'closing_text'
    ];
    
    protected function casts(): array
    {
        return ['birth_date' => 'date:Y-m-d', 'death_date' => 'date:Y-m-d', 'facts' => 'array', 'qualities' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(MemorialPhoto::class)->orderBy('sort_order');
    }

    public function timelines()
    {
        return $this->hasMany(MemorialTimeline::class)->orderBy('sort_order');
    }

    public function quotes()
    {
        return $this->hasMany(MemorialQuote::class)->orderBy('sort_order');
    }

    public function lifeSections()
    {
        return $this->hasMany(\App\Models\MemorialLifeSection::class, 'memorial_id', 'id')
            ->orderBy('sort_order');
    }
}
    
