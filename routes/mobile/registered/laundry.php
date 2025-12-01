<?php

/*
|--------------------------------------------------------------------------
| Authenticated Laundry Routes
|--------------------------------------------------------------------------
|
| These routes require authentication
|
*/

$router->group(['prefix' => 'laundry'], function () use ($router) {
    // Get all laundries (with filters)
    $router->get('/', 'api\LaundryController@readAll');
    
    // Get laundries by user ID
    $router->get('/user/{user_id}', 'api\LaundryController@whereUserId');
    
    // Get authenticated user's laundries
    $router->get('/my-orders', 'api\LaundryController@myLaundries');
    
    // Create new laundry order
    $router->post('/', 'api\LaundryController@store');
    
    // Get laundry by ID
    $router->get('/{id}', 'api\LaundryController@show');
    
    // Update laundry status
    $router->put('/{id}/status', 'api\LaundryController@updateStatus');
    
    // Claim laundry order
    $router->post('/claim', 'api\LaundryController@claim');
    
    // Get unclaimed laundries
    $router->get('/status/unclaimed', 'api\LaundryController@getUnclaimed');
});