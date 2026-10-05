<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
class TokensController extends Controller
{
    public function create(Request $request) {
        $token = $request->user()->createToken('api-token', ['use-api']);

        return ['token' => $token->plainTextToken];
    }
}
