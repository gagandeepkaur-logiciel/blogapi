<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'userid', 'postid', 'comment', 'facebook_post_id', 'comment_id', 'pageid', 'created_by',
    ];

    public function post(){
        return $this->belongsTo(Post::class);
    }
}