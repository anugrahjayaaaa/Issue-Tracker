<?php

namespace App\Http\Controllers;

use App\Http\Requests\Translation\TranslationUpdateRequest;
use Spatie\TranslationLoader\LanguageLine;

class TranslationController extends Controller
{
    protected array $locales = ['en', 'id'];

    public function index()
    {
        $lines = LanguageLine::orderBy('group')->orderBy('key')->paginate(25);

        return view('settings.translations.index', [
            'lines' => $lines,
            'locales' => $this->locales,
        ]);
    }

    public function edit(LanguageLine $languageLine)
    {
        return view('settings.translations.edit', [
            'line' => $languageLine,
            'locales' => $this->locales,
        ]);
    }

    public function update(TranslationUpdateRequest $request, LanguageLine $languageLine)
    {
        $data = $request->validated();

        foreach ($this->locales as $locale) {
            $languageLine->setTranslation($locale, $data[$locale]);
        }
        $languageLine->save();

        return redirect()->route('translations.index')
            ->with('status', __('messages.saved') ?? 'Saved.');
    }
}
