<?php

namespace App\Providers;

use App\Models\UploadedFile;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('update-uploaded_file', function (User $user, UploadedFile $uploaded_file) {
            return $user->id === $uploaded_file->user_id;
        });

        Gate::define('visible-uploaded_file', function (?User $user, UploadedFile $uploaded_file) {
            if ($uploaded_file->visibly === 0) {

                $user = auth('sanctum')->user();

                if ($user === null) return false;

                return $user->id  === $uploaded_file->user_id;
            }

            return true;
        });
    }
}
