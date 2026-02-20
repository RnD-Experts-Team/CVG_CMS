<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessStep extends Model
{
    protected $fillable = ['process_section_id', 'sort_order', 'title', 'description'];

    public function section()
    {
        return $this->belongsTo(ProcessSection::class, 'process_section_id');
    }
}
