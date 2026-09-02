<?php

namespace App\Http\Controllers;

use App\Http\Requests\Translation\TranslationUpdateRequest;
use Spatie\TranslationLoader\LanguageLine;

class TranslationController extends Controller
{
    protected array $locales = ['en', 'id'];

    public function index()
    {
        $lines = LanguageLine::query()
            ->when(request('q'), fn ($q, $s) => $q->where(function ($sq) use ($s) {
                $sq->where('group', 'like', "%$s%")
                    ->orWhere('key', 'like', "%$s%")
                    ->orWhereJsonContains('text->en', $s);
            }))
            ->orderBy(request('sort') ?: 'group', request('dir') ?: 'asc')
            ->paginate(25)
            ->withQueryString();

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
