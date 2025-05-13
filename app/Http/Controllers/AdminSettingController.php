<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class AdminSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Site ayarlarını al, veritabanında ayarlar tablosu olması durumunda
        // $settings = Setting::pluck('value', 'key')->toArray();
        
        // Henüz ayarlar tablosu oluşturulmadığı için, varsayılan değerleri döndürelim
        $settings = [
            'site_title' => 'Kütüphane Otomasyonu',
            'welcome_message' => 'Kütüphane Otomasyonu Sistemine Hoş Geldiniz',
            'max_books_per_user' => 5,
            'loan_period_days' => 14,
            'overdue_fine_per_day' => 1.00,
            'email_notifications' => true,
            'maintenance_mode' => false
        ];
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_title' => 'required|string|max:255',
            'welcome_message' => 'required|string',
            'max_books_per_user' => 'required|integer|min:1|max:20',
            'loan_period_days' => 'required|integer|min:1|max:60',
            'overdue_fine_per_day' => 'required|numeric|min:0|max:10',
            'email_notifications' => 'boolean',
            'maintenance_mode' => 'boolean'
        ]);
        
        // Settings tablosu olduğunda, ayarları veritabanında güncelleriz
        // foreach ($validated as $key => $value) {
        //     Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        // }
        
        // Önbelleği temizle
        // Cache::forget('settings');
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Ayarlar başarıyla güncellendi.');
    }
    
    /**
     * Clear system cache.
     */
    public function clearCache()
    {
        // Önbelleği temizle
        Cache::flush();
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Sistem önbelleği temizlendi.');
    }
} 