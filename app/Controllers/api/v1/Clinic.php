<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;

class Clinic extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }
}
