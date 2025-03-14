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
            Route::get('self', [Api\UserController::class, 'self']);
            // Route::post('upload', [Api\UserController::class, 'upload']);
            Route::get('conversations', [Api\UserController::class, 'conversations']);
            Route::get('{user}/messages', [Api\UserController::class, 'messages']);
            Route::post('{user}/send-message', [Api\UserController::class, 'sendMessage']);
            Route::post('/deleteAccount/{id}', [Api\UserController::class, 'deleteAccount']);
            Route::post('/cancelDelete/{id}', [Api\UserController::class, 'cancelDelete']);
            Route::patch('/', [Api\UserController::class, 'update']);
            Route::post('/profileupdate', [Api\UserController::class, 'profileUpdate']);
            Route::get('/refresh/{user}', [Api\UserController::class, 'refreshOnboardingUrl']);
            Route::get('/checkAccount/{account}', [Api\StripeController::class, 'checkAccount']);
            Route::post('saveAddress/', [Api\SaveAddressController::class, 'create']);
            Route::patch('secretquestion/', [Api\UserController::class, 'setSecretQuestion']);
            Route::post('change-password', [Api\Auth\ForgotPasswordController::class, 'changePassword']);
            Route::post('two-steps', [Api\UserController::class, 'twoStepsVerifications']);
            Route::post('third-party', [Api\UserController::class, 'thirdParty']);
            Route::post('fb-account', [Api\UserController::class, 'fbAccount']);
            Route::post('/updateaddress', [Api\UserController::class, 'updateAddress']);
            Route::get('/recentuserview', [Api\ProductController::class, 'recentUserView']);
            Route::delete('/deleteRecent', [Api\ProductController::class, 'deleteRecent']);
            Route::get('/getbid/{id}', [Api\BidsController::class, 'getBidsUserProduct']);
            Route::get('/getuserbid', [Api\BidsController::class, 'getUserBids']);
            Route::get('/getselleractivebid', [Api\BidsController::class, 'getSellerActiveBid']);
        });
        Route::group(['prefix' => '/products'], function () {
            Route::post('/add', [Api\ProductController::class, 'store']);
            Route::post('/{product:guid}', [Api\ProductController::class, 'update']);
            Route::patch('/ratings/{product:guid}', [Api\ProductController::class, 'ratings']);
            Route::get('/checkRatings/{productId}/{userId}/{orderId}', [Api\ProductController::class, 'checkRatings']);
            Route::get('/self/', [Api\ProductController::class, 'self']);
            Route::get('/self/{value}', [Api\ProductController::class, 'selfItems']);
            Route::get('/min', [Api\ProductController::class, 'getMin']);
            Route::get('/max', [Api\ProductController::class, 'getMax']);
            Route::get('/size', [Api\ProductController::class, 'getSizes']);
            Route::get('/shows/{product:guid}', [Api\ProductController::class, 'show']);
            // HOTFIX
            // @TODO check why /upload is not working maybe another route with the same name (GIVING 404 on /upload route) is declared.
            // Route::post('image-upload/{product:guid}', [Api\ProductController::class, 'upload']);
            Route::post('/image-upload', [Api\ProductController::class, 'upload']);
            Route::post('saved-users/{product:guid}', [Api\ProductController::class, 'saved']);
            Route::get('saved', [Api\ProductController::class, 'getSaved']);
            Route::get('getSaveByUser', [Api\ProductController::class, 'getSaveByUser']);
            Route::get('saved/{id}', [Api\ProductController::class, 'getSavedbyId']);
            Route::post('/{product:guid}/offer', [Api\ProductController::class, 'offer']);
            Route::delete('media/{media:guid}', [Api\ProductController::class, 'deleteMedia']);
            Route::get('offers/buying', [Api\ProductController::class, 'getBuyingOffers']);
            Route::get('offers/selling', [Api\ProductController::class, 'getSellingOffers']);
            Route::post('/upload', [Api\ProductController::class, 'Imgupload']);
        });
        Route::group(['prefix' => '/stock'], function(){
            Route::post('/add', [Api\StockController::class, 'store']);
            Route::get('/instock', [Api\ProductController::class, 'inStock']);
            Route::get('/outstock', [Api\ProductController::class, 'outStock']);
        });

        Route::group(['prefix' => '/watchlist'], function () {
            Route::post('/add', [Api\WatchListController::class, 'store']);    
        });
                
        Route::group(['prefix' => '/cart'], function () {
            Route::post('/add', [Api\UserCartController::class, 'store']);
            Route::get('/', [Api\UserCartController::class, 'index']);
            Route::get('/self', [Api\UserCartController::class, 'self']);    
            Route::post('/clear/{id}', [Api\UserCartController::class, 'clear']);    
            Route::delete('/destroy/{id}', [Api\UserCartController::class, 'destroy']);    
            Route::put('/update/{id}', [Api\UserCartController::class, 'update']); 
            Route::get('/count', [Api\UserCartController::class, 'count']); 
        });
        Route::group(['prefix' => '/notificationsettings'], function () {
            Route::post('/add', [Api\NotificationSettingController::class, 'store']);
            Route::get('/', [Api\NotificationSettingController::class, 'index']);
            Route::get('/show', [Api\NotificationSettingController::class, 'show']);    
            Route::post('/clear/{id}', [Api\NotificationSettingController::class, 'clear']);    
            Route::delete('/destroy/{id}', [Api\NotificationSettingController::class, 'destroy']);    
            Route::put('/update', [Api\NotificationSettingController::class, 'update']); 
            Route::get('/count', [Api\NotificationSettingController::class, 'count']); 
        });
        Route::group(['prefix' => '/offer'], function () {
            Route::post('status/{offer:guid}', [Api\OfferController::class, 'statusHandler']);
            Route::post('/{offer:guid}', [Api\OfferController::class, 'pendingOffer']);
            Route::post('offerCancel/{id}', [Api\OfferController::class, 'cancelOffer']);
        });
        Route::group(['prefix' => '/shipping'], function () {
            Route::post('/', [Api\ShippingDetailController::class, 'index']);
            Route::post('/add', [Api\ShippingDetailController::class, 'store']);
            Route::get('/self', [Api\ShippingDetailController::class, 'self']);
        });
        Route::group(['prefix' => '/notifications'], function () {
            Route::get('/get', [Api\NotificationController::class, 'index']);
            Route::get('/count', [Api\NotificationController::class, 'count']);
            Route::patch('/update/{notificationId}', [Api\NotificationController::class, 'update']);
        });
        Route::group(['prefix' => '/savelater'], function () {
            Route::post('/add', [Api\SaveCartLaterController::class, 'store']);
            Route::get('/', [Api\SaveCartLaterController::class, 'index']);
            Route::get('/getById/{id}', [Api\SaveCartLaterController::class, 'getById']);
            Route::get('/getByUser', [Api\SaveCartLaterController::class, 'getByUser']);
        });
        Route::group(['prefix' => '/savelater'], function () {
            Route::post('/add', [Api\SaveCartLaterController::class, 'store']);
            Route::get('/', [Api\SaveCartLaterController::class, 'index']);
            Route::get('/getById/{id}', [Api\SaveCartLaterController::class, 'getById']);
            Route::get('/getByUser', [Api\SaveCartLaterController::class, 'getByUser']);
        });
        Route::group(['prefix' => '/checkout'], function () {
            Route::post('/add', [Api\CheckoutController::class, 'store']);
            Route::get('/', [Api\CheckoutController::class, 'index']);
            Route::get('/self', [Api\CheckoutController::class, 'self']);
            Route::get('/destroy/{id}', [Api\CheckoutController::class, 'destroy']);
        });
        // Route::get('/message/conversations/{productId}', [Api\MessageController::class, 'conversations']);
        Route::get('/message/userConversations/', [Api\MessageController::class, 'conversations']);
        Route::get('/message/conversations', [Api\MessageController::class, 'getUserConversations']);
        Route::post('/message/saveAssociated', [Api\MessageController::class, 'saveAssociated']);
        Route::get('/message/{recipientId}/{productId}', [Api\MessageController::class, 'show']);
        // Route::get('/message/checkMessage', [Api\MessageController::class, 'checkMessage']);
        Route::Resources([
            'order' => \Api\OrderController::class,
            'prices' => \Api\PricesController::class,
            // 'transaction' => \Api\TransactionController::class,
        ]);
        Route::get('/order/getById/{id}', [Api\OrderController::class, 'getById']);
        Route::get('/order/{id}', [Api\OrderController::class, 'update']);
        Route::patch('/order/updateSeller/{id}', [Api\OrderController::class, 'updateSeller']);
        Route::get('/order/customer/comp/count', [Api\OrderController::class, 'customerOrderCompCount']);
        Route::get('/order/customer/pend/count', [Api\OrderController::class, 'customerOrderPendCount']);
        Route::get('/order/customer/refund/count', [Api\OrderController::class, 'customerOrderRefundCount']);
        Route::get('/order/counts', [Api\OrderController::class, 'getUserCompletedCount']);
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
        Route::group(['prefix' => '/order/customer'], function () {
            Route::get('/ongoing', [Api\OrderController::class, 'customerOngoingOrders']);
            Route::get('/completed', [Api\OrderController::class, 'customerCompletedOrders']);
            Route::get('/refund', [Api\OrderController::class, 'customerRefundOrders']);
            Route::get('/buyagainorders', [Api\OrderController::class, 'buyAgainOrders']);
        });
        Route::group(['prefix' => '/bids'], function () {
            Route::get('/getMax/{id}', [Api\BidsController::class, 'getMaxBids']);
            Route::post('/add', [Api\BidsController::class, 'store']);
            Route::post('/confirmedBids', [Api\BidsController::class, 'confirmedBids']);
            Route::post('/acceptbid', [Api\BidsController::class, 'acceptBid']);
            Route::post('/rejectbid', [Api\BidsController::class, 'rejectBid']);
            Route::get('/gettotalbidsproduct/{id}', [Api\BidsController::class, 'getTotalBidsProduct']);
        });

        Route::group(['prefix' => '/prices'], function () {
            Route::get('/getbyId/{id}', [Api\PricesController::class, 'getbyId']);
        });
        Route::group(['prefix' => '/ordersummary'], function () {
            Route::get('/', [Api\OrderController::class, 'getOrderSummary']);
            Route::get('/{id}', [Api\OrderController::class, 'getSingleOrderSummary']);
        });
        Route::post('refund', [Api\RefundController::class, 'store']);
        Route::patch('refund/{id}/{status}', [Api\RefundController::class, 'update']);
        Route::group(['prefix' => '/seller'], function () {
            Route::get('/', [Api\SellerDataController::class, 'index']);
            Route::post('/add', [Api\SellerDataController::class, 'store']);
            Route::post('/setbank', [Api\SellerDataController::class, 'setBankData']);
            Route::post('/update', [Api\SellerDataController::class, 'updateSellerData']);
            Route::get('/getshopdetails', [Api\SellerDataController::class, 'getShopDetails']);
            Route::get('/getshops/{id}', [Api\SellerDataController::class, 'getShopDetail']);
            Route::post('/saveSeller', [Api\SellerDataController::class, 'saveSeller']);
            Route::post('/updateBank', [Api\SellerDataController::class, 'updateBank']);
            Route::get('/getBankDetails', [Api\SellerDataController::class, 'getBankDetails']);
        });
        Route::group(['prefix' => '/transaction'], function () {
            Route::get('/usertransaction', [Api\TransactionController::class, 'getUserTransactions']);
            Route::get('/gettransactions', [Api\TransactionController::class, 'getTransactions']);
            Route::get('/getstripetransactions', [Api\TransactionController::class, 'getStripeTransactions']);
            
        });
        
});

