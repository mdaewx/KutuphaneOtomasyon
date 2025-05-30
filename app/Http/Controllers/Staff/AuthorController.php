<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        $authors = Author::where(function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('surname', 'LIKE', "%{$search}%");
        })
        ->orderBy('name')
        ->limit(10)
        ->get();
        
        return response()->json($authors);
    }
}
