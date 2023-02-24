<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use App\Transformers\PostListTransformer;

class CommentTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        // 
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        'post', 
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Comment $comments)
    {
        return [
            'comment' => $comments->comment,
            'posted_by' => $comments->created_by,
        ];
    }
    
    public function includePost(Comment $comments){
        $post = $comments->post;
        return $this->item($post, new PostListTransformer());
    }
}
