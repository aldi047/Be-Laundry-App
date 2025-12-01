<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$router->group(['prefix' => 'api'], function () use ($router) {
    
    // Health check endpoint
    $router->get('/health', function () {
        return response()->json(['status' => 'OK', 'message' => 'API is running']);
    });

    /*
    |--------------------------------------------------------------------------
    | Public Authentication Routes
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => 'auth'], function () use ($router) {
        // User registration
        $router->post('/register', controller_path('AuthController@register'));
        
        // User login
        $router->post('/login', controller_path('AuthController@login'));

        // Refresh token
        $router->get('/refresh', controller_path('AuthController@refresh'));
        
    });

            
    // User login
    $router->post('/login', controller_path('AuthController@loginUsingEmailPassword'));

    // Authenticated user detail
    $router->group(['prefix' => 'user', 'middleware' => 'jwt'], function () use ($router) {
        $router->get('/', controller_path('UserController@readAll', 'api'));
        $router->get('/me', controller_path('UserController@profile', 'api'));
    });

    /*
    |--------------------------------------------------------------------------
    | Public Shop Routes
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => 'shop', 'middleware' => 'jwt'], function () use ($router) {
        // Get all shops
        $router->get('/', controller_path('ShopController@readAll', 'api'));
        
        // Get shop by ID
        $router->get('/{id}', controller_path('ShopController@show', 'api'));
        
        // Get recommended shops
        $router->get('/recommendation/limit', controller_path('ShopController@readRecommendationLimit', 'api'));
        
        // Search shops by city
        $router->get('/search/city', controller_path('ShopController@searchByCity', 'api'));

        // Search shops by city with path parameter
        $router->get('/search/city/{city}', controller_path('ShopController@searchByCityPath', 'api'));
        
        // Get shops with delivery service
        $router->get('/services/delivery', controller_path('ShopController@getDeliveryShops', 'api'));
        
        // Get shops with pickup service
        $router->get('/services/pickup', controller_path('ShopController@getPickupShops', 'api'));
        
        // Get nearby shops
        $router->post('/nearby', controller_path('ShopController@getNearbyShops', 'api'));
        
        // Get shop statistics
        $router->get('/{id}/statistics', controller_path('ShopController@getStatistics', 'api'));
    });

    /*
    |--------------------------------------------------------------------------
    | Public Promo Routes
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => 'promo', 'middleware' => 'jwt'], function () use ($router) {
        // Get all promos
        $router->get('/', controller_path('PromoController@readAll', 'api'));
        
        // Get limited promos
        $router->get('/limit', controller_path('PromoController@readLimit', 'api'));
        
        // Get promo by ID
        $router->get('/{id}', controller_path('PromoController@show', 'api'));
        
        // Get promos by shop
        $router->get('/shop/{shopId}', controller_path('PromoController@getByShop', 'api'));
        
        // Get featured promos
        $router->get('/featured/list', controller_path('PromoController@getFeatured', 'api'));
        
        // Search promos
        $router->get('/search/query', controller_path('PromoController@search', 'api'));
        
        // Get promos by minimum discount
        $router->get('/discount/minimum', controller_path('PromoController@getByMinDiscount', 'api'));
        
        // Get promos by price range
        $router->get('/price/range', controller_path('PromoController@getByPriceRange', 'api'));
        
        // Get promo statistics
        $router->get('/statistics/overview', controller_path('PromoController@getStatistics', 'api'));
    });

    /*
    |--------------------------------------------------------------------------
    | Laundry Routes (Protected)
    |--------------------------------------------------------------------------
    */
    $router->group(['prefix' => 'laundry', 'middleware' => 'jwt'], function () use ($router) {
        // Get all laundry
        $router->get('/', controller_path('LaundryController@readAll', 'api'));

        // Get laundry for authenticated user
        $router->get('/user', controller_path('LaundryController@myLaundries', 'api'));

        // Get laundry by user ID
        $router->get('/user/{user_id}', controller_path('LaundryController@whereUserId', 'api'));

        // Claim laundry by claim code
        $router->post('/claim', controller_path('LaundryController@claim', 'api'));
    });

});