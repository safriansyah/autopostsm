<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Services\SocialMedia\SocialMediaManager;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoPostToSocialMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically post scheduled content to social media.';

    /**
     * Execute the console command.
     */
    public function handle(SocialMediaManager $manager): void
    {
        $timezone = env('APP_TIMEZONE') ?? config('app.timezone');
        $posts = Post::publishable($timezone)->get();

        if ($posts->isEmpty()) {
            return;
        }

        $isAnyPostSuccessful = false;

        try {
            foreach ($posts as $post) {
                if ($manager->publishPost($post, $post->caption())) {
                    $isAnyPostSuccessful = true;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error posting to social media: ' . $e->getMessage());
            $this->notifyUsers(
                'Error posting to social media',
                'An error occurred while posting to social media. Please check the logs.',
                'danger',
            );

            return;
        }

        if ($isAnyPostSuccessful) {
            $this->notifyUsers(
                'Scheduled posts successfully posted',
                'Some or all scheduled posts were successfully posted to social media.',
                'success',
            );
        }
    }

    /**
     * Send a database notification to every user.
     */
    protected function notifyUsers(string $title, string $body, string $type): void
    {
        foreach (User::all() as $user) {
            Notification::make()
                ->title($title)
                ->body($body)
                ->{$type}()
                ->sendToDatabase($user);
        }
    }
}
