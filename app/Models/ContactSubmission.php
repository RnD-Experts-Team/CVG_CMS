<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $fillable = [
        'full_name', 'email', 'phone_number', 'project_details', 'ip_address', 'user_agent',
    ];
}
