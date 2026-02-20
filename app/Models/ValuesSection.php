<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValuesSection extends Model
{
    protected $fillable = ['title'];

    public function values()
    {
        return $this->hasMany(ValuesItem::class, 'values_section_id');
    }
}