Route::group(['prefix' => '/stripe', ['middleware' => 'auth:api']], function () {
    Route::post('/generate', [Api\StripeController::class, 'generate']);
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
    Route::get('/overAll', [Api\CategoryController::class, 'all']);
});
Route::group(['prefix' => '/seller'], function () {
    Route::get('/getfeatured', [Api\SellerDataController::class, 'getFeatured']);
});
Route::group(['prefix' => '/products'], function () {
    Route::get('/', [Api\ProductController::class, 'index']);
    Route::get('/show/{product:guid}', [Api\ProductController::class, 'show']);
    Route::get('media/{product:guid}', [Api\ProductController::class, 'media']);
    Route::post('/search', [Api\ProductController::class, 'searched']);
    Route::post('/checkEmailReview/{id}', [Api\ProductController::class, 'checkEmailReview']);
    Route::get('/userRating/{product:user_id}', [Api\ProductController::class, 'userRating']);
    Route::get('/getAttributes/{categoryID}', [Api\ProductController::class, 'getAttributes']);
    Route::get('/getProductAttributes/{id}', [Api\ProductController::class, 'getProductAttributes']);
    Route::get('/recent', [Api\ProductController::class, 'recentView']);
    Route::post('/createRecents', [Api\ProductController::class, 'createRecentView']);
    Route::post('/deleteRecent', [Api\ProductController::class, 'deleteRecent']);
    Route::delete('/destory/{guid}', [Api\ProductController::class, 'destroy']);
    Route::get('/storeproduct/{storeid}', [Api\ProductController::class, 'getProductbyStore']);
    Route::get('/storecategories/{storeid}', [Api\ProductController::class, 'getcategorybyStore']);
    Route::get('/getbycategory/{id}', [Api\ProductController::class, 'getByCategory']);
    Route::get('/categories', [Api\ProductController::class, 'categories']);
    Route::get('/getbyprice/{val}', [Api\ProductController::class, 'getProductByPrice']);
    Route::get('/getbypricerange/{min}/{max}', [Api\ProductController::class, 'getProductByPriceRange']);
    Route::get('/getproductbysize/{size}', [Api\ProductController::class, 'getProductBySize']);
    Route::get('/getauctionedproducts', [Api\ProductController::class, 'getAuctionedProducts']);
    Route::get('/trendingProduct/{guid}', [Api\ProductController::class, 'getTrendingProduct']);
    Route::get('/getsaveseller/{id}', [Api\SellerDataController::class, 'getSaveSeller']);
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
Route::group(['prefix' => '/bank'], function () {
    Route::get('/get', [Api\SellerDataController::class, 'getBank']);
});
Route::group(['prefix' => '/user'], function () {
    Route::post('/resendOtp', [Api\Auth\RegisterController::class, 'resendOtpEmailVerification']);
    Route::post('/resendForgetOtp', [Api\Auth\ForgotPasswordController::class, 'resendForgetOtp']);
});
Route::group(['prefix' => '/brands'], function () {
    Route::get('/', [Api\BrandsController::class, 'index']);
    Route::get('/category/{id}', [Api\BrandsController::class, 'withCategory']);
});
Route::get('/linkstorage', function () {
    Artisan::call('storage:link');
});
