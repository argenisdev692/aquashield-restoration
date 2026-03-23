<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class FileEsxPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('files-esx/FilesEsxIndexPage');
    }

    public function show(string $uuid): Response
    {
        return Inertia::render('files-esx/FilesEsxShowPage', compact('uuid'));
    }

    public function create(): Response
    {
        return Inertia::render('files-esx/FilesEsxCreatePage');
    }

    public function edit(string $uuid): Response
    {
        return Inertia::render('files-esx/FilesEsxEditPage', compact('uuid'));
    }
}
