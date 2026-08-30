<?php

namespace App\Http\Controllers;

use App\Http\Requests\Locale\LocaleUpdateRequest;

class LocaleController extends Controller
{
    public function update(LocaleUpdateRequest $request)
    {
        session(['locale' => $request->locale]);

        return redirect()->back();
    }
}
