<?php

namespace App\Transformers;

use App\Models\Post;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Fractal;

class PostListTransformer extends TransformerAbstract
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
        'comments', 'post'
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Post $post)
    {
        return [
            'title' => $post->title,
            'category' => $post->categoryid,
            'folder' => $post->folder_id,
            'image' => $post->image,
            'created_by' => $post->created_by,
        ];
    }
    
    public function includeComments(Post $post)
    {
        $comments = $post->comments;
        return $this->collection($comments, new CommentTransformer());
    }
    
    public function includePost(Post $post)
    {
        return $this->item($post, new PostListTransformer());
    }
}
