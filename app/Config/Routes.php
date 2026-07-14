<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ================================================================
// PUBLIC ROUTES (No authentication required)
// ================================================================

// Root redirect to portal selection
$routes->get('/', static function () {
    return redirect()->to('/portal');
});

// Dashboard redirect (backward compatibility - redirect to role-based dashboard)
$routes->get('dashboard', static function () {
    $role = session()->get('role');
    return redirect()->to(match ($role) {
        'snd_admin' => '/admin/dashboard',
        'owner'     => '/owner/dashboard',
        'customer'  => '/customer/dashboard',
        default     => '/portal',
    });
}, ['filter' => 'auth']);

// Bare-path redirects: stored notification links and older views use
// unprefixed URLs — bounce each to the logged-in role's own section.
$bareRedirects = [
    'rentals'        => ['snd_admin' => '/admin/rentals',         'owner' => '/owner/rentals',       'customer' => '/customer/rentals'],
    'rentals/(:num)' => ['snd_admin' => '/admin/rentals/%s',      'owner' => '/owner/rentals/%s',    'customer' => '/customer/rentals/%s'],
    'wallet'         => ['snd_admin' => '/admin/wallet/requests', 'owner' => '/owner/wallet',        'customer' => '/customer/wallet'],
    'wallet/topup'   => ['snd_admin' => '/admin/wallet/requests', 'owner' => '/owner/wallet',        'customer' => '/customer/wallet/topup'],
    'profile'        => ['snd_admin' => '/admin/profile',         'owner' => '/owner/profile',       'customer' => '/customer/profile'],
    'notifications'  => ['snd_admin' => '/admin/notifications',   'owner' => '/owner/notifications', 'customer' => '/customer/notifications'],
];
foreach ($bareRedirects as $barePath => $map) {
    $routes->get($barePath, static function ($id = null) use ($map) {
        $target = $map[session()->get('role') ?? ''] ?? '/portal';
        return redirect()->to($id === null ? $target : sprintf($target, $id));
    }, ['filter' => 'auth']);
}

// Portal selection page
$routes->get('portal', 'AuthController::portal');

// Customer authentication (blue)
$routes->get('login',    'AuthController::login');
$routes->post('login',   'AuthController::loginProcess');
$routes->get('register', 'AuthController::register');
$routes->post('register','AuthController::registerProcess');

// Owner authentication (green)
$routes->get('owner/login',     'AuthController::ownerLogin');
$routes->post('owner/login',    'AuthController::ownerLoginProcess');
$routes->get('owner/register',  'AuthController::ownerRegister');
$routes->post('owner/register', 'AuthController::ownerRegisterProcess');

// Admin authentication (crimson)
$routes->get('admin/login',     'AuthController::adminLogin');
$routes->post('admin/login',    'AuthController::adminLoginProcess');
$routes->get('admin/register',  'AuthController::adminRegister');
$routes->post('admin/register', 'AuthController::adminRegisterProcess');

// Password Reset
$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::sendResetCode');
$routes->get('reset-password', 'AuthController::resetPasswordForm');
$routes->post('reset-password', 'AuthController::resetPassword');

// Logout (shared)
$routes->get('logout', 'AuthController::logout');

// Email verification
$routes->get('verify-email',    'AuthController::showVerifyEmail');
$routes->post('verify-email',   'AuthController::processVerifyEmail');
$routes->post('resend-verify',  'AuthController::resendVerifyEmail');

// Reviews (public - anyone can view)
$routes->get('reviews', 'ReviewController::index');

// Temporary SMTP test — REMOVE after testing
$routes->get('test-email', 'TestEmailController::index');


// ================================================================
// CUSTOMER ROUTES (/customer/*)
// ================================================================

