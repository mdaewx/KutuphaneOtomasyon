<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hem role alanı hem de is_admin kontrolü yapılıyor (geriye dönük uyumluluk için)
        if (!Auth::check() || (!Auth::user()->is_admin && Auth::user()->role !== 'admin')) {
            return redirect('/')->with('error', 'Yönetici paneline erişim izniniz yok.');
        }

        return $next($request);
    }
} 