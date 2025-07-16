<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
     return redirect('/docs');
});
Route::get('/docs', function () {
    $swaggerView = resource_path('views/vendor/l5-swagger/index.blade.php');

    if (!File::exists($swaggerView)) {
        abort(404, 'Swagger view not found.');
    }

    return view('vendor.l5-swagger.index');
});
Route::get('/storage/api-docs/api-docs.json', function () {
    return response()->file(storage_path('api-docs/api-docs.json'));
});