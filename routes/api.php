<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Api\OrderController;
use App\Models\Fedex;
use Illuminate\Support\Facades\Route;

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
Route::group(['prefix' => 'auth'], function () {
//    Route::post('login', [Api\AuthController::class,'login']);
//    Route::post('register', [Api\AuthController::class,'register']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', [Api\AuthController::class, 'logout']);
        Route::get('user', [Api\AuthController::class, 'user']);
    });
});

Route::post('auth/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerificationController::class, 'verifyRegisterUser']);
Route::group(['prefix' => '/auth', ['middleware' => 'checkuserlogin']], function () {
    Route::post('onsuccessFullLogin/{token}', [Api\AuthController::class, 'onsuccessFullLogin']);
});

Route::group(['prefix' => '/auth', ['middleware' => 'throttle:20,5']], function () {
    Route::post('/register', [Api\Auth\RegisterController::class, 'register']);
    Route::post('/login', [Api\Auth\LoginController::class, 'login']);
    Route::post('/facebook-login', [Api\Auth\LoginController::class, 'facebookLogin']);
    Route::post('/google-login', [Api\Auth\LoginController::class, 'googleLogin']);
    Route::post('/apple-login', [Api\Auth\LoginController::class, 'appleLogin']);
    Route::post('logout', [Api\AuthController::class, 'logout']);
});

//===============================All the below route should be in Secure routes==============================
Route::group(['middleware' => 'auth:api'], function () {
        Route::get('categories-secure', [Api\CategoryController::class, 'index']);
        // Route::post('forgot-password', [Api\Auth\ForgotPasswordController::class, 'check']);
        // Route::post('verify/otp', [Api\Auth\ForgotPasswordController::class, 'verifyOtp']);
        // Route::post('password/reset', [Api\Auth\ResetPasswordController::class, 'reset']);
        Route::group(['prefix' => '/categories'], function () {
            //Route::get('/', [Api\CategoryController::class, 'index']);
            //Route::get('categories', [Api\CategoryController::class, 'index']);
            Route::get('/{category}', [Api\CategoryController::class, 'show']);
            Route::post('/', [Api\CategoryController::class, 'store']);
        });
        Route::group(['prefix' => '/user'], function () {
            // Route::get('/depositfund', [Api\StripeController::class, 'depositFund']);
            Route::get('detail/', [Api\UserController::class, 'detail']);
            Route::get('detail/{id}', [Api\UserController::class, 'detailById']);
            // Route::post('upload', [Api\UserController::class, 'upload']);
            Route::get('conversations', [Api\UserController::class, 'conversations']);
            Route::get('{user}/messages', [Api\UserController::class, 'messages']);
            Route::post('{user}/send-message', [Api\UserController::class, 'sendMessage']);
            Route::post('/deleteAccount/{id}', [Api\UserController::class, 'deleteAccount']);
            Route::post('/cancelDelete/{id}', [Api\UserController::class, 'cancelDelete']);
            Route::patch('/', [Api\UserController::class, 'update']);
            Route::get('/refresh/{user}', [Api\UserController::class, 'refreshOnboardingUrl']);
            Route::get('/checkAccount/{account}', [Api\StripeController::class, 'checkAccount']);
            Route::post('saveAddress/', [Api\SaveAddressController::class, 'create']);

        });
        Route::group(['prefix' => '/products'], function () {
            Route::post('/', [Api\ProductController::class, 'store']);
            Route::patch('/{product:guid}', [Api\ProductController::class, 'update']);
            Route::patch('/ratings/{product:guid}', [Api\ProductController::class, 'ratings']);
            Route::get('/checkRatings/{productId}/{userId}/{orderId}', [Api\ProductController::class, 'checkRatings']);
            Route::get('/self/', [Api\ProductController::class, 'self']);
            // HOTFIX
            // @TODO check why /upload is not working maybe another route with the same name (GIVING 404 on /upload route) is declared.
            Route::post('image-upload/{product:guid}', [Api\ProductController::class, 'upload']);
            Route::post('saved-users/{product:guid}', [Api\ProductController::class, 'saved']);
            Route::get('saved', [Api\ProductController::class, 'getSaved']);
            Route::get('getSaveByUser', [Api\ProductController::class, 'getSaveByUser']);
            Route::get('saved/{id}', [Api\ProductController::class, 'getSavedbyId']);
            Route::post('/{product:guid}/offer', [Api\ProductController::class, 'offer']);
            Route::delete('media/{media:guid}', [Api\ProductController::class, 'deleteMedia']);
            Route::get('offers/buying', [Api\ProductController::class, 'getBuyingOffers']);
            Route::get('offers/selling', [Api\ProductController::class, 'getSellingOffers']);
            
        });
        Route::group(['prefix' => '/offer'], function () {
            Route::post('status/{offer:guid}', [Api\OfferController::class, 'statusHandler']);
            Route::post('/{offer:guid}', [Api\OfferController::class, 'pendingOffer']);
            Route::post('offerCancel/{id}', [Api\OfferController::class, 'cancelOffer']);
        });
        Route::group(['prefix' => '/notifications'], function () {
            Route::get('/get', [Api\NotificationController::class, 'index']);
            Route::get('/count', [Api\NotificationController::class, 'count']);
            Route::patch('/update/{notificationId}', [Api\NotificationController::class, 'update']);
        });
        Route::get('/message/conversations/{productId}', [Api\MessageController::class, 'conversations']);
        Route::get('/message/conversations', [Api\MessageController::class, 'getUserConversations']);
        Route::post('/message/saveAssociated', [Api\MessageController::class, 'saveAssociated']);
        Route::get('/message/{recipientId}/{productId}', [Api\MessageController::class, 'show']);
        Route::get('/message/checkMessage', [Api\MessageController::class, 'checkMessage']);
        Route::Resources([
            'order' => \Api\OrderController::class,
            'prices' => \Api\PricesController::class,
        ]);
        Route::get('/order/tracking/{id}', [Api\OrderController::class, 'tracking']);
        Route::patch('/order/packed/{id}', [Api\OrderController::class, 'packed']);
        Route::post('/order/ratecalculator', [Api\OrderController::class, 'ratecalculator']);
        Route::post('/order/validatePostalCode', [Api\OrderController::class, 'verifyAddressEasyPost']);
        Route::post('/order/validateAddress', [Api\OrderController::class, 'validateAddress']);
        Route::get('/order/getTrsutedUserData/{id}', [Api\OrderController::class, 'getTrsutedUserData']);
        Route::post('/order/delivered/{id}', [Api\OrderController::class, 'delivered']);
        Route::post('/order/notdelivered/{id}', [Api\OrderController::class, 'notdelivered']);
        Route::group(['prefix' => '/stripe'], function () {
            Route::get('/balance', [Api\StripeController::class, 'balance']);
            Route::get('/Transactions', [Api\StripeController::class, 'getTransactions']);
            Route::get('/PaymentIntents/{id}', [Api\StripeController::class, 'getPaymentIntents']);
            Route::get('/paymentsStatus', [Api\StripeController::class, 'getPaymentsStatus']);
            Route::get('/updateUserAccount', [Api\StripeController::class, 'updateUserAccount']);
            Route::get('/addUserAccforPostAdd/{uuid}', [Api\StripeController::class, 'addUserAccforPostAdd']);
            Route::get('/getBankAccounts', [Api\StripeController::class, 'getBankAccounts']);
        });
        Route::group(['prefix' => '/prices'], function () {
            Route::get('/getbyId/{id}', [Api\PricesController::class, 'getbyId']);
        });
        Route::post('refund', [Api\RefundController::class, 'store']);
        Route::patch('refund/{id}/{status}', [Api\RefundController::class, 'update']);
});

Route::group(['prefix' => '/stripe', ['middleware' => 'auth:api']], function () {
    Route::get('/generate/{product:guid}/{price}', [Api\StripeController::class, 'generate']);
    Route::get('/feature', [Api\StripeController::class, 'feature']);
    Route::get('/hire', [Api\StripeController::class, 'hire']);
});
//===============================All the below route should be in Secure routes==============================

//====================================== PUBLIC ROUTES =========================================

//Route::patch('products/{id}',[Api\ProductController::class,'update']);
Route::delete('products/{id}', [Api\ProductController::class, 'destroy']);

Route::group(['prefix' => '/categories', ['middleware' => 'throttle:20,5']], function () {
    Route::get('/tabs', [Api\CategoryController::class, 'tabs']);
    Route::get('tabs/list', [Api\CategoryController::class, 'tabs']);
    Route::get('/product-attributes/{category}', [Api\CategoryController::class, 'productAttributes']);
    Route::get('/', [Api\CategoryController::class, 'index']);
});

Route::group(['prefix' => '/products'], function () {
    Route::get('/', [Api\ProductController::class, 'index']);
    Route::get('/show/{product:guid}', [Api\ProductController::class, 'show']);
    Route::get('media/{product:guid}', [Api\ProductController::class, 'media']);
    Route::get('/search', [Api\ProductController::class, 'search']);
    Route::post('/checkEmailReview/{id}', [Api\ProductController::class, 'checkEmailReview']);
    Route::get('/userRating/{product:user_id}', [Api\ProductController::class, 'userRating']);
    Route::get('/getAttributes/{categoryID}', [Api\ProductController::class, 'getAttributes']);
    Route::get('/getProductAttributes/{id}', [Api\ProductController::class, 'getProductAttributes']);
});

Route::group(['prefix' => '/location'], function () {
    Route::post('/getCityStatebyPostal/{zipcode}', [Api\CityStateController::class, 'getCityStatebyPostal']);
});
Route::group(['prefix' => '/city'], function () {
    Route::get('/', [Api\CityStateController::class, 'index']);
    Route::get('/states/{id}', [Api\CityStateController::class, 'getCityByStates']);
});
Route::group(['prefix' => '/state'], function () {
    Route::get('/', [Api\CityStateController::class, 'getState']);
    Route::get('country/{id}', [Api\CityStateController::class, 'getStateByCountry']);
});
Route::group(['prefix' => '/countries'], function () {
    Route::get('/', [Api\CityStateController::class, 'getCountries']);
});
Route::post('/getCityStatebyPostal/{zipcode}', [Api\CityStateController::class, 'getCityStatebyPostal']);
Route::post('forgot-password', [Api\Auth\ForgotPasswordController::class, 'check']);
Route::post('verify/otp', [Api\Auth\ForgotPasswordController::class, 'verifyOtp']);
Route::post('verify/Auth/otp', [Api\Auth\ForgotPasswordController::class, 'verifyAuthOtp']);
Route::post('password/reset', [Api\Auth\ResetPasswordController::class, 'reset']);
Route::group(['prefix' => '/user'], function () {
    Route::post('upload', [Api\UserController::class, 'upload']);
});