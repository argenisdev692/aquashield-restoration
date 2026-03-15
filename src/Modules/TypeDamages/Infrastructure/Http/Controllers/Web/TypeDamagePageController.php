<?php

declare(strict_types=1);

namespace Src\Modules\TypeDamages\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\TypeDamages\Application\Queries\GetTypeDamageHandler;

final class TypeDamagePageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('type-damages/TypeDamagesIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('type-damages/TypeDamageCreatePage');
    }

    public function show(string $uuid, GetTypeDamageHandler $handler): Response
    {
        $typeDamage = $handler->handle($uuid);

        if ($typeDamage === null) {
            abort(404);
        }

        return Inertia::render('type-damages/TypeDamageShowPage', [
            'typeDamage' => $typeDamage,
        ]);
    }

    public function edit(string $uuid, GetTypeDamageHandler $handler): Response
    {
        $typeDamage = $handler->handle($uuid);

        if ($typeDamage === null) {
            abort(404);
        }

        return Inertia::render('type-damages/TypeDamageEditPage', [
            'typeDamage' => $typeDamage,
        ]);
    }
}
