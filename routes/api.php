<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'v1',
    'namespace' => 'App\Http\Controllers\Api\V1',
], function () {

    Route::group([
        'prefix' => 'auth'
    ], function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');

        Route::group([
            'middleware' => 'auth:sanctum'
        ], function () {
            Route::get('logout', 'AuthController@logout');
        });
    });

    Route::group([
        'prefix' => 'storage',
    ], function () {

        Route::get('/size', 'StorageController@size')->name('storage.size');

        Route::group([
            'middleware' => 'auth:sanctum'
        ], function () {
            Route::post('/store', 'StorageController@store')->name('storage.store');
            Route::post('/update/{folder}', 'StorageController@update')->name('storage.update');
            Route::delete('/{folder}', 'StorageController@delete')->name('storage.destroy');
        });
    });

    Route::group([
        'prefix' => 'upload',
    ], function () {

        Route::get('/download/{uploaded_file}', 'UploadController@download')->name('upload.download');
        Route::get('/show/{uploaded_file}', 'UploadController@show')->name('upload.show');

        Route::group([
            'middleware' => 'auth:sanctum'
        ], function () {
            Route::post('/store/{folder}', 'UploadController@store')->name('upload.store');
            Route::post('/update/{uploaded_file}', 'UploadController@update')->name('upload.update');
            Route::delete('/{uploaded_file}', 'UploadController@delete')->name('upload.destroy');
            Route::post('/visibly/{uploaded_file}', 'UploadController@visibly')->name('upload.visibly');
        });
    });
});
