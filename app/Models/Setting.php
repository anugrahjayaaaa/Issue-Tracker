<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    /** Get a setting value (string|null). */
    public static function get(string $key, ?string $default = null): ?string
    {
        if ($key === 'license_secret') {
            throw new \LogicException('Use config("app.license_secret") — Settings is not a secret store.');
        }

        $row = static::find($key);

        return $row ? $row->value : $default;
    }

    /** Set a setting value, insert-or-update. */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
