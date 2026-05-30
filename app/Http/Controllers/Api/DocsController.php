<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class DocsController extends Controller
{
    /**
     * Render the dynamically compiled visual Swagger UI.
     */
    public function index()
    {
        return view('swagger');
    }
}
