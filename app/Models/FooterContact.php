<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterContact extends Model
{
    protected $fillable = ['phone', 'whatsapp', 'email', 'address'];
}
