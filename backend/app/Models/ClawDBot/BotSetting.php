<?php

namespace App\Models\ClawDBot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BotSetting extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
        'is_public',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at'
    ];

    /**
     * Scope to get public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to get settings by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get setting value
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get boolean setting value
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::getValue($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get integer setting value
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = static::getValue($key, $default);
        return (int) $value;
    }

    /**
     * Get array setting value
     */
    public static function getArray(string $key, array $default = []): array
    {
        $setting = static::where('key', $key)->first();
        return $setting && $setting->type === 'array' ? $setting->value : $default;
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value, string $type = 'string', string $description = null, bool $isPublic = false)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_public' => $isPublic
            ]
        );
    }

    /**
     * Get all public settings as array
     */
    public static function getAllPublic(): array
    {
        return static::public()
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get all settings by type
     */
    public static function getAllByType(string $type): array
    {
        return static::ofType($type)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }
}
