<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LibrarianMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || (!auth()->user()->hasRole('librarian') && !auth()->user()->is_staff)) {
            return redirect()->route('login')->with('error', 'Kütüphane memuru yetkisine sahip değilsiniz.');
        }

        return $next($request);
    }
} 