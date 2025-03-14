<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BrandsController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
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
Route::get('/', function(){
    return redirect()->route('login');
})->name('admin.view-login');

Route::get('/forget-email', function () {
    return view('pages.auth.forgetEmail');
});
Route::post('/forget-email', [ForgotPasswordController::class, 'checkUI'])->name('forget.email');

Route::get('/forget-code', function () {
    return view('pages.auth.forgetCode');
})->name('forget-code');
Route::post('/forget-code', [ForgotPasswordController::class, 'verifyOtpUI'])->name('forget-code');

// Display the reset password form
Route::get('/forget-password', function () {
    return view('pages.auth.forgetPassword');
})->name('forget-password');


Route::get('/vendor_order_detail', function () {
    return view('pages.vendor.orderDetail');
});



// Handle the password reset form submission
Route::post('/forget-password', [ResetPasswordController::class, 'resetUI'])->name('forget-password');



Auth::routes(['verify' => true]);
Route::get('/admin-dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/user-management', [DashboardController::class, 'userManagement'])->name('userManagement');
Route::post('/active-inactive-vendor/{id}', [DashboardController::class, 'activeInactiveVendor'])->name('activeInactiveVendor.active');
Route::get('/order-management', [DashboardController::class, 'orderManagement'])->name('orderManagement');
Route::get('/order-detail/{id}', [DashboardController::class, 'orderDetail'])->name('order.detail');
Route::get('/vendor-dashboard/{id}', [DashboardController::class, 'vendorDashboard'])->name('vendor.dashboard');
Route::get('/report-admin', [DashboardController::class, 'reportAdmin'])->name('report');
Route::get('/help-support', [DashboardController::class, 'helpAndSupport'])->name('heilSupport');
Route::get('/seller-withdraw', [DashboardController::class, 'sellerWithdraw'])->name('sellerWithdraw');
Route::post('/seller-withdraw-status', [DashboardController::class, 'sellerWithdrawStatus'])->name('sellerWithdrawUpdate');

// change password
Route::get('/setting-page', [UserController::class, 'changePassword'])->name('change.password');
Route::post('/setting-page', [UserController::class, 'updatePassword'])->name('update.password');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


// Route::get('/order-management', function () {
//     return view('pages.orderManagement');
// });

Route::get('/revenue-management', function () {
    return view('pages.revenueManagement');
});
Route::get('/bids-offer',[DashboardController::class,'bids']);



//category
Route::get('/category-form', [CategoryController::class, 'create']);
Route::get('/category-table', [CategoryController::class, 'index'])->name('categoryTable');
Route::post('/category-form', [CategoryController::class, 'store']);
Route::get('/categoryEdit/{id}', [CategoryController::class, 'edit']);
//end category

//attribute
Route::get('/attribute-form', [AttributeController::class, 'create']);
Route::get('/arrtibute-table', [AttributeController::class, 'index'])->name('attributeTable');
Route::post('/attribute-form', [AttributeController::class, 'store']);
Route::get('/attributeEdit/{id}', [AttributeController::class, 'edit']);
//end attribute

//link category attribute
Route::get('/attribute-form/{category}', [CategoryController::class, 'showAttributes']);
Route::post('/attributes/create', [CategoryController::class, 'addAttribute'])->name('attribute.createAttributes');


//end link category attribute
//  product
Route::get('product-table', [ProductController::class, 'index'])->name('productTable');
Route::get('product-activeInactive/{id}', [ProductController::class, 'activeInactive'])->name('product.activeInactive');
Route::get('product-isFeaturetActiveInactive/{id}', [ProductController::class, 'isFeaturetActiveInactive'])->name('product.isFeaturetActiveInactive');
// Route::get('/admin-login', function () {
//     return view('pages.login');
// });



//end product


//brand
Route::get('/brand-form', [BrandsController::class, 'create']);
Route::get('/brand-table', [BrandsController::class, 'index'])->name('brandTable');
Route::post('/brand-form', [BrandsController::class, 'store']);
Route::get('/brandEdit/{id}', [BrandsController::class, 'edit']);
Route::post('/brands/{id}', [BrandsController::class, 'update'])->name('brands.update');

Route::get('/banner-form', [BannerController::class, 'create']);
Route::get('/banner-table', [BannerController::class, 'index'])->name('bannerTable');
Route::post('/banner-form', [BannerController::class, 'store']);
Route::get('/bannerEdit/{id}', [BannerController::class, 'edit']);
Route::put('/banner/{id}', [BannerController::class, 'update'])->name('banner.update');
Route::post('/banner-store', [BannerController::class, 'store'])->name('banner.store');
Route::delete('/banner-destroy/{id}', [BannerController::class, 'destroy'])->name('banner.destroy');

Route::get('/featured-banner-table', [BannerController::class, 'indexFeatured'])->name('bannerFeaturedTable');
Route::get('/featured-banner-form', [BannerController::class, 'createFeatured']);
Route::post('/featured-banner-store', [BannerController::class, 'storeFeatured'])->name('featured.banner.store');
Route::delete('/featured-banner-destroy/{id}', [BannerController::class, 'destroyFeatured'])->name('featured.banner.destroy');
Route::get('/featured-banner-edit/{id}', [BannerController::class, 'editFeatured']);
Route::post('/featured-banner/{id}', [BannerController::class, 'updateFeatured'])->name('featured.banner.update');
//end brand

//login
Route::get('/brand-form', [BrandsController::class, 'create']);
Route::get('/brand-table', [BrandsController::class, 'index'])->name('brandTable');
//end login



Route::get('/discount-promotion', function () {
    return view('pages.discountPromotion');
});
Route::get('/report-anaylytic',[DashboardController::class,'report']);
Route::get('/push-notification', function () {
    return view('pages.pushNotification');
});
Auth::routes();



