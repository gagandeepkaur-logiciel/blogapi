<?php

namespace App\Transformers;

use App\Models\Post;
use App\Models\Category;
use Facebook\Request;
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
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Post $post)
    {
        $id = auth()->user()->id;
        $categoryid = Post::where('userid', $id)->pluck('categoryid');
        $categoryname = Category::where('id',$categoryid)->pluck('name');
        return [
            'title' => $post->title,
            'category' => $categoryname,
            'created_by' => $post->created_by,
        ];
    }
}
