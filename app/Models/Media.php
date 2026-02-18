<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'path',
        'type',
        'mime_type',
        'width',
        'height',
        'size_bytes',
        'alt_text',
        'title',
    ];

    /**
     * Append computed url automatically
     */
    protected $appends = ['url'];

    /**
     * ✅ Calculate full URL dynamically
     */
    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        return asset('storage/'.$this->path);
    }

    public function heroSections()
    {
        return $this->hasMany(HeroMedia::class);
    }

    public function projectImages()
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'image_media_id');
    }
}
