<?php

namespace App\Gitlab;

use Illuminate\Support\Facades\Facade;

Class Gitlab extends Facade {
    protected static function getFacadeAccessor()
    {
        return 'gitlab';
    }
}
