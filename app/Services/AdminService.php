<?php

namespace App\Services;

class AdminService
{
    /**
     * A simple class to resolve the [admin] target class issue
     */
    public function dashboard()
    {
        // This method provides a fallback if the AdminController is not found
        return view('admin.test_dashboard');
    }
    
    /**
     * Make the class callable
     */
    public function __invoke(...$parameters)
    {
        // Make sure the service can be called directly
        return $this->dashboard();
    }
    
    /**
     * Proxy method to delegate to appropriate controller
     */
    public function __call($method, $parameters)
    {
        // If the method doesn't exist, log it for debugging
        \Log::info("AdminService called with method: {$method}");
        
        // Try to find an appropriate controller to handle this
        if (method_exists(\App\Http\Controllers\AdminController::class, $method)) {
            return app(\App\Http\Controllers\AdminController::class)->$method(...$parameters);
        }
        
        // Fallback to the test controller
        if (method_exists(\App\Http\Controllers\TestAdminController::class, $method)) {
            return app(\App\Http\Controllers\TestAdminController::class)->$method(...$parameters);
        }
        
        return response()->view('errors.404', [], 404);
    }
} 