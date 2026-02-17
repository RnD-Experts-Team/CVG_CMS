<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessSection extends Model
{
    protected $fillable = ['title', 'image_media_id'];

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    public function steps()
    {
        return $this->hasMany(ProcessStep::class, 'process_section_id')->orderBy('sort_order');
    }
}
