<?php

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
|
| These routes require authentication
|
*/

$router->group(['prefix' => 'user'], function () use ($router) {
    // Get user profile
    $router->get('/profile', 'api\UserController@profile');
    
    // Update user profile
    $router->put('/profile', 'api\UserController@updateProfile');
    
    // Change password
    $router->post('/change-password', 'api\UserController@changePassword');
    
    // Logout
    $router->post('/logout', 'api\UserController@logout');
});