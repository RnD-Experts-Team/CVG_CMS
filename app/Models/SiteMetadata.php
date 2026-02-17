<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteMetadata extends Model
{
    protected $fillable = ['name', 'description', 'keywords', 'logo_media_id', 'favicon_media_id'];

    public function logo()
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    public function favicon()
    {
        return $this->belongsTo(Media::class, 'favicon_media_id');
    }
}
