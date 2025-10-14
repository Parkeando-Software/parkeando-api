<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserInbox extends Model
{
    protected $table = 'user_inbox';
    protected $fillable = ['user_id','type','payload','is_read'];
    protected $casts = [
        'payload' => 'array',
        'is_read' => 'boolean',
    ];
}
