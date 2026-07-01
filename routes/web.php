<?php

use Illuminate\Support\Facades\Route;

// Send the root straight to the AutoPostSM admin panel.
// Using redirect('admin') (no leading slash) keeps it correct when the app
// is served from a sub-folder, e.g. https://domain/autopostsm/admin.
Route::get('/', fn () => redirect('admin'));
