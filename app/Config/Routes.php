<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->group("api", function ($routes) {
    $routes->group("auth", function ($routes) {
        $routes->post("register", "AuthController::register");
        $routes->post("login", "AuthController::login");
        $routes->post("forgot-password", "AuthController::forgot_password");
        $routes->post("reset-password", "AuthController::reset_password");
        $routes->post("verify-otp", "AuthController::verify_otp");
        $routes->post("reset-password", "AuthController::reset_password");
    });
    $routes->group("products", function ($routes) {
        $routes->post("add", "ProductController::create_product", ['filter' => 'authFilter']);
        $routes->post("edit", "ProductController::update_product", ['filter' => 'authFilter']);
        $routes->get("delete/(:num)", "ProductController::delete_product/$1", ['filter' => 'authFilter']);
        $routes->get("(:any)", "ProductController::read_products/$1", ['filter' => 'authFilter']);
        $routes->get("/", "ProductController::read_products", ['filter' => 'authFilter']);
    });
});
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
