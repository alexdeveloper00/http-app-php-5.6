<?php 

use Http\Route;

use App\Controllers\SiteController;
use App\Middlewares\BearerAuthMiddleware;

Route::verbs(['GET'])->map('/home')->handler([SiteController::class, 'index'])->middleware(BearerAuthMiddleware::class);
return Route::dispatch();