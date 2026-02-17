<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValuesItem extends Model
{
    protected $fillable = ['values_section_id', 'title', 'description', 'media_id', 'sort_order'];

    public function section()
    {
        return $this->belongsTo(ValuesSection::class, 'values_section_id');
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
