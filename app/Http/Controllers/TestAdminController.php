<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestAdminController extends Controller
{
    /**
     * Display a test admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        // Simple test dashboard to verify routing is working correctly
        return view('admin.test_dashboard');
    }
}
