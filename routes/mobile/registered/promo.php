<?php

/*
|--------------------------------------------------------------------------
| Authenticated Promo Routes
|--------------------------------------------------------------------------
|
| These routes require authentication for additional features
|
*/

$router->group(['prefix' => 'promos'], function () use ($router) {
    // Additional authenticated promo endpoints can be added here
    // For now, most promo functionality is available publicly
    
    // Example: Promo management routes for shop owners
    // $router->post('/', 'api\PromoController@store');
    // $router->put('/{id}', 'api\PromoController@update');
    // $router->delete('/{id}', 'api\PromoController@destroy');
});