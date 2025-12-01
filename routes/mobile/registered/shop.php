<?php

/*
|--------------------------------------------------------------------------
| Authenticated Shop Routes
|--------------------------------------------------------------------------
|
| These routes require authentication for additional features
|
*/

$router->group(['prefix' => 'shops'], function () use ($router) {
    // Additional authenticated shop endpoints can be added here
    // For now, most shop functionality is available publicly
    
    // Example: Shop management routes for shop owners
    // $router->put('/{id}', 'api\ShopController@update');
    // $router->delete('/{id}', 'api\ShopController@destroy');
});