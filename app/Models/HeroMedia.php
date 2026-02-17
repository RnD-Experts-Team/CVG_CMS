<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroMedia extends Model
{
    protected $fillable = ['hero_section_id', 'media_id', 'sort_order'];

    public function heroSection()
    {
        return $this->belongsTo(HeroSection::class, 'hero_section_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