$routes->group('customer', ['filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Rentals
    $routes->get('rentals', 'RentalController::index');
    $routes->get('rentals/create', 'RentalController::create');
    $routes->get('rentals/check-availability', 'RentalController::checkAvailability');
    $routes->get('rentals/check-availability-range', 'RentalController::checkAvailabilityRange');
    $routes->post('rentals/store', 'RentalController::store');
    $routes->get('rentals/(:num)', 'RentalController::show/$1');
    $routes->get('rentals/(:num)/detail.json', 'RentalController::detailJson/$1');
    $routes->post('rentals/(:num)/cancel-booking', 'RentalController::cancelBooking/$1');

    // Wallet
    $routes->get('wallet', 'WalletController::index');
    $routes->get('wallet/topup', 'WalletController::topUpForm');
    $routes->post('wallet/topup', 'WalletController::topUp');

    // Profile
    $routes->get('profile', 'UserController::profile');
    $routes->post('profile/update', 'UserController::updateProfile');
    $routes->post('profile/ocr-extract', 'UserController::ocrExtract');

    // Notifications
    $routes->get('notifications', 'NotificationController::index');
    $routes->get('notifications/fetch', 'NotificationController::fetch');
    $routes->get('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/mark-all-read', 'NotificationController::markAllRead');

    // Reviews
    $routes->post('reviews/store/(:num)', 'ReviewController::store/$1');

    // Payment Gateways (for item viewing)
    $routes->get('payment-gateways/for-item/(:num)', 'PaymentGatewayController::forItem/$1');
});


// ================================================================
// OWNER ROUTES (/owner/*)
// ================================================================

$routes->group('owner', ['filter' => 'owner'], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Items
    $routes->get('items', 'ItemController::index');
    $routes->get('items/create', 'ItemController::create');
    $routes->post('items/store', 'ItemController::store');
    $routes->get('items/edit/(:num)', 'ItemController::edit/$1');
    $routes->post('items/update/(:num)', 'ItemController::update/$1');
    $routes->post('items/delete/(:num)', 'ItemController::delete/$1');

    // Rentals
    $routes->get('rentals', 'RentalController::index');
    $routes->get('rentals/create', 'RentalController::create');
    $routes->post('rentals/store', 'RentalController::store');
    $routes->get('rentals/check-availability', 'RentalController::checkAvailability');
    $routes->get('rentals/check-availability-range', 'RentalController::checkAvailabilityRange');
    $routes->get('rentals/(:num)', 'RentalController::show/$1');
    $routes->get('rentals/(:num)/detail.json', 'RentalController::detailJson/$1');
    $routes->get('rentals/(:num)/return', 'RentalController::confirmReturn/$1');
    $routes->get('rentals/(:num)/return-info', 'RentalController::returnInfo/$1');
    $routes->post('rentals/(:num)/return', 'RentalController::processReturn/$1');
    $routes->post('rentals/(:num)/activate', 'RentalController::activate/$1');
    $routes->post('rentals/(:num)/delete', 'RentalController::delete/$1');
    $routes->post('rentals/(:num)/confirm-payment', 'RentalController::confirmPayment/$1');
    $routes->post('rentals/(:num)/cancel-booking', 'RentalController::cancelBooking/$1');

    // Reports
    $routes->get('reports', 'ReportController::index');
    $routes->get('reports/export/csv', 'ReportController::exportCSV');

    // Reviews (delete)
    $routes->post('reviews/delete/(:num)', 'ReviewController::delete/$1');

    // Payment Gateways
    $routes->get('payment-gateways', 'PaymentGatewayController::index');
    $routes->get('payment-gateways/for-item/(:num)', 'PaymentGatewayController::forItem/$1');
    $routes->post('payment-gateways/store', 'PaymentGatewayController::store');
    $routes->get('payment-gateways/edit/(:num)', 'PaymentGatewayController::edit/$1');
    $routes->post('payment-gateways/update/(:num)', 'PaymentGatewayController::update/$1');
    $routes->post('payment-gateways/delete/(:num)', 'PaymentGatewayController::delete/$1');

    // Store Wallet / Earnings
    $routes->get('wallet', 'StoreWalletController::index');

    // User profiles (view customer profiles)
    $routes->get('users/(:num)/profile', 'UserController::viewCustomerProfile/$1');
    $routes->get('users/(:num)/profile.json', 'UserController::profileJson/$1');
    $routes->post('users/(:num)/verify-id', 'UserController::verifyId/$1');
    $routes->post('users/(:num)/unverify-id', 'UserController::unverifyId/$1');

    // Notifications
    $routes->get('notifications', 'NotificationController::index');
    $routes->get('notifications/fetch', 'NotificationController::fetch');
    $routes->get('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/mark-all-read', 'NotificationController::markAllRead');

    // Profile
    $routes->get('profile', 'UserController::profile');
    $routes->post('profile/update', 'UserController::updateProfile');
    $routes->post('profile/ocr-extract', 'UserController::ocrExtract');
});


// ================================================================
// ADMIN ROUTES (/admin/*)
// ================================================================

