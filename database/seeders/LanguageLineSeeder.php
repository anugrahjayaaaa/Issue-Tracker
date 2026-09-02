<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\TranslationLoader\LanguageLine;

class LanguageLineSeeder extends Seeder
{
    public function run(): void
    {
        // ponytail: validation lang stays file-only — spatie DB loader can't handle
        // nested array (between/size/password/attributes) → getTranslation() TypeError
        $groups = ['messages', 'ui'];
        $locales = config('app.available_locales');

        foreach ($groups as $group) {
            $keys = [];
            foreach ($locales as $locale) {
                $path = lang_path("{$locale}/{$group}.php");
                if (! file_exists($path)) {
                    continue;
                }
                foreach (require $path as $key => $text) {
                    $keys[$key][$locale] = $text;
                }
            }
            foreach ($keys as $key => $text) {
                LanguageLine::updateOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['text' => $text],
                );
            }

            // ponytail: drop DB rows whose key no longer exists in lang files (idempotent cleanup)
            $validKeys = array_keys($keys);
            LanguageLine::where('group', $group)
                ->whereNotIn('key', $validKeys)
                ->delete();
        }
    }
}
