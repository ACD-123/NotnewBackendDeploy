<?php

use Illuminate\Support\Facades\Route;
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

Route::get('/', function () {
    return view('welcome');
});
Auth::routes(['verify' => true]);
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/user', 'UserController@get')->name('user.get');
Route::post('/user/{id}', 'UserController@edit')->name('user.edit');
Route::post('user/update/{id}', 'UserController@updateUser')->name('user.updateUser');
Route::get('/user/show/{id}', 'UserController@show')->name('user.show');
// Route::get('/user', 'UserController@destroy')->name('user.destroy');
Route::post('/user/{id}', 'UserController@destroy')->name('user.destroy');
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
    ]);


    Route::get('category', 'CategoryController@search')->name('category.search');
    
    Route::get('prices', 'PriceController@search')->name('prices.search');
     Route::get('prices', 'PriceController@search')->name('prices.search');
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
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
