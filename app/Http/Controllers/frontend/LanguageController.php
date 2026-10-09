<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function select_lng(Request $request, $language = null)
    {
        $selectedLanguage = strtolower($language ?? $request->query('language', ''));

        if (Language::whereRaw('LOWER(name) = ?', [$selectedLanguage])->exists()) {
            session(['language' => $selectedLanguage]);
            $request->session()->save();
        }

        return redirect()->to(url()->previous() ?: route('home'));
    }
}
