<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use JsonException;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $languages = Language::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        $activeLanguages = Language::where('is_active', true)->orderBy('name')->get();
        $defaultLanguage = Setting::get('default_language', config('app.locale', 'en'));

        return view('admin.settings.languages.index', compact('languages', 'activeLanguages', 'defaultLanguage', 'search'));
    }

    public function updateDefault(Request $request)
    {
        $validated = $request->validate(['default_language' => 'required|string|exists:languages,code']);
        $language = Language::where('code', $validated['default_language'])->firstOrFail();
        if (! $language->is_active) {
            return back()->withErrors(['default_language' => 'The default language must be active.'])->withInput();
        }

        Setting::set('default_language', $language->code);

        return back()->with('success', 'Default language updated.');
    }

    public function create()
    {
        return view('admin.settings.languages.form', ['language' => new Language(), 'isEditing' => false]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:12', 'regex:/^[a-z]{2,3}(?:_[A-Z]{2})?$/', 'unique:languages,code'],
            'flutter_code' => 'nullable|string|max:12|regex:/^[a-zA-Z_-]+$/',
            'rtl' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $language = Language::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'flutter_code' => $validated['flutter_code'] ?? null,
            'rtl' => $request->boolean('rtl'),
            'is_active' => $request->boolean('is_active'),
        ]);
        $this->ensureTranslationFile($language);

        return redirect()->route('admin.settings.languages.index')->with('success', 'Language added.');
    }

    public function edit(Language $language)
    {
        return view('admin.settings.languages.form', ['language' => $language, 'isEditing' => true]);
    }

    public function update(Request $request, Language $language)
    {
        if ($language->code === 'en' && $request->input('code') !== 'en') {
            return back()->withErrors(['code' => 'The English locale code cannot be changed.'])->withInput();
        }
        if (Setting::get('default_language', config('app.locale', 'en')) === $language->code && ! $request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'The default language must remain active.'])->withInput();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:12', 'regex:/^[a-z]{2,3}(?:_[A-Z]{2})?$/', Rule::unique('languages', 'code')->ignore($language->id)],
            'flutter_code' => 'nullable|string|max:12|regex:/^[a-zA-Z_-]+$/',
            'rtl' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $oldCode = $language->code;
        $language->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'flutter_code' => $validated['flutter_code'] ?? null,
            'rtl' => $request->boolean('rtl'),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($oldCode !== $language->code) {
            $oldPath = lang_path($oldCode);
            $newPath = lang_path($language->code);
            if (File::isDirectory($oldPath) && ! File::exists($newPath)) {
                File::moveDirectory($oldPath, $newPath);
            }
            if (Setting::get('default_language') === $oldCode) {
                Setting::set('default_language', $language->code);
            }
        }

        $this->ensureTranslationFile($language);

        return redirect()->route('admin.settings.languages.index')->with('success', 'Language updated.');
    }

    public function destroy(Language $language)
    {
        if ($language->code === 'en' || Setting::get('default_language', config('app.locale', 'en')) === $language->code) {
            return back()->with('error', 'The English or default language cannot be deleted.');
        }
        if ($language->is_active && Language::where('is_active', true)->count() <= 1) {
            return back()->with('error', 'At least one language must remain active.');
        }

        $language->delete();

        return redirect()->route('admin.settings.languages.index')->with('success', 'Language deleted.');
    }

    public function toggleStatus(Language $language)
    {
        if ($language->is_active && Setting::get('default_language', config('app.locale', 'en')) === $language->code) {
            return response()->json(['message' => 'The default language cannot be disabled.'], 422);
        }
        if ($language->is_active && Language::where('is_active', true)->count() <= 1) {
            return response()->json(['message' => 'At least one language must remain active.'], 422);
        }

        $language->update(['is_active' => ! $language->is_active]);

        return response()->json(['success' => true, 'is_active' => $language->is_active]);
    }

    public function toggleRtl(Language $language)
    {
        $language->update(['rtl' => ! $language->rtl]);

        return response()->json(['success' => true, 'rtl' => $language->rtl]);
    }

    public function translations(Language $language)
    {
        $translations = $this->readTranslations($language);

        return view('admin.settings.languages.translations', compact('language', 'translations'));
    }

    public function updateTranslations(Request $request, Language $language)
    {
        $validated = $request->validate([
            'translations' => 'required|array',
            'translations.*' => 'nullable|string|max:10000',
        ]);
        $translations = $this->readTranslations($language);

        foreach ($validated['translations'] as $key => $value) {
            if (array_key_exists($key, $translations)) {
                $translations[$key] = $value ?? '';
            }
        }

        $this->writeTranslations($language, Arr::undot($translations));

        return redirect()->route('admin.settings.languages.translations', $language)->with('success', 'Translations saved.');
    }

    public function importAppTranslations(Request $request, Language $language)
    {
        $request->validate(['lang_file' => 'required|file|max:2048']);
        if (strtolower($request->file('lang_file')->getClientOriginalExtension()) !== 'arb') {
            return back()->withErrors(['lang_file' => 'Upload an .arb translation file.']);
        }

        try {
            $decoded = json_decode($request->file('lang_file')->get(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return back()->withErrors(['lang_file' => 'The ARB file must contain valid JSON.']);
        }
        if (! is_array($decoded)) {
            return back()->withErrors(['lang_file' => 'The ARB file must contain a JSON object.']);
        }

        $translations = [];
        foreach ($decoded as $key => $value) {
            if (is_string($key) && ! str_starts_with($key, '@') && is_string($value)) {
                $translations[$key] = $value;
            }
        }
        if ($translations === []) {
            return back()->withErrors(['lang_file' => 'No translation entries were found in the ARB file.']);
        }

        $this->writeTranslations($language, Arr::undot($translations));

        return redirect()->route('admin.settings.languages.translations', $language)->with('success', 'ARB translations imported.');
    }

    public function exportAppTranslations(Language $language)
    {
        $content = json_encode($this->readTranslations($language), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response($content, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="app_'.$language->code.'.arb"',
        ]);
    }

    private function readTranslations(Language $language): array
    {
        $this->ensureTranslationFile($language);
        $translations = require lang_path($language->code.'/messages.php');

        return is_array($translations) ? Arr::dot($translations) : [];
    }

    private function writeTranslations(Language $language, array $translations): void
    {
        $directory = lang_path($language->code);
        File::ensureDirectoryExists($directory);
        File::put($directory.'/messages.php', "<?php\n\nreturn ".var_export($translations, true).";\n");
    }

    private function ensureTranslationFile(Language $language): void
    {
        $path = lang_path($language->code.'/messages.php');
        if (File::exists($path)) {
            return;
        }

        $basePath = lang_path('en/messages.php');
        File::ensureDirectoryExists(dirname($path));
        File::copy($basePath, $path);
    }
}
