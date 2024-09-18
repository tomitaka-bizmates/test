<?php

use Illuminate\Support\Facades\Route;
use Nuwave\Lighthouse\Http\GraphQLController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/graphql', [GraphQLController::class, 'query']);
// Route::get('/graphql-playground', [GraphQLController::class, 'playground']);