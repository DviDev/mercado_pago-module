<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Modules\MercadoPago\Http\Controllers\PaymentStatusSuccessController;

Route::prefix('mp')->group(function() {


    Route::post('payment/status/company/3/vkjbdkjbf4jjp5bj7hjvgh0dfozjktk8k0jk25165g',
        [PaymentStatusSuccessController::class, 'processWebhookCompanyId3'])->withoutMiddleware('web');

    Route::post('payment/status/company/4/s4n45b34p5n34pon77pn586pn2o4ib9ml6no3yuf3',
        [PaymentStatusSuccessController::class, 'processWebhookCompanyId4'])->withoutMiddleware('web');

    Route::post('payment/status/company/5/enoitoni45onrtoon45iub6onfon3epmmhpm7bi',
        [PaymentStatusSuccessController::class, 'processWebhookCompanyId5'])->withoutMiddleware('web');
});
