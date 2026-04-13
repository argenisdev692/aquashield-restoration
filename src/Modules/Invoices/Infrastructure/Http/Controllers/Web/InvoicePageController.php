<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class InvoicePageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('InvoicesIndexPage');
    }

    public function show(string $uuid): Response
    {
        return Inertia::render('InvoicesShowPage', ['uuid' => $uuid]);
    }

    public function create(): Response
    {
        return Inertia::render('InvoicesCreatePage');
    }

    public function edit(string $uuid): Response
    {
        return Inertia::render('InvoicesEditPage', ['uuid' => $uuid]);
    }
}
