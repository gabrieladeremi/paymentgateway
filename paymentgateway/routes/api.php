<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Controllers\TransferController;
use App\Models\Transfer;

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

Route::middleware('auth:api')->get('/', function (Request $request) {
    return $request->user();
});

//Route::post('/register', [RegistrationController::class, 'index']);

Route::prefix('auth')
    ->middleware(['api'])
        ->group(static function () {

            Route::post('/register', [RegistrationController::class, 'store']);

            Route::get('/login', [LoginController::class, 'login']);

            Route::get('/user', [JwtAuthController::class, 'user']);

            Route::post('/token-refresh', [JwtAuthController::class, 'refresh']);

            Route::post('/signout', [JwtAuthController::class, 'signout']);

            Route::post('/verify/account', [TransferController::class, 'verifyAccountInfo']);

            Route::post('/recipient', [TransferController::class, 'recipient']);

            Route::post('/initiateTransfer', [TransferController::class, 'initiateTransfer']);

            Route::post('/finalizeTransfer', [TransferController::class, 'finalizeTransfer']);

        });




        // Route::get('/payment/callback', [TransferController::class, 'handleGatewayCallback']);

