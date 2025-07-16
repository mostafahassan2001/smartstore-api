<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/docs', function () {
    $swaggerView = resource_path('views/vendor/l5-swagger/index.blade.php');

    if (!File::exists($swaggerView)) {
        abort(404, 'Swagger view not found.');
    }

    return view('vendor.l5-swagger.index');
});
