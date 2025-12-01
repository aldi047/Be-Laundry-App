<?php
$router->group([
    'prefix' => 'mobile',
    'namespace' => MOBILE_DIR,
    'middleware' => 'jwt.auth'
], function () use ($router) {
    $router->get('/', function () {
        echo sha1(md5(rand(0, 100000)));
    });
    $router->group(['namespace' => MAIN_DIR], function () use ($router) {
        include 'registered/user.php';
        include 'registered/laundry.php';
        include 'registered/shop.php';
        include 'registered/promo.php';
    });
});