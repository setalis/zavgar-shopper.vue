<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', array_keys(config('app.available_locales', [])))],
        ]);

        session([
            'locale' => $data['locale'],
            'shopper_locale' => $data['locale'],
        ]);

        app()->setLocale($data['locale']);

        return back();
    }
}