// Route::get('/', function () {
//    // \Artisan::call("route:clear");
//     return redirect()->route('admin.view-login');
// });

// Route::get('/home', 'HomeController@index')->name('home');
Route::get('/user', 'UserController@get')->name('user.get');
Route::post('/user/{id}', 'UserController@edit')->name('user.edit');
Route::post('user/update/{id}', 'UserController@updateUser')->name('user.updateUser');
Route::post('notification/promotion', 'DashboardController@sendPromotionalNotification')->name('push.promotion');
Route::get('/user/show/{id}', 'UserController@show')->name('user.show');
// Route::get('/user', 'UserController@destroy')->name('user.destroy');
Route::delete('/user/{id}', 'UserController@destroy')->name('user.destroy');
Route::get('/flexe-fee', 'FlexeFeeController@index')->name('flexefee.index');
Route::get('/flexe-fee/show/{id}', 'FlexeFeeController@show')->name('flexefee.show');
Route::post('/flexe-fee/update/{id}', 'FlexeFeeController@update')->name('flexefee.update');
Route::get('/trusted-seller', 'TrustedSellerController@index')->name('trusted-seller.index');
Route::post('/trusted-seller/{id}', 'TrustedSellerController@edit')->name('trusted-seller.edit');
Route::get('/trusted-seller/show/{id}', 'TrustedSellerController@show')->name('trusted-seller.show');
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
// Route::get('/users', 'UserController@index')->name('users.index');

    Route::Resources([
        'category' => CategoryController::class,
        'products' => ProductController::class,
        'services' => ServiceController::class,
        'attribute' => AttributeController::class,
        'unit-type' => UnitTypeController::class,
        'media' => MediaController::class,
        'banks' => BankController::class,
        'price' => PriceController::class,
        'brands' => BrandsController::class,
        'deliverycompany' => DeliveryCompanyController::class,
        'sellerdata' => SellerDataController::class,
        "category_attributes"=>CategoryAttributesController::class
    ]);


    Route::get('category', 'CategoryController@search')->name('category.search');
    Route::get('category/attributes/{id}', 'CategoryController@showCategoryAttributes')->name('category.editattribute');
    Route::post('addOrUpdateCategoryAttributes','CategoryController@addOrUpdateCategoryAttributes')->name('category.updateattribute');
    Route::get('prices', 'PriceController@search')->name('prices.search');
    Route::get('sellerdata', 'SellerDataController@search')->name('sellerdata.search');
    Route::get('sellerdata/report/{guid}', 'SellerDataController@sellerReport')->name('sellerdata.report');
    Route::get('deliverycompanys', 'DeliveryCompanyController@search')->name('deliverycompany.search');
    Route::get('brandss', 'BrandsController@search')->name('brands.search');
    Route::get('banks', 'BankController@search')->name('banks.search');
    Route::get('bank/{id}', 'BankController@edit')->name('bank.edit');
    Route::get('attributes', 'AttributeController@search')->name('attributes.search');
    Route::get('category/{category}/attributes/{product?}', 'CategoryController@attributes')->name('category.attributes');

    Route::get('in-active-category', 'CategoryController@inActive')->name('category.in-active');
    Route::get('in-active-prices', 'PriceController@inActive')->name('prices.in-active');
    Route::get('in-active-category-search', 'CategoryController@searchInActive')->name('category.inactive.search');
    Route::get('in-active-prices', 'PriceController@searchInActive')->name('prices.inactive.search');
    Route::post('in-activate-category/all', 'CategoryController@activateAll')->name('categories.active-all');
    Route::post('in-activate-prices/all', 'PriceController@activateAll')->name('prices.active-all');
    Route::get('products', 'ProductController@search')->name('products.search');
    Route::post('products-hot/{id}', 'ProductController@updateHot')->name('products.hot.update');
    Route::get('in-active-products', 'ProductController@inActive')->name('products.in-active');
    Route::get('search-in-active-products', 'ProductController@searchInActive')->name('products.inactive.search');
    Route::post('in-activate-products/all', 'ProductController@activateAll')->name('products.active-all');
    Route::get('services', 'ServiceController@search')->name('services.search');
    Route::get('in-active-services', 'ServiceController@inActive')->name('services.in-active');
    Route::get('search-in-active-services', 'ServiceController@searchInActive')->name('services.in-active.search');
    Route::post('in-activate-services/all', 'ServiceController@activateAll')->name('services.active-all');
    Route::get('products/customer/{user}', 'UserController@showUserProducts')->name('customer.products');
    Route::get('services/customer/{user}', 'UserController@showUserServices')->name('customer.services');
    Route::post('in-activate-products/customer/{user}', 'UserController@activateAllProducts')->name('customer.products.active-all');
    Route::post('in-activate-services/customer/{user}', 'UserController@activateAllServices')->name('customer.services.active-all');
    Route::post('user/update/{id}', 'UserController@changeUser')->name('user.changeStatus');

    Route::group(['prefix' => 'category/properties'], function () {
        Route::get('show-list/{category:guid}', 'CategoryController@showAttributesList')->name('category.show-list');
        Route::get('show/{category:guid}', 'CategoryController@showAttributes')->name('category.show-attributes');
        Route::post('add/{category:guid}', 'CategoryController@addAttributes')->name('category.add-attributes');
        Route::post('attributes/{id}', 'CategoryController@deleteCategoryAttribute')->name('category.delete-attributes');
        Route::get('search', 'CategoryController@searchCatAttributes')->name('category.show-single-attributes');
    });
    Route::group(['prefix' => 'properties'], function () {
        Route::delete('/destroy/{id}', 'CategoryController@deleteCategoryAttribute')->name('properties.destroy');
    });
});


