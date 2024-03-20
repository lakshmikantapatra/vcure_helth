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
        $r->post('(:num)/practice', 'Doctor::postPractice/$1');
        $r->get('(:num)/practice', 'Doctor::getPractice/$1');
        $r->post('practice/(:num)/schedule', 'Doctor::postPracticeSchedule/$1');
        $r->put('practice/(:num)', "Doctor::updatePractice/$1");
        $r->put('practice/(:num)/schedule', 'Doctor::updatePracticeSchedule/$1');
        $r->delete('practice/(:num)', 'Doctor::deletePractice/$1');
        $r->get('appointments/filter', 'Doctor::getAppointmentsFilters');
    });

    // ======= CLINIC ROUTES ========
    $api->group('clinic', function ($r) {
        $r->get('/', 'Clinic::index');
    });

    // ======= PATIENT ROUTES ========
    $api->group('patient', function ($r) {
        $r->get('/', 'Patient::index');
        $r->post('register', 'Patient::register');
        $r->put('verify/(:segment)', 'Doctor::verifyOTP/$1');
        $r->post('login', 'Doctor::login');
    });

    // ======= PUBLIC ROUTES ========
    $api->group('public', function ($r) {
        $r->get('/', 'Public::index');
    });
});
// ########## END API ROUTES ##########
