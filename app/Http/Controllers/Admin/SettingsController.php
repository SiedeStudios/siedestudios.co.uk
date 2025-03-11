<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Load settings from database
        $settings = [
            'site_title' => Setting::get('site_title', config('app.name')),
            'site_description' => Setting::get('site_description', 'SiedeStudios Portfolio Website'),
            'contact_email' => Setting::get('contact_email', 'info@siedestudios.com'),
            'contact_phone' => Setting::get('contact_phone', ''),
            'social_twitter' => Setting::get('social_twitter', ''),
            'social_facebook' => Setting::get('social_facebook', ''),
            'social_instagram' => Setting::get('social_instagram', ''),
            'social_github' => Setting::get('social_github', ''),
            'social_linkedin' => Setting::get('social_linkedin', ''),
            'meta_keywords' => Setting::get('meta_keywords', ''),
            'google_analytics' => Setting::get('google_analytics', ''),
            'logo' => Setting::get('logo', ''),
            'favicon' => Setting::get('favicon', ''),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update the settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'site_title' => 'required|string|max:255',
                'site_description' => 'nullable|string|max:500',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'social_twitter' => 'nullable|url|max:255',
                'social_facebook' => 'nullable|url|max:255',
                'social_instagram' => 'nullable|url|max:255',
                'social_github' => 'nullable|url|max:255',
                'social_linkedin' => 'nullable|url|max:255',
                'meta_keywords' => 'nullable|string|max:500',
                'google_analytics' => 'nullable|string|max:50',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
                'favicon' => 'nullable|file|mimes:ico,png|max:1024',
            ]);

            // Update settings in database
            foreach ($validated as $key => $value) {
                if ($key === 'logo' || $key === 'favicon') {
                    continue; // Handle files separately
                }
                
                Setting::set($key, $value);
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $oldLogo = Setting::get('logo');
                if ($oldLogo) {
                    Storage::delete('public/settings/' . $oldLogo);
                }

                $logoName = 'logo_' . time() . '.' . $request->file('logo')->getClientOriginalExtension();
                $request->file('logo')->storeAs('public/settings', $logoName);
                Setting::set('logo', $logoName);
            }

            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                $oldFavicon = Setting::get('favicon');
                if ($oldFavicon) {
                    Storage::delete('public/settings/' . $oldFavicon);
                }

                $faviconName = 'favicon_' . time() . '.' . $request->file('favicon')->getClientOriginalExtension();
                $request->file('favicon')->storeAs('public/settings', $faviconName);
                Setting::set('favicon', $faviconName);
            }

            // Update app name in the .env file
            if ($request->site_title !== config('app.name')) {
                $this->setEnvValue('APP_NAME', '"' . $request->site_title . '"');
            }

            return redirect()->route('admin.settings')
                ->with('success', 'Settings updated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Settings update error: ' . $e->getMessage());
            return redirect()->route('admin.settings')
                ->with('error', 'An error occurred while updating settings. Please try again.');
        }
    }

    /**
     * Set a .env file value.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    private function setEnvValue($key, $value)
    {
        try {
            $path = app()->environmentFilePath();
            $env = file_get_contents($path);

            $old = env($key);
            
            // If value contains spaces or hash characters, surround with quotes
            if (strpos($value, ' ') !== false || strpos($value, '#') !== false) {
                if (!str_starts_with($value, '"') && !str_ends_with($value, '"')) {
                    $value = '"' . $value . '"';
                }
            }

            // Format the old key-value pair for replacement
            $oldPair = $key . '=' . (is_string($old) && 
                        !str_starts_with($old, '"') && 
                        !str_ends_with($old, '"') && 
                        (strpos($old, ' ') !== false || strpos($old, '#') !== false) ? 
                        '"' . $old . '"' : $old);
            
            // Create the new key-value pair
            $newPair = $key . '=' . $value;

            // Replace the old pair with the new one
            $env = str_replace($oldPair, $newPair, $env);
            
            // Write the updated content back to the .env file
            file_put_contents($path, $env);
            
        } catch (\Exception $e) {
            Log::error('Error updating .env file: ' . $e->getMessage());
            // Continue execution even if .env update fails
        }
    }
}