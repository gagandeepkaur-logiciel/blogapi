<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class FacebookPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facebook_id',
        'page_id',
        'page_name',
        'access_token',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
