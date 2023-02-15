<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Comment;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'userid', 'categoryid', 'title', 'description', 'image', 'facebook_post_id', 'facebook_msg_id', 'pageid', 'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment(){
        return $this->hasMany(Comment::class);
    }
}