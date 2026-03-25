<?php

declare(strict_types=1);

namespace Src\Modules\Customers\Infrastructure\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Src\Modules\Customers\Application\Queries\GetCustomerHandler;

final class CustomerPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('customers/CustomersIndexPage');
    }

    public function create(): Response
    {
        return Inertia::render('customers/CustomerCreatePage');
    }

    public function show(string $uuid, GetCustomerHandler $handler): Response
    {
        $customer = $handler->handle($uuid);

        if ($customer === null) {
            abort(404);
        }

        return Inertia::render('customers/CustomerShowPage', [
            'customer' => $customer,
        ]);
    }

    public function edit(string $uuid, GetCustomerHandler $handler): Response
    {
        $customer = $handler->handle($uuid);

        if ($customer === null) {
            abort(404);
        }

        return Inertia::render('customers/CustomerEditPage', [
            'customer' => $customer,
        ]);
    }
}
