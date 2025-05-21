<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function index()
    {
        $fines = auth()->user()->borrowings()
            ->with(['book', 'fines'])
            ->whereHas('fines')
            ->get()
            ->pluck('fines')
            ->flatten();

        return view('user.fines.index', compact('fines'));
    }
} 