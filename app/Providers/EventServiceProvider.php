<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CreatePost;
use App\Listeners\CreatedPost;
use App\Events\CreateComment;
use App\Listeners\CreatedComment;
use App\Events\UpdatePost;
use App\Listeners\UpdatedPost;
use App\Events\UpdateComment;
use App\Listeners\UpdatedComment;
use App\Events\DeleteComment;
use App\Listeners\DeletedComment;
use App\Events\DeletePost;
use App\Listeners\DeletedPost;
use App\Models\Post;
use App\Observers\PostObserver;

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
