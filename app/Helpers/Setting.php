<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param string|array|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('App\Models\Setting');
        }

        if (is_array($key)) {
            return Setting::setMultiple($key);
        }

        return Setting::get($key, $default);
    }
}