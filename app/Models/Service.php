<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = ['image_media_id', 'title', 'content', 'description', 'featured'];

    protected $casts = [
        'featured' => 'boolean',
    ];

    protected $appends = ['url'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->icon_path) {
            return null;
        }

        return asset('storage/'.$this->icon_path);
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }
}
