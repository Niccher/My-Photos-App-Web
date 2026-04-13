<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class TestController extends BaseController
{
    public function index()
    {
        return $this->response->setJSON(['status' => 'success']);
    }
}
