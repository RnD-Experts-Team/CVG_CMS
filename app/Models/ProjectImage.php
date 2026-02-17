<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    protected $fillable = [
        'project_id', 'media_id', 'previous_image_id', 'sort_order', 'caption',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function previousImage()
    {
        return $this->belongsTo(ProjectImage::class, 'previous_image_id');
    }
}
