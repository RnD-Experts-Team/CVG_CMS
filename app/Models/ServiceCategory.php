<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = ['key', 'title', 'description', 'icon_path'];

    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        if (! $this->icon_path) {
            return null;
        }

        return asset('storage/'.$this->icon_path);
    }
}
