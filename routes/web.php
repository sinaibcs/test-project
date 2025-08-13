<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes(['register' => false,'reset' => false,'verify' => false,]);
Route::get('/', function () {
    return view('welcome');
});
Route::get('/report', function () {
    return view('reports.division_report');
});



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/cloud/{path}', function($path){
    // return [];
    $url = env('FILE_VIEW_ROOT').'/'.$path;

    // Fetch the image using Laravel's Http facade (Guzzle wrapper)
    $response = Http::get($url);

    // Check if the request was successful
    if ($response->successful()) {
        // Return the raw image data to the browser with the correct headers
        return response($response->body())
            ->header('Content-Type', $response->header('Content-Type'));
    }

    // If the image cannot be fetched, return an error message
    return response()->json(['error' => 'Unable to fetch the image.'], 404);
})->where('path', '.*');

