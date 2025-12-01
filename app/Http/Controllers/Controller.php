<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function getDefaultParameter(Request $request)
    {
        return [
            'TOKEN' => $request->header('TTOKEN', $request->input('TTOKEN', '')),
            'ip_server' => $request->server('SERVER_ADDR'),
            'user_agent' => $request->input('user_agent'),
        ];
    }
}
