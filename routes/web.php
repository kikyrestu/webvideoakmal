<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

// ─── Public Pages ────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index']);
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/video/{slug}', [VideoController::class, 'show'])->name('videos.show');
Route::get('/group/{slug}', [GroupController::class, 'show'])->name('groups.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');

// ─── Interactive (throttled, session + CSRF) ─────────────────
Route::post('/videos/{id}/like', [LikeController::class, 'toggle'])
    ->middleware('throttle:10,1')
    ->name('videos.like');

Route::post('/videos/{id}/rate', [RatingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('videos.rate');

Route::post('/videos/{id}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('videos.comment');
