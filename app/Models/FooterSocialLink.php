<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterSocialLink extends Model
{
    protected $fillable = ['platform', 'url', 'sort_order', 'is_active'];
}
