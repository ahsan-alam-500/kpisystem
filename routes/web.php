<?php

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "No Content";
});

Route::get('users/export', [UserController::class, 'export']);
