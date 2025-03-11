<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value)
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Set multiple settings at once.
     *
     * @param array $settings
     * @return void
     */
    public static function setMultiple(array $settings)
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value);
        }
    }
}