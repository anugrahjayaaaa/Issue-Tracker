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
        $row = static::find($key);

        return $row ? $row->value : $default;
    }

    /** Set a setting value, insert-or-update. */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
