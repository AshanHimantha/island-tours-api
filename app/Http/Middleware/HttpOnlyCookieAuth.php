<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HttpOnlyCookieAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if auth_token cookie exists
        if (!$request->cookie('auth_token')) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Get token from cookie and authenticate
        $token = $request->cookie('auth_token');
        
        // Find the token in the database
        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        
        if (!$personalAccessToken || 
            (!$personalAccessToken->can('*') && !$personalAccessToken->can('api'))) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }
        
        // Get the user associated with the token
        $user = $personalAccessToken->tokenable;
        
        // Login the user
        Auth::login($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}
