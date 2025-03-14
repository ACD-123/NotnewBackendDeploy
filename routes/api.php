<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Api\OrderController;
use App\Models\Fedex;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\PostCategoryController;
use App\Http\Controllers\Api\PostAttributeController;
use App\Http\Controllers\Api\PostProductController;
use App\Http\Controllers\Api\PostChatController;
use Illuminate\Http\Request;


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
Route::get('shahnawaz',function(){
    return config('app.name');
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => 'post-categories'], function () {
    Route::get('/',[PostCategoryController::class,'index']);
    Route::get('/get-by-id/{guid}',[PostCategoryController::class,'getByGuid']);
    Route::post('/create',[PostCategoryController::class,'create']);
    Route::post('/update/{guid}',[PostCategoryController::class,'update']);
    Route::post('/update-status/{guid}',[PostCategoryController::class,'updateStatus']);
    Route::get('/category-wise/{guid}',[PostProductController::class,'getCategoryWiseProducts']);
    Route::post('/assign-attributes',[PostCategoryController::class,'assignAttributes']);
    Route::get('/category-wise-attributes/{guid}',[PostCategoryController::class,'getCategoryAttributes']);
});
Route::group(['prefix' => 'post-attributes'], function () {
    Route::get('/',[PostAttributeController::class,'index']);
    Route::get('/get-by-id/{guid}',[PostAttributeController::class,'getByGuid']);
    Route::post('/create',[PostAttributeController::class,'create']);
    Route::post('/update/{guid}',[PostAttributeController::class,'update']);
});

Route::group(['prefix' => 'post-products'], function () {
    Route::get('/',[PostProductController::class,'index'])->middleware('auth:api');
    Route::post('/create',[PostProductController::class,'create'])->middleware('auth:api');
    Route::get('/get-by-id/{guid}',[PostProductController::class,'getByGuid']);
    Route::post('/update/{guid}',[PostProductController::class,'update'])->middleware('auth:api');
    Route::post('/promote/{guid}',[PostProductController::class,'promote'])->middleware('auth:api');
    Route::post('/update-status/{guid}',[PostProductController::class,'updateStatus'])->middleware('auth:api');
    Route::post('/delete/{guid}',[PostProductController::class,'delete'])->middleware('auth:api');
    Route::get('/home',[PostProductController::class,'home']);
    Route::get('/filter',[PostProductController::class,'filter']);
    Route::get('/checkout/{guid}',[PostChatController::class,'checkout']);
});
Route::group(['prefix'=>'post-favourites'],function(){
    Route::get('/',[PostProductController::class,'getFavourites'])->middleware('auth:api');
    Route::post('/save',[PostProductController::class,'storeFavourite'])->middleware('auth:api');
});
Route::group(['prefix'=>'post-chats'],function(){
    Route::get('/',[PostChatController::class,'getChatList'])->middleware('auth:api');
    Route::get('/get-chat-room/{id}',[PostChatController::class,'getChatRoomByID'])->middleware('auth:api');
    Route::get('/get-chat-room-messages/{id}',[PostChatController::class,'getChatRoomWithMessages'])->middleware('auth:api');
    Route::get('/get-chat-count',[PostChatController::class,'getChatUnreadCount'])->middleware('auth:api');
    Route::post('/delete-chat-room/{id}',[PostChatController::class,'deleteChatRoom'])->middleware('auth:api');
    Route::post('/create-chat-room',[PostChatController::class,'createChatRoom'])->middleware('auth:api');
    Route::post('/send-message',[PostChatController::class,'createMessage'])->middleware('auth:api');
});
Route::get('/order/createPaymentIntentTest', [Api\OrderController::class, 'createPaymentIntentTest']);
Route::get('/noti', [Api\OrderController::class, 'noti']);

Route::get('/testshipengine', [Api\OrderController::class, 'testingShipping']);
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
    Route::post('/google-login-app', [Api\Auth\LoginController::class, 'googleLoginApp']);
    Route::post('/apple-login', [Api\Auth\LoginController::class, 'appleLogin']);
    Route::post('logout', [Api\AuthController::class, 'logout']);
    Route::post('/social-login', [Api\Auth\LoginController::class, 'socialLogin']);
});

