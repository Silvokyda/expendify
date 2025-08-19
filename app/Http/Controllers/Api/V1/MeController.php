<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class MeController extends BaseApiController
{
    public function show(Request $request)
    {
        return $this->success(['user' => $request->user()], 'Current user');
    }
}
