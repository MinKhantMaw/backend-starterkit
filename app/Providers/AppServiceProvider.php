<?php

namespace App\Providers;

use App\Contracts\Repositories\CmsRepositoryInterface;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Content;
use App\Models\Media;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Observers\AuditableObserver;
use App\Repositories\Eloquent\EloquentCmsRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CmsRepositoryInterface::class, EloquentCmsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([User::class, Content::class, Page::class, Post::class, Category::class, Tag::class,
            Media::class, Menu::class, MenuItem::class, Setting::class, ContactMessage::class] as $model) {
            $model::observe(AuditableObserver::class);
        }

        Gate::before(function ($user) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