Route::group(['prefix' => '/guest-cart'], function () {
            Route::post('/add', [Api\UserCartController::class, 'storeGuest']);
            Route::get('/self', [Api\UserCartController::class, 'selfGuest']);    
            Route::post('/clear', [Api\UserCartController::class, 'guestClear']);
//            Route::post('/clear/{id}', [Api\UserCartController::class, 'clear']);    
            Route::post('/destroy', [Api\UserCartController::class, 'guestDestroy']);    
            Route::get('/count', [Api\UserCartController::class, 'guestCount']);
            Route::post('/update/{id}', [Api\UserCartController::class, 'guestUpdate']); 
              Route::post('/applyCoupon', [Api\CouponController::class,'checkCouponGuest']);
            Route::post('/deleteCoupon', [Api\CouponController::class,'deleteCouponCartGuest']);
        });
Route::get('/cart/cron',[Api\UserCartController::class,'deleteCartAll']);

//===============================All the below route should be in Secure routes==============================
Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => '/cart'], function () {
            Route::post('/add', [Api\UserCartController::class, 'store']);
            Route::get('/', [Api\UserCartController::class, 'index']);
            Route::get('/self', [Api\UserCartController::class, 'self']);    
            Route::post('/clear', [Api\UserCartController::class, 'clear']);
//            Route::post('/clear/{id}', [Api\UserCartController::class, 'clear']);    
            Route::post('/destroy', [Api\UserCartController::class, 'destroy']);    
            Route::post('/update/{id}', [Api\UserCartController::class, 'update']); 
            Route::get('/count', [Api\UserCartController::class, 'count']);
            Route::post('/applyCouponStores', [Api\CouponController::class,'checkCouponStores']);
            Route::post('/deleteCouponStores', [Api\CouponController::class,'deleteCouponStores']);
               Route::post('/applyCoupon', [Api\CouponController::class,'checkCoupon']);
            Route::post('/deleteCoupon', [Api\CouponController::class,'deleteCouponCart']);
                 Route::post('/reorder', [Api\UserCartController::class,'reorder']);
                 Route::post('/reorderweb',[Api\UserCartController::class,'reorderWeb']);
        });
        Route::post('user-underage', [Api\UserController::class, 'updateUnderage']);
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
            Route::post('/logout-fcm',[Api\UserController::class,'logoutFCMToken']);
            Route::get('/wallet-transactions', [Api\UserController::class, 'walletTransaction']);
            Route::get('/wallet-amount', [Api\UserController::class, 'walletAmount']);
            Route::get('detail/', [Api\UserController::class, 'detail']);
            Route::get('getNotificationSetting', [Api\UserController::class, 'getNotificationSetting']);
            Route::post('updateNotificationSetting', [Api\UserController::class, 'updateNotificationSetting']);
            Route::post('updateFcmWeb', [Api\UserController::class, 'updateFCMWebToken']);
            Route::post('updateFcm', [Api\UserController::class, 'updateFCMToken']);
            Route::post('updatePassword', [Api\UserController::class, 'updatePassword']);
            Route::get('getUserAddress', [Api\UserController::class, 'getUserAddress']);
            Route::post('storeUserAddress', [Api\UserController::class, 'storeUserAddress']);
            Route::post('updateUserAddress',[Api\UserController::class, 'updateUserAddress']);
            Route::post('deleteUserAddress',[Api\UserController::class, 'deleteUserAddress']);
            Route::get('detail/{id}', [Api\UserController::class, 'detailById']);
            Route::get('self', [Api\UserController::class, 'self']);
            Route::get('self-new', [Api\UserController::class, 'selfNew']);
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
            Route::post('secretquestion/', [Api\UserController::class, 'setSecretQuestion']);
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
            Route::post('/saveSearch', [Api\ProductController::class, 'savedSearch']);
            Route::get('/getsavesearch', [Api\ProductController::class, 'getSavedSearch']);
        });
        Route::group(['prefix'=>'chat'],function(){
            Route::get('/getById', [Api\ChatMessagesController::class, 'getById']);
            Route::post('/deleteById', [Api\ChatMessagesController::class, 'deleteById']);
            Route::post('/sendMessage', [Api\ChatMessagesController::class, 'save']);
            Route::post('/getChatRooms', [Api\ChatRoomsController::class, 'getChatRooms']);
            Route::post('/createChatRooms', [Api\ChatRoomsController::class, 'createChatRooms']);
            Route::get('/getChatListBUid', [Api\ChatRoomsController::class, 'getChatListBUid']);
            Route::get('/getChatUsers/{userID}/{status}', [Api\ChatRoomsController::class, 'getChatUsers']);
            Route::post('/is_seen', [Api\ChatMessagesController::class, 'updateIsSeen']);
            Route::post('/is_read', [Api\ChatMessagesController::class, 'updateIsRead']);
        });
        Route::group(['prefix' => '/products'], function () {
            Route::post('/add', [Api\ProductController::class, 'store']);
            
            Route::post('/{product:guid}', [Api\ProductController::class, 'update']);
            Route::patch('/ratings/{product:guid}', [Api\ProductController::class, 'ratings']);
            Route::get('/checkRatings/{productId}/{userId}/{orderId}', [Api\ProductController::class, 'checkRatings']);
            Route::get('/self/', [Api\ProductController::class, 'self']);
            Route::get('/self/{value}', [Api\ProductController::class, 'selfItems']);
            Route::get('/shows/{product:guid}', [Api\ProductController::class, 'show']);
            Route::get('/active', [Api\ProductController::class, 'active']);
            Route::get('/inactive', [Api\ProductController::class, 'inactive']);
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
            Route::post('/teststock', [Api\OrderController::class, 'store_stock']);
        });
        Route::post('/imageproductuploads', [Api\ProductController::class, 'imageUploadProduct']);
        Route::group(['prefix' => '/watchlist'], function () {
            Route::post('/add', [Api\WatchListController::class, 'store']);    
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
            Route::get('/user-notification',[Api\NotificationController::class, 'getUserNotification']);
            Route::get('/user-count',[Api\NotificationController::class, 'getUserNotificationCount']);
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
            Route::get('/self_', [Api\CheckoutController::class, 'self_']);
            Route::get('/buynow', [Api\CheckoutController::class, 'selfCheckOut']);
            Route::get('/buynow_', [Api\CheckoutController::class, 'selfCheckOut_']);
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
        
        Route::post('/order/createPaymentIntent', [Api\OrderController::class, 'createPaymentIntent']);
        Route::get('/order/getById/{id}', [Api\OrderController::class, 'getById']);
        Route::get('/order/getById_/{id}', [Api\OrderController::class, 'getById_']);
        Route::get('/order/{id}', [Api\OrderController::class, 'update']);
        Route::patch('/order/updateSeller/{id}', [Api\OrderController::class, 'updateSeller']);
        
        Route::get('/order/customer/comp/count', [Api\OrderController::class, 'customerOrderCompCount']);
        Route::get('/order/customer/pend/count', [Api\OrderController::class, 'customerOrderPendCount']);
        Route::get('/order/customer/refund/count', [Api\OrderController::class, 'customerOrderRefundCount']);
        Route::get('/orders/seller/dashboard',[Api\OrderController::class,'getSellerDashboardStats']);
        Route::get('/orders/counts', [Api\OrderController::class, 'getUserCount']);
        Route::get('/orders/add', [Api\OrderController::class, 'store']);
        Route::get('/orders/counts_', [Api\OrderController::class, 'getUserCount_']);
        Route::get('/order/tracking/{id}', [Api\OrderController::class, 'tracking']);
        Route::patch('/order/packed/{id}', [Api\OrderController::class, 'packed']);
        Route::post('/order/ratecalculator', [Api\OrderController::class, 'ratecalculator']);
        Route::post('/order/store_', [Api\OrderController::class, 'store_']);
        Route::post('/order/validatePostalCode', [Api\OrderController::class, 'verifyAddressEasyPost']);
        Route::post('/order/validateAddress', [Api\OrderController::class, 'validateAddress']);
        Route::get('/order/getTrsutedUserData/{id}', [Api\OrderController::class, 'getTrsutedUserData']);
        Route::post('/order/delivered/{id}', [Api\OrderController::class, 'delivered']);
        Route::post('/order/notdelivered/{id}', [Api\OrderController::class, 'notdelivered']);
        Route::get('/orders/rejected', [Api\OrderController::class, 'getRejectedOrders']);
        Route::get('/orders/rejected_customer', [Api\OrderController::class, 'getRejectedOrdersCustomer']);
        Route::get('/orders/accepted', [Api\OrderController::class, 'getAcceptedOrders']);
        Route::get('/orders/accepted_customer', [Api\OrderController::class, 'getAcceptedOrdersCustomer']);
        Route::post('/orders/updateOrderStatus',[Api\OrderController::class,'updateOrderStatus']);
        Route::get('/orders/active', [Api\OrderController::class, 'active']);
        Route::get('/orders/activecustomer', [Api\OrderController::class, 'active_Customer']);
        Route::get('/orders/completed', [Api\OrderController::class, 'completed']);
        Route::get('/orders/refund', [Api\OrderController::class, 'refund']);
        Route::get('/orders/completedcustomer', [Api\OrderController::class, 'completed_Customer']);
        Route::get('/orders/completedcustomer_', [Api\OrderController::class, 'completed_Customer_']);
        Route::get('/orders/refundcustomer', [Api\OrderController::class, 'refund_Customer']);
        
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
        Route::post('refund-status-update',[Api\RefundController::class,'statusRefund']);
        Route::post('refund/fileupload', [Api\RefundController::class, 'fileUpload']);
        
        Route::patch('refund/{id}/{status}', [Api\RefundController::class, 'update']);
        Route::group(['prefix' => '/seller'], function () {
            Route::get('/', [Api\SellerDataController::class, 'index']);
            Route::post('/report',[Api\SellerDataController::class, 'report']);
            Route::post('/witdraw/{guid}', [Api\SellerDataController::class, 'sellerWithdraw']);
            Route::get('/sellertransaction/{guid}',[Api\SellerDataController::class,'sellertransaction']);
            Route::post('/add', [Api\SellerDataController::class, 'store']);
            Route::post('/setbank', [Api\SellerDataController::class, 'setBankData']);
            Route::post('/update', [Api\SellerDataController::class, 'updateSellerData']);
            Route::post('/updateVideo', [Api\CouponController::class, 'updateVideo']);

            Route::get('/getshopdetails', [Api\SellerDataController::class, 'getShopDetails']);
            Route::post('/saveSeller', [Api\SellerDataController::class, 'saveSeller']);
            Route::post('/updateBank', [Api\SellerDataController::class, 'updateBank']);
            Route::get('/getBankDetails', [Api\SellerDataController::class, 'getBankDetails']);
            Route::get('/getusersaveseller', [Api\SellerDataController::class, 'getUserSaveSeller']);
            Route::post('/createUserRecents', [Api\ProductController::class, 'createUserRecientView']);
            Route::get('/feedback/{id}', [Api\SellerDataController::class, 'feedback']);
            Route::get('/getsellerorder', [Api\SellerDataController::class, 'getSellOrder']);
        });
        Route::group(['prefix' => '/transaction'], function () {
            Route::get('/usertransaction', [Api\TransactionController::class, 'getUserTransactions']);
            Route::get('/gettransactions', [Api\TransactionController::class, 'getTransactions']);
            Route::get('/getstripetransactions', [Api\TransactionController::class, 'getStripeTransactions']);
        });
         Route::group(['prefix' => '/refund'], function () {
            Route::post('/add', [Api\RefundController::class, 'store']);
            Route::get('/{id}', [Api\RefundController::class, 'show']);
        });
         Route::group(['prefix' => '/feedback'], function () {
            Route::post('/add', [Api\FeedBackController::class, 'store']);
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
 Route::get('category-underage', [Api\CategoryController::class, 'indexUnderage']);
 Route::get('category', [Api\CategoryController::class, 'indexHome']);
Route::group(['prefix' => '/categories', ['middleware' => 'throttle:20,5']], function () {
    Route::get('/tabs', [Api\CategoryController::class, 'tabs']);
    Route::get('tabs/list', [Api\CategoryController::class, 'tabs']);
    Route::get('/product-attributes/{category}', [Api\CategoryController::class, 'productAttributes']);
     Route::get('/sub-categories/{categoryId}', [Api\CategoryController::class, 'getSubCategories']);
    
    Route::get('/', [Api\CategoryController::class, 'index']);
   
    Route::get('/recursive', [Api\CategoryController::class, 'recursive']);
    Route::get('/overAll', [Api\CategoryController::class, 'all']);
});
Route::get('/recursiveCategories', [Api\CategoryController::class, 'recursive']);
Route::group(['prefix' => '/seller'], function () {
    Route::get('/getfeatured', [Api\SellerDataController::class, 'getFeatured']);
    Route::get('/getshopproduct/{id}', [Api\SellerDataController::class, 'getShopDetailProduct']);
    Route::get('/feedback/{id}', [Api\SellerDataController::class, 'feedback']);
    Route::get('/getshops/{id}', [Api\SellerDataController::class, 'getShopDetail']);
    Route::get('/getShopDetailHeader/{id}',[Api\SellerDataController::class,'getShopInitialDetail']);
    Route::get('/getShopDetailProducts/{id}',[Api\SellerDataController::class,'getProductsByShop']);
    Route::get('/getShopDetailFeedback/{id}',[Api\SellerDataController::class,'getFeedBackByShop']);
    Route::get('/getShopDetailAbout/{id}',[Api\SellerDataController::class,'getAboutDataByShop']);
    Route::post('/createRecents', [Api\ProductController::class, 'createRecentView']);
});
Route::group(['prefix' => '/products'], function () {
    Route::get('/', [Api\ProductController::class, 'index']);
     Route::get('/getUnderAgeProducts', [Api\ProductController::class, 'getUnderAgeProducts']);
    Route::get('/maxPriceProduct', [Api\ProductController::class, 'maxPriceProduct']);
    Route::get('/filterProducts',[Api\ProductController::class, 'filterProducts']);
    Route::get('/filterProductsUnderAge',[Api\ProductController::class, 'filterProductsUnderAge']);
    Route::get('/getRelatedProducts/{productID}',[Api\ProductController::class,'getRelatedProducts']);
    Route::get('getUnderAgeBanners',[Api\ProductController::class, 'getUnderageBanners']);
     Route::get('getTopSelling',[Api\ProductController::class, 'getTopSelling']);
     Route::get('getTopSellingUnderAge',[Api\ProductController::class, 'getTopSellingUnderAge']);
     Route::get('getHotUnderAge',[Api\ProductController::class, 'getHotUnderAge']);
     Route::get('deleteProduct/{id}',[Api\ProductController::class,'deleteProduct']);
      Route::get('getTopSellers',[Api\ProductController::class, 'getTopSellers']);
     Route::get('/auctioned', [Api\ProductController::class, 'getAuctionedProductsForUser']);
      Route::get('/category-wise-product/{categoryId}',[Api\ProductController::class,'getCategoryWiseProduct']);
    Route::get('/show/{product:guid}', [Api\ProductController::class, 'show']);
    Route::get('media/{product:guid}', [Api\ProductController::class, 'media']);
    Route::post('/search', [Api\ProductController::class, 'searched']);
    Route::post('/checkEmailReview/{id}', [Api\ProductController::class, 'checkEmailReview']);
    Route::get('/userRating/{product:user_id}', [Api\ProductController::class, 'userRating']);
    Route::get('/getAttributes/{categoryID}', [Api\ProductController::class, 'getAttributes']);
     Route::get('/getCategoryAttributes/{id}', [Api\ProductController::class, 'CategoryAttributes']);
    Route::get('/getProductAttributes/{id}', [Api\ProductController::class, 'getProductAttributes']);
    Route::get('/recent', [Api\ProductController::class, 'recentView']);
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
    Route::get('/getrecomemdedproducts/{shops}', [Api\ProductController::class, 'getRecomemdedProducts']);
    Route::get('/trendingProduct/{guid}', [Api\ProductController::class, 'getTrendingProduct']);
    Route::get('/getsaveseller/{id}', [Api\SellerDataController::class, 'getSaveSeller']);
    Route::get('results/{search}', [Api\ProductController::class, 'results']);
    Route::get('related/{guid}', [Api\ProductController::class, 'relatedProduct']);
    Route::get('/min', [Api\ProductController::class, 'getMin']);
    Route::get('/max', [Api\ProductController::class, 'getMax']);
    Route::get('/size', [Api\ProductController::class, 'getSizes']);
    Route::get('/{product:guid}', [Api\ProductController::class, 'getProductById']);
    
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
Route::group(['prefix' => '/favourites'], function () {
    Route::get('/get', [Api\FavouriteController::class, 'index']);
    Route::post('/save', [Api\FavouriteController::class, 'store']);
});
Route::group(['prefix' => '/follower'], function () {
    Route::get('/get', [Api\FavouriteController::class, 'getFollowers']);
    Route::post('/save', [Api\FavouriteController::class, 'storeFollower']);
});
Route::group(['prefix' => '/bidding'], function () {
    Route::get('/get', [Api\BiddingController::class, 'index']);
    Route::get('/getactiveinactive', [Api\BiddingController::class, 'getActiveInactiveAuctionedProducts']);
    Route::post('/save', [Api\BiddingController::class, 'store']);
    Route::get('/userbids', [Api\BiddingController::class, 'user']);
    Route::post('/award',[Api\BiddingController::class,'award']);
});
Route::group(['prefix' => '/additional_pages'], function () {
    Route::get('/get', [Api\FaqController::class, 'index']);
    Route::post('/store', [Api\FaqController::class, 'store']);
});
Route::group(['prefix' => '/coupons'], function () {
    Route::get('/get', [Api\CouponController::class, 'index']);
    Route::get('/get/{id}', [Api\CouponController::class, 'getById']);
    Route::post('/save', [Api\CouponController::class, 'store']);
    Route::post('/update',[Api\CouponController::class,'update']);
    Route::post('/delete',[Api\CouponController::class,'deleteCoupon']);
    Route::post('/update-status',[Api\CouponController::class,'updateStatus']);
    
});
Route::group(['prefix' => '/wishlist'], function () {
    Route::get('/get', [Api\WishlistController::class, 'index']);
    Route::post('/save', [Api\WishlistController::class, 'store']);
});
Route::group(['prefix'=>'searchHistory'],function(){
    Route::get('/get', [Api\SearchHistoryController::class, 'index']);
     Route::get('/getUnderAge', [Api\SearchHistoryController::class, 'indexUnderAge']);
    Route::post('/save',[Api\SearchHistoryController::class,'storeSearchKeyword']);
    Route::post('/saveProducts',[Api\SearchHistoryController::class,'storeSearchKeywordProducts']);
    Route::get('/getProducts',[Api\SearchHistoryController::class,'getStoreProductsFromSearch']);
    Route::post('/clear',[Api\SearchHistoryController::class,'clearSeacrch']);
    Route::post('/delete',[Api\SearchHistoryController::class,'deleteSearch']);
    Route::post('/deleteSearchProduct',[Api\SearchHistoryController::class,'deleteSearchProduct']);
    Route::get('/getKeywords', [Api\SearchHistoryController::class, 'getSearchKeyWordsList']);
});
Route::group(['prefix' => '/user'], function () {
    Route::post('/resendOtp', [Api\Auth\RegisterController::class, 'resendOtpEmailVerification']);
    Route::post('/resendForgetOtp', [Api\Auth\ForgotPasswordController::class, 'resendForgetOtp']);
});
Route::group(['prefix' => '/brands'], function () {
    Route::get('/', [Api\BrandsController::class, 'index']);
    Route::get('/category/{id}', [Api\BrandsController::class, 'withCategory']);
});
Route::get('/getcompanies', [Api\ProductController::class, 'getCompanies']);
Route::get('/getbrandscompanies', [Api\ProductController::class, 'getBrandsCompanies']);
Route::get('/products/shows/{product:guid}', [Api\ProductController::class, 'show']);
Route::get('/linkstorage', function () {
    Artisan::call('storage:link');
});

Route::get('/help-support', [DashboardController::class, 'helpAndSupport'])->name('heilSupport');
Route::post('/category-form', [CategoryController::class, 'store']);
