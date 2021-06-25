<?php
Route::model('repository', App\Models\Repository::class);

Route::get('api/{repository}', 'DocumentController');
