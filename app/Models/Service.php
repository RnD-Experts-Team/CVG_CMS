<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = ['image_media_id', 'title', 'description', 'featured'];

    protected $casts = [
        'featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }
}
