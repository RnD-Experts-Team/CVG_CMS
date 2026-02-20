<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    protected $fillable = ['title', 'subtitle', 'button_text', 'button_link'];

    public function media()
    {
        return $this->hasMany(HeroMedia::class, 'hero_section_id');
    }
}
