<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class InstagramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('instagram', function () {
            $data = Setting::where('option_name', 'like', 'instagram_%')->pluck('option_value', 'option_name')->toArray();

            $token = $data['instagram_access_token'] ?? env('INSTAGRAM_ACCESS_TOKEN');

            // Instagram API with Instagram Login uses graph.instagram.com and
            // the "me" alias for the authenticated IG user. Tokens that start
            // with "IGAA" will NOT work against graph.facebook.com.
            return Http::withToken($token)
                ->baseUrl('https://graph.instagram.com/v21.0');
        });
    }
}
