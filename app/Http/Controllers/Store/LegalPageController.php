<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use Inertia\Inertia;
use Inertia\Response;

class LegalPageController extends Controller
{
    public function show(LegalPage $legalPage): Response
    {
        return Inertia::render('store/LegalPage', [
            'page' => [
                'title' => $legalPage->title,
                'content' => $legalPage->content,
                'metaTitle' => $legalPage->meta_title ?? $legalPage->title,
                'metaDescription' => $legalPage->meta_description,
            ],
        ]);
    }
}
