<?php

namespace App\Transformers;

use App\Models\Category;
use App\Models\Post;
use League\Fractal\TransformerAbstract;

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
        // 'comments',
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($post)
    {
        $userid = auth()->user()->id;
        $categoryid = Post::where('userid', $userid)->pluck('categoryid')->first();
        $categoryname = Category::where('id', $categoryid)->pluck('name');
        return [
            'title' => $post->title,
            'category' => $categoryname,
            'created_by' => $post->created_by,
        ];
    }
}
