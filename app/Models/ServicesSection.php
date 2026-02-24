<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicesSection extends Model
{
    protected $fillable = ['title', 'description', 'content', 'image_media_id', 'button_text'];

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }
}
