<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;
use App\Transformers\PostListTransformer;

class CommentTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        // 'comments',
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        // 'comments',
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($post)
    {
        return [
            'title' => $post->title,
            'created_by' => $post->created_by,
        ];
    }
    
    
}