$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Users management
    $routes->get('users', 'UserController::index');
    $routes->get('users/create', 'UserController::create');
    $routes->post('users/store', 'UserController::store');
    $routes->get('users/edit/(:num)', 'UserController::edit/$1');
    $routes->post('users/update/(:num)', 'UserController::update/$1');
    $routes->post('users/delete/(:num)', 'UserController::delete/$1');

    // Categories management
    $routes->get('categories', 'CategoryController::index');
    $routes->get('categories/create', 'CategoryController::create');
    $routes->post('categories/store', 'CategoryController::store');
    $routes->get('categories/edit/(:num)', 'CategoryController::edit/$1');
    $routes->post('categories/update/(:num)', 'CategoryController::update/$1');
    $routes->post('categories/delete/(:num)', 'CategoryController::delete/$1');

    // ID Verifications
    $routes->get('verifications', 'UserController::verificationList');

    // Wallet management
    $routes->get('wallet', 'StoreWalletController::index');
    $routes->get('wallet/requests', 'WalletController::topupRequests');
    $routes->post('wallet/approve/(:num)', 'WalletController::approveTopup/$1');
    $routes->post('wallet/reject/(:num)', 'WalletController::rejectTopup/$1');

    // Rentals (full management, same as owner)
    $routes->get('rentals', 'RentalController::index');
    $routes->get('rentals/create', 'RentalController::create');
    $routes->post('rentals/store', 'RentalController::store');
    $routes->get('rentals/check-availability', 'RentalController::checkAvailability');
    $routes->get('rentals/check-availability-range', 'RentalController::checkAvailabilityRange');
    $routes->get('rentals/(:num)', 'RentalController::show/$1');
    $routes->get('rentals/(:num)/detail.json', 'RentalController::detailJson/$1');
    $routes->get('rentals/(:num)/return', 'RentalController::confirmReturn/$1');
    $routes->get('rentals/(:num)/return-info', 'RentalController::returnInfo/$1');
    $routes->post('rentals/(:num)/return', 'RentalController::processReturn/$1');
    $routes->post('rentals/(:num)/activate', 'RentalController::activate/$1');
    $routes->post('rentals/(:num)/delete', 'RentalController::delete/$1');
    $routes->post('rentals/(:num)/confirm-payment', 'RentalController::confirmPayment/$1');
    $routes->post('rentals/(:num)/cancel-booking', 'RentalController::cancelBooking/$1');

    // Items (same management as owner; scoped to own items)
    $routes->get('items', 'ItemController::index');
    $routes->get('items/create', 'ItemController::create');
    $routes->post('items/store', 'ItemController::store');
    $routes->get('items/edit/(:num)', 'ItemController::edit/$1');
    $routes->post('items/update/(:num)', 'ItemController::update/$1');
    $routes->post('items/delete/(:num)', 'ItemController::delete/$1');

    // Reports
    $routes->get('reports', 'ReportController::index');
    $routes->get('reports/export/csv', 'ReportController::exportCSV');

    // Reviews (delete)
    $routes->post('reviews/delete/(:num)', 'ReviewController::delete/$1');

    // Payment Gateways
    $routes->get('payment-gateways', 'PaymentGatewayController::index');
    $routes->get('payment-gateways/for-item/(:num)', 'PaymentGatewayController::forItem/$1');
    $routes->post('payment-gateways/store', 'PaymentGatewayController::store');
    $routes->get('payment-gateways/edit/(:num)', 'PaymentGatewayController::edit/$1');
    $routes->post('payment-gateways/update/(:num)', 'PaymentGatewayController::update/$1');
    $routes->post('payment-gateways/delete/(:num)', 'PaymentGatewayController::delete/$1');

    // Customer profile view (shared rentals views link here)
    $routes->get('users/(:num)/profile', 'UserController::viewCustomerProfile/$1');
    $routes->get('users/(:num)/profile.json', 'UserController::profileJson/$1');

    // Own profile
    $routes->get('profile', 'UserController::profile');
    $routes->post('profile/update', 'UserController::updateProfile');
    $routes->post('profile/ocr-extract', 'UserController::ocrExtract');

    // Notifications
    $routes->get('notifications', 'NotificationController::index');
    $routes->get('notifications/fetch', 'NotificationController::fetch');
    $routes->get('notifications/read/(:num)', 'NotificationController::markRead/$1');
    $routes->post('notifications/mark-all-read', 'NotificationController::markAllRead');
});