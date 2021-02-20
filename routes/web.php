<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

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

Auth::routes();

Route::group(
    ['middleware' => 'auth', 'prefix' => 'dashboard', 'as' => 'dashboard.'],
    function (Router $routes) {
        $routes->get('/', Dashboard\DashboardController::class)->name('dashboard');
        $routes->resource('posts', Dashboard\PostsController::class);
    }
);

Route::get('/{post:slug}', [BlogController::class, 'post'])->name('post');
Route::get('/', [BlogController::class, 'home'])->name('home');
