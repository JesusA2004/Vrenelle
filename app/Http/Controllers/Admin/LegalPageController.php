<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LegalPageController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        return Inertia::render('admin/legal/Index', [
            'pages' => LegalPage::orderBy('title')->get(),
        ]);
    }

    public function edit(Request $request, LegalPage $legalPage): Response
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        return Inertia::render('admin/legal/Form', ['page' => $legalPage]);
    }

    public function update(Request $request, LegalPage $legalPage): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ], ['title.required' => 'El título es obligatorio.', 'content.required' => 'El contenido es obligatorio.']);

        $legalPage->update($data);

        return redirect()->route('admin.legal.index')->with('success', 'Página legal actualizada correctamente.');
    }
}
