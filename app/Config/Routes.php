<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ########## API ROUTES ##########
$routes->group('api/v1', ['namespace' => 'App\Controllers\api\v1'], function ($api) {
    // ======= DOCTOR ROUTES ========
    $api->group('doctor', function ($r) {
        $r->get('/', 'Doctor::index');
        $r->post('registration', 'Doctor::registration');
        $r->put('personal_details/(:num)', 'Doctor::personalDetails/$1');
        $r->post('login', 'Doctor::login');
        $r->post('reset_password', 'Doctor::resetPassword');
        $r->put('verify/(:segment)', 'Doctor::verifyOTP/$1');
        $r->put('new_password/(:num)', 'Doctor::newPassword/$1');
    });
});
// ########## END API ROUTES ##########
