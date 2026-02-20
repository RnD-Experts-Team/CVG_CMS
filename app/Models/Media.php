<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'disk', 'path', 'url', 'type', 'mime_type',
        'width', 'height', 'size_bytes', 'alt_text', 'title',
    ];

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
