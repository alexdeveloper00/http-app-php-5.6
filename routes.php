<?php 

use Http\Route;

use App\Controllers\SiteController;
use App\Middlewares\BearerAuthMiddleware;

Route::verbs(['GET'])->map('/home')->handler([SiteController::class, 'index'])->middleware(BearerAuthMiddleware::class);
Route::verbs(['GET'])->map('/users')->handler([SiteController::class, 'users']);
Route::verbs(['GET'])->map('/generals')->handler([SiteController::class, 'generals']);
return Route::dispatch();