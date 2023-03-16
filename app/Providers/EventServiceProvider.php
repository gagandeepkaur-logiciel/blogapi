<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\{
    CreatePost,
    CreateComment,
    UpdatePost,
    UpdateComment,
    DeleteComment,
    DeletePost,
};
use App\Listeners\{
    CreatedPost,
    CreatedComment,
    UpdatedPost,
    UpdatedComment,
    DeletedComment,
    DeletedPost
};
use App\Models\{
    Folder,
    Post
};
use App\Observers\{
    FolderObserver,
    PostObserver
};

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        CreatePost::class => [
            CreatedPost::class,
        ],

        CreateComment::class => [
            CreatedComment::class,
        ],

        UpdatePost::class => [
            UpdatedPost::class,
        ],

        UpdateComment::class => [
            UpdatedComment::class,
        ],

        DeleteComment::class => [
            DeletedComment::class,
        ],

        UpdateComment::class => [
            UpdatedComment::class,
        ],

        DeletePost::class => [
            DeletedPost::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Post::observe(PostObserver::class);
        Folder::observe(FolderObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
