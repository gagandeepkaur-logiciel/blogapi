<?php

use Illuminate\Support\Facades\Auth;
use App\Models\{
    FacebookPage,
    Post
};
use App\Http\Controllers\FacebookController;

if (!function_exists('check_Page_Token')) {
    function check_Page_Token($page_name)
    {
        $data = FacebookPage::where('page_name', $page_name)
            ->first();
        return $data;
    }
}
if (!function_exists('check_tokens')) {
    function check_tokens($post_id)
    {
        $data = Post::where('id', $post_id)
            ->first();
        return $data;
    }
}
if (!function_exists('page_token')) {
    function page_token($page_id)
    {
        $data = FacebookPage::where('page_id', $page_id)
            ->pluck('access_token')
            ->first();
        return $data;
    }
}
