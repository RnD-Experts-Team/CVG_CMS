<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    protected $fillable = ['title', 'description', 'image_media_id'];

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }
}
