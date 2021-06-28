<?php
Route::model('repository', App\Models\Repository::class);
Route::model('file', App\Models\RepositoryFile::class);

Route::get('/', 'DocumentController');
Route::get('/repository/{repository}/document/{file?}', 'DocumentController@repository')->name('document');
Route::get('/repository/{repository}/search', 'DocumentController@search')->name('search');
