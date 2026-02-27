#!/usr/bin/env bash
# ══════════════════════════════════════════════════════════════════
# generate-crud.sh — Scaffold a full CRUD module (Backend + Frontend)
#
# Usage:
#   bash generate-crud.sh ModuleName table_name "attr1:type attr2:type?"
#
# Examples:
#   bash generate-crud.sh InsuranceCompanies insurance_companies "name:string address:text? phone:string? email:string? website:string?"
#   bash generate-crud.sh Products products "name:string price:decimal description:text? category_id:foreignId"
#
# Types: string, text, integer, decimal, boolean, date, datetime, foreignId
# Append '?' to make a field nullable (e.g., phone:string?)
#
# What it generates:
#   1. Database migration
#   2. src/Contexts/{Module}/ — Domain, Application, Infrastructure
#   3. resources/js/types/{module}.ts
#   4. resources/js/modules/{module}/hooks + components
#   5. resources/js/Pages/{module}/ — Index, Create, Show, Edit
#   6. ServiceProvider (remember to register in bootstrap/providers.php)
# ══════════════════════════════════════════════════════════════════

set -euo pipefail

# ── Args ──────────────────────────────────────────────────────
MODULE_NAME="${1:?Usage: bash generate-crud.sh ModuleName table_name \"attr1:type attr2:type?\"}"
TABLE_NAME="${2:?Missing table name}"
ATTRS_RAW="${3:?Missing attributes}"

# ── Derived names ─────────────────────────────────────────────
MODULE_UPPER="${MODULE_NAME}"                                     # e.g. InsuranceCompanies
MODULE_LOWER="$(echo "$MODULE_NAME" | sed 's/\(.\)/\L\1/')"      # e.g. insuranceCompanies
MODULE_SNAKE="$(echo "$MODULE_NAME" | sed 's/\([A-Z]\)/_\L\1/g' | sed 's/^_//')"  # e.g. insurance_companies
MODULE_KEBAB="$(echo "$MODULE_SNAKE" | tr '_' '-')"               # e.g. insurance-companies
SINGULAR="$(echo "$MODULE_NAME" | sed 's/s$//' | sed 's/ie$/y/')" # e.g. InsuranceCompany
SINGULAR_SNAKE="$(echo "$SINGULAR" | sed 's/\([A-Z]\)/_\L\1/g' | sed 's/^_//')"
TIMESTAMP="$(date +%Y_%m_%d_%H%M%S)"

SRC_DIR="src/Contexts/${MODULE_UPPER}"
FE_TYPES="resources/js/types/${MODULE_SNAKE}.ts"
FE_MODULE="resources/js/modules/${MODULE_SNAKE}"
FE_PAGES="resources/js/Pages/${MODULE_SNAKE}"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Generating CRUD: ${MODULE_UPPER}"
echo "  Table: ${TABLE_NAME}"
echo "  Singular: ${SINGULAR}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# ── Parse attributes ──────────────────────────────────────────
declare -a ATTR_NAMES=()
declare -a ATTR_TYPES=()
declare -a ATTR_NULLABLE=()
declare -a ATTR_CAMEL=()

for attr_def in $ATTRS_RAW; do
  IFS=':' read -r aname atype <<< "$attr_def"
  nullable="false"
  if [[ "$atype" == *"?" ]]; then
    atype="${atype%?}"
    nullable="true"
  fi
  # camelCase
  camel="$(echo "$aname" | sed -r 's/_([a-z])/\U\1/g')"

  ATTR_NAMES+=("$aname")
  ATTR_TYPES+=("$atype")
  ATTR_NULLABLE+=("$nullable")
  ATTR_CAMEL+=("$camel")
done

# ── Helper: migration column type ────────────────────────────
migration_col() {
  local name="$1" type="$2" nullable="$3"
  local col="\$table->"
  case "$type" in
    string)     col+="string('${name}')" ;;
    text)       col+="text('${name}')" ;;
    integer)    col+="integer('${name}')" ;;
    decimal)    col+="decimal('${name}', 10, 2)" ;;
    boolean)    col+="boolean('${name}')->default(false)" ;;
    date)       col+="date('${name}')" ;;
    datetime)   col+="dateTime('${name}')" ;;
    foreignId)  col+="foreignId('${name}')->constrained()->onUpdate('cascade')->onDelete('cascade')" ;;
    *)          col+="string('${name}')" ;;
  esac
  [[ "$nullable" == "true" ]] && col+="->nullable()"
  col+=";"
  echo "            $col"
}

# ══════════════════════════════════════════════════════════════
# 1. MIGRATION
# ══════════════════════════════════════════════════════════════
MIG_FILE="database/migrations/${TIMESTAMP}_create_${TABLE_NAME}_table.php"
echo "📦 Creating migration: ${MIG_FILE}"

MIG_COLS=""
for i in "${!ATTR_NAMES[@]}"; do
  MIG_COLS+="$(migration_col "${ATTR_NAMES[$i]}" "${ATTR_TYPES[$i]}" "${ATTR_NULLABLE[$i]}")"$'\n'
done

cat > "$MIG_FILE" << MIGRATION_EOF
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('${TABLE_NAME}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('uuid')->unique();
${MIG_COLS}            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('${TABLE_NAME}');
    }
};
MIGRATION_EOF

# ══════════════════════════════════════════════════════════════
# 2. DOMAIN LAYER
# ══════════════════════════════════════════════════════════════
echo "🏗️  Creating Domain layer..."

# Domain Entity
mkdir -p "${SRC_DIR}/Domain/Entities"
ENTITY_PROPS=""
for i in "${!ATTR_NAMES[@]}"; do
  type_php="string"
  [[ "${ATTR_TYPES[$i]}" == "integer" || "${ATTR_TYPES[$i]}" == "foreignId" ]] && type_php="int"
  [[ "${ATTR_TYPES[$i]}" == "boolean" ]] && type_php="bool"
  [[ "${ATTR_TYPES[$i]}" == "decimal" ]] && type_php="float"
  prefix=""
  [[ "${ATTR_NULLABLE[$i]}" == "true" ]] && prefix="?"
  ENTITY_PROPS+="        public ${prefix}${type_php} \$${ATTR_CAMEL[$i]} = null,"$'\n'
done

cat > "${SRC_DIR}/Domain/Entities/${SINGULAR}.php" << ENTITY_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities;

final readonly class ${SINGULAR}
{
    public function __construct(
        public int \$id,
        public string \$uuid,
${ENTITY_PROPS}        public ?string \$createdAt = null,
        public ?string \$updatedAt = null,
    ) {}
}
ENTITY_EOF

# Domain Port
mkdir -p "${SRC_DIR}/Domain/Ports"
cat > "${SRC_DIR}/Domain/Ports/${SINGULAR}RepositoryPort.php" << PORT_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Domain\\Ports;

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities\\${SINGULAR};

interface ${SINGULAR}RepositoryPort
{
    public function findById(int \$id): ?${SINGULAR};
    /** @return array{data: list<${SINGULAR}>, total: int, perPage: int, currentPage: int, lastPage: int} */
    public function findAllPaginated(array \$filters = [], int \$page = 1, int \$perPage = 15): array;
    public function create(array \$data): ${SINGULAR};
    public function update(int \$id, array \$data): ${SINGULAR};
    public function softDelete(int \$id): void;
}
PORT_EOF

# Domain Exception
mkdir -p "${SRC_DIR}/Domain/Exceptions"
cat > "${SRC_DIR}/Domain/Exceptions/${SINGULAR}NotFoundException.php" << EXC_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Domain\\Exceptions;

use RuntimeException;

final class ${SINGULAR}NotFoundException extends RuntimeException
{
    public static function withId(int \$id): self
    {
        return new self("${SINGULAR} with ID [{\$id}] not found.");
    }
}
EXC_EOF

# Domain Events
mkdir -p "${SRC_DIR}/Domain/Events"
cat > "${SRC_DIR}/Domain/Events/${SINGULAR}Created.php" << DEV_C_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events;

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities\\${SINGULAR};
use Illuminate\\Foundation\\Events\\Dispatchable;

final class ${SINGULAR}Created
{
    use Dispatchable;

    public function __construct(public readonly ${SINGULAR} \$entity) {}
}
DEV_C_EOF

cat > "${SRC_DIR}/Domain/Events/${SINGULAR}Updated.php" << DEV_U_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events;

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities\\${SINGULAR};
use Illuminate\\Foundation\\Events\\Dispatchable;

final class ${SINGULAR}Updated
{
    use Dispatchable;

    public function __construct(public readonly ${SINGULAR} \$entity) {}
}
DEV_U_EOF

cat > "${SRC_DIR}/Domain/Events/${SINGULAR}Deleted.php" << DEV_D_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events;

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities\\${SINGULAR};
use Illuminate\\Foundation\\Events\\Dispatchable;

final class ${SINGULAR}Deleted
{
    use Dispatchable;

    public function __construct(public readonly ${SINGULAR} \$entity) {}
}
DEV_D_EOF

# Domain Subscribers
mkdir -p "${SRC_DIR}/Domain/Subscribers"
cat > "${SRC_DIR}/Domain/Subscribers/Invalidate${SINGULAR}Cache.php" << SUB_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Domain\\Subscribers;

use Illuminate\\Events\\Dispatcher;
use Illuminate\\Support\\Facades\\Cache;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events\\${SINGULAR}Created;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events\\${SINGULAR}Updated;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events\\${SINGULAR}Deleted;

final class Invalidate${SINGULAR}Cache
{
    public function subscribe(Dispatcher \$events): void
    {
        \$events->listen(
            [${SINGULAR}Created::class, ${SINGULAR}Updated::class, ${SINGULAR}Deleted::class],
            [\$this, 'handleInvalidation']
        );
    }

    public function handleInvalidation(object \$event): void
    {
        // For individual record
        if (isset(\$event->entity->id)) {
            Cache::forget("${MODULE_SNAKE}_" . \$event->entity->id);
        }
        
        // For lists (if not using Redis tags, a wildcard mechanism is often needed, or rely on TTL)
        // Cache::tags(["${MODULE_SNAKE}_list"])->flush();
    }
}
SUB_EOF

# ══════════════════════════════════════════════════════════════
# 3. APPLICATION LAYER (CQRS)
# ══════════════════════════════════════════════════════════════
echo "⚙️  Creating Application layer..."

# Create Command + Handler
mkdir -p "${SRC_DIR}/Application/Commands/Create${SINGULAR}"
cat > "${SRC_DIR}/Application/Commands/Create${SINGULAR}/Create${SINGULAR}Command.php" << CMD_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Commands\\Create${SINGULAR};

final readonly class Create${SINGULAR}Command
{
    public function __construct(
        public array \$data,
    ) {}
}
CMD_EOF

cat > "${SRC_DIR}/Application/Commands/Create${SINGULAR}/Create${SINGULAR}Handler.php" << HDL_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Commands\\Create${SINGULAR};

use Illuminate\\Support\\Str;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities\\${SINGULAR};
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events\\${SINGULAR}Created;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Ports\\${SINGULAR}RepositoryPort;

final readonly class Create${SINGULAR}Handler
{
    public function __construct(private ${SINGULAR}RepositoryPort \$repository) {}

    public function handle(Create${SINGULAR}Command \$command): ${SINGULAR}
    {
        \$entity = \$this->repository->create(array_merge(
            ['uuid' => Str::uuid()->toString()],
            \$command->data,
        ));
        
        event(new ${SINGULAR}Created(\$entity));

        return \$entity;
    }
}
HDL_EOF

# Update Command + Handler
mkdir -p "${SRC_DIR}/Application/Commands/Update${SINGULAR}"
cat > "${SRC_DIR}/Application/Commands/Update${SINGULAR}/Update${SINGULAR}Command.php" << UCMD_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Commands\\Update${SINGULAR};

final readonly class Update${SINGULAR}Command
{
    public function __construct(public int \$id, public array \$data) {}
}
UCMD_EOF

cat > "${SRC_DIR}/Application/Commands/Update${SINGULAR}/Update${SINGULAR}Handler.php" << UHDL_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Commands\\Update${SINGULAR};

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities\\${SINGULAR};
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events\\${SINGULAR}Updated;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Exceptions\\${SINGULAR}NotFoundException;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Ports\\${SINGULAR}RepositoryPort;

final readonly class Update${SINGULAR}Handler
{
    public function __construct(private ${SINGULAR}RepositoryPort \$repository) {}

    public function handle(Update${SINGULAR}Command \$command): ${SINGULAR}
    {
        if (\$this->repository->findById(\$command->id) === null) {
            throw ${SINGULAR}NotFoundException::withId(\$command->id);
        }
        \$updatedEntity = \$this->repository->update(\$command->id, \$command->data);
        
        event(new ${SINGULAR}Updated(\$updatedEntity));

        return \$updatedEntity;
    }
}
UHDL_EOF

# Delete Command + Handler
mkdir -p "${SRC_DIR}/Application/Commands/Delete${SINGULAR}"
cat > "${SRC_DIR}/Application/Commands/Delete${SINGULAR}/Delete${SINGULAR}Command.php" << DCMD_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Commands\\Delete${SINGULAR};

final readonly class Delete${SINGULAR}Command
{
    public function __construct(public int \$id) {}
}
DCMD_EOF

cat > "${SRC_DIR}/Application/Commands/Delete${SINGULAR}/Delete${SINGULAR}Handler.php" << DHDL_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Commands\\Delete${SINGULAR};

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Events\\${SINGULAR}Deleted;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Exceptions\\${SINGULAR}NotFoundException;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Ports\\${SINGULAR}RepositoryPort;

final readonly class Delete${SINGULAR}Handler
{
    public function __construct(private ${SINGULAR}RepositoryPort \$repository) {}

    public function handle(Delete${SINGULAR}Command \$command): void
    {
        \$entity = \$this->repository->findById(\$command->id);
        if (\$entity === null) {
            throw ${SINGULAR}NotFoundException::withId(\$command->id);
        }
        \$this->repository->softDelete(\$command->id);
        
        event(new ${SINGULAR}Deleted(\$entity));
    }
}
DHDL_EOF

# List Query + Handler
mkdir -p "${SRC_DIR}/Application/Queries/List${MODULE_UPPER}"
cat > "${SRC_DIR}/Application/Queries/List${MODULE_UPPER}/List${MODULE_UPPER}Query.php" << LQ_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Queries\\List${MODULE_UPPER};

final readonly class List${MODULE_UPPER}Query
{
    public function __construct(public array \$filters = [], public int \$page = 1, public int \$perPage = 15) {}
}
LQ_EOF

cat > "${SRC_DIR}/Application/Queries/List${MODULE_UPPER}/List${MODULE_UPPER}Handler.php" << LH_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Queries\\List${MODULE_UPPER};

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Ports\\${SINGULAR}RepositoryPort;
use Illuminate\\Support\\Facades\\Cache;

final readonly class List${MODULE_UPPER}Handler
{
    public function __construct(private ${SINGULAR}RepositoryPort \$repository) {}

    public function handle(List${MODULE_UPPER}Query \$query): array
    {
        \$cacheKey = "${MODULE_SNAKE}_list_" . md5(serialize(\$query->filters));
        \$ttl = now()->addMinutes(15);

        return Cache::remember(\$cacheKey, \$ttl, function () use (\$query) {
            return \$this->repository->findAllPaginated(\$query->filters, \$query->page, \$query->perPage);
        });
    }
}
LH_EOF

# Get Query + Handler
mkdir -p "${SRC_DIR}/Application/Queries/Get${SINGULAR}"
cat > "${SRC_DIR}/Application/Queries/Get${SINGULAR}/Get${SINGULAR}Query.php" << GQ_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Queries\\Get${SINGULAR};

final readonly class Get${SINGULAR}Query
{
    public function __construct(public int \$id) {}
}
GQ_EOF

cat > "${SRC_DIR}/Application/Queries/Get${SINGULAR}/Get${SINGULAR}Handler.php" << GH_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Application\\Queries\\Get${SINGULAR};

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities\\${SINGULAR};
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Exceptions\\${SINGULAR}NotFoundException;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Ports\\${SINGULAR}RepositoryPort;
use Illuminate\\Support\\Facades\\Cache;

final readonly class Get${SINGULAR}Handler
{
    public function __construct(private ${SINGULAR}RepositoryPort \$repository) {}

    public function handle(Get${SINGULAR}Query \$query): ${SINGULAR}
    {
        \$cacheKey = "${MODULE_SNAKE}_" . \$query->id;
        \$ttl = now()->addMinutes(15);

        \$entity = Cache::remember(\$cacheKey, \$ttl, function () use (\$query) {
            return \$this->repository->findById(\$query->id);
        });

        if (\$entity === null) {
            throw ${SINGULAR}NotFoundException::withId(\$query->id);
        }
        return \$entity;
    }
}
GH_EOF

# ══════════════════════════════════════════════════════════════
# 4. INFRASTRUCTURE — PROVIDER
# ══════════════════════════════════════════════════════════════
echo "🔧 Creating Infrastructure + Provider..."

mkdir -p "${SRC_DIR}/Providers"
cat > "${SRC_DIR}/Providers/${MODULE_UPPER}ServiceProvider.php" << SP_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Providers;

use Illuminate\\Support\\ServiceProvider;
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Ports\\${SINGULAR}RepositoryPort;
use Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Persistence\\Repositories\\Eloquent${SINGULAR}Repository;

final class ${MODULE_UPPER}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(${SINGULAR}RepositoryPort::class, Eloquent${SINGULAR}Repository::class);
    }

    public function boot(): void
    {
        // Register Domain Event Subscribers
        \$this->app['events']->subscribe(\\Src\\Contexts\\${MODULE_UPPER}\\Domain\\Subscribers\\Invalidate${SINGULAR}Cache::class);
        
        // Routes are loaded from web.php — register in routes/web.php
    }
}
SP_EOF

# Infrastructure — Eloquent Model
mkdir -p "${SRC_DIR}/Infrastructure/Persistence/Eloquent/Models"

FILLABLE=""
for name in "${ATTR_NAMES[@]}"; do
  FILLABLE+="        '${name}',"$'\n'
done

cat > "${SRC_DIR}/Infrastructure/Persistence/Eloquent/Models/${SINGULAR}EloquentModel.php" << EM_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Persistence\\Eloquent\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\SoftDeletes;
use Spatie\\Activitylog\\Traits\\LogsActivity;
use Spatie\\Activitylog\\LogOptions;

/** @internal */
final class ${SINGULAR}EloquentModel extends Model // implements \Shared\Infrastructure\Audit\AuditableInterface (if exists)
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string \$eventName) => "${SINGULAR} has been {\$eventName}");
    }

    protected \$table = '${TABLE_NAME}';

    protected \$fillable = [
        'uuid',
${FILLABLE}    ];

    public function scopeInDateRange(
        \Illuminate\Database\Eloquent\Builder $query,
        ?string $from,
        ?string $to,
        string $column = 'created_at'
    ): \Illuminate\Database\Eloquent\Builder {
        return $query
            ->when($from, fn($q) => $q->whereDate($column, '>=', $from))
            ->when($to, fn($q) => $q->whereDate($column, '<=', $to));
    }
}
EM_EOF

# Infrastructure — Export logic
mkdir -p "${SRC_DIR}/Infrastructure/Adapters/Http/Export"
cat > "${SRC_DIR}/Infrastructure/Adapters/Http/Export/${SINGULAR}ExcelExport.php" << EXCEL_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Adapters\\Http\\Export;

use Maatwebsite\\Excel\\Concerns\\FromQuery;
use Maatwebsite\\Excel\\Concerns\\Exportable;
use Maatwebsite\\Excel\\Concerns\\WithHeadings;
use Maatwebsite\\Excel\\Concerns\\WithMapping;
use Maatwebsite\\Excel\\Concerns\\ShouldAutoSize;
use Maatwebsite\\Excel\\Concerns\\WithTitle;
use Maatwebsite\\Excel\\Concerns\\WithStyles;
use PhpOffice\\PhpSpreadsheet\\Worksheet\\Worksheet;
use Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Persistence\\Eloquent\\Models\\${SINGULAR}EloquentModel;
use Illuminate\\Database\\Eloquent\\Builder;

final class ${SINGULAR}ExcelExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle,
    WithStyles
{
    use Exportable;

    public function __construct(private readonly array \$filters) {}

    public function query(): Builder
    {
        return ${SINGULAR}EloquentModel::query()
            ->whereNull('deleted_at')
            ->when(\$this->filters['search'] ?? null, fn(\$q, \$s) => \$q->where('uuid', 'like', "%\$s%"))
            ->when((\$this->filters['dateFrom'] ?? null) || (\$this->filters['dateTo'] ?? null), 
                fn(\$q) => \$q->inDateRange(\$this->filters['dateFrom'] ?? null, \$this->filters['dateTo'] ?? null)
            )
            ->orderBy(\$this->filters['sortBy'] ?? 'created_at', \$this->filters['sortDir'] ?? 'desc');
    }

    public function headings(): array
    {
        return ['UUID', 'Created At'];
    }

    public function map(\$item): array
    {
        return [\$item->uuid, \$item->created_at?->toIso8601String()];
    }

    public function title(): string
    {
        return '${MODULE_UPPER} Export';
    }

    public function styles(Worksheet \$sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
EXCEL_EOF

cat > "${SRC_DIR}/Infrastructure/Adapters/Http/Export/${SINGULAR}PdfExport.php" << PDF_CLASS_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Adapters\\Http\\Export;

use Barryvdh\\DomPDF\\Facade\\Pdf;
use Illuminate\\Http\\Response;
use Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Persistence\\Eloquent\\Models\\${SINGULAR}EloquentModel;

final class ${SINGULAR}PdfExport
{
    public function __construct(private readonly array \$filters) {}

    public function stream(): Response
    {
        \$rows = ${SINGULAR}EloquentModel::query()
            ->whereNull('deleted_at')
            ->when(\$this->filters['search'] ?? null, fn(\$q, \$s) => \$q->where('uuid', 'like', "%\$s%"))
            ->when((\$this->filters['dateFrom'] ?? null) || (\$this->filters['dateTo'] ?? null), 
                fn(\$q) => \$q->inDateRange(\$this->filters['dateFrom'] ?? null, \$this->filters['dateTo'] ?? null)
            )
            ->orderBy(\$this->filters['sortBy'] ?? 'created_at', \$this->filters['sortDir'] ?? 'desc')
            ->get();

        \$pdf = Pdf::loadView('exports.pdf.${MODULE_SNAKE}', [
            'title' => '${MODULE_UPPER} Report',
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'rows' => \$rows,
        ]);

        return \$pdf->stream('${MODULE_KEBAB}-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
PDF_CLASS_EOF

# Infrastructure — Export Controller
mkdir -p "${SRC_DIR}/Infrastructure/Adapters/Http/Controllers/Api"
cat > "${SRC_DIR}/Infrastructure/Adapters/Http/Controllers/Api/${SINGULAR}ExportController.php" << EXP_CTRL_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Adapters\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\Http\\Response;
use Maatwebsite\\Excel\\Facades\\Excel;
use Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Adapters\\Http\\Export\\${SINGULAR}ExcelExport;
use Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Adapters\\Http\\Export\\${SINGULAR}PdfExport;
use Symfony\\Component\\HttpFoundation\\BinaryFileResponse;

final class ${SINGULAR}ExportController extends Controller
{
    public function __invoke(Request \$request): Response|BinaryFileResponse
    {
        \$filters = \$request->all();
        \$format = \$request->query('format', 'excel');

        return match (\$format) {
            'excel' => Excel::download(
                new ${SINGULAR}ExcelExport(\$filters),
                '${MODULE_KEBAB}-export-' . now()->format('Y-m-d') . '.xlsx'
            ),
            'pdf' => (new ${SINGULAR}PdfExport(\$filters))->stream(),
            default => response()->json(['error' => 'Invalid format'], 422),
        };
    }
}
EXP_CTRL_EOF

# PDF View
mkdir -p "resources/views/exports/pdf"
cat > "resources/views/exports/pdf/${MODULE_SNAKE}.blade.php" << BLADE_EOF
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ \$title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #22d3ee; padding-bottom: 10px; }
        .logo { height: 50px; }
        .title { font-size: 18px; font-weight: bold; color: #0891b2; }
        .meta { margin-top: 5px; color: #666; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f3f4f6; color: #333; text-align: left; padding: 8px; border: 1px solid #e5e7eb; }
        td { padding: 8px; border: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #fafafa; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin: 0;">
            <tr style="background: none;">
                <td style="border: none; padding: 0;">
                    <img src="{{ public_path('img/Logo PNG.png') }}" class="logo" alt="Logo">
                </td>
                <td style="border: none; text-align: right; vertical-align: middle;">
                    <div class="title">{{ \$title }}</div>
                    <div class="meta">Generated on: {{ \$generatedAt }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>UUID</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach(\$rows as \$item)
            <tr>
                <td>{{ \$item->uuid }}</td>
                <td>{{ \$item->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        AquaShield CRM - Page <span class="pagenum"></span>
    </div>
</body>
</html>
BLADE_EOF

# Infrastructure — Repository
mkdir -p "${SRC_DIR}/Infrastructure/Persistence/Repositories"
cat > "${SRC_DIR}/Infrastructure/Persistence/Repositories/Eloquent${SINGULAR}Repository.php" << REPO_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Persistence\\Repositories;

use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Entities\\${SINGULAR};
use Src\\Contexts\\${MODULE_UPPER}\\Domain\\Ports\\${SINGULAR}RepositoryPort;
use Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Persistence\\Eloquent\\Models\\${SINGULAR}EloquentModel;

final class Eloquent${SINGULAR}Repository implements ${SINGULAR}RepositoryPort
{
    public function findById(int \$id): ?${SINGULAR}
    {
        \$model = ${SINGULAR}EloquentModel::find(\$id);
        return \$model ? \$this->toDomain(\$model) : null;
    }

    public function findAllPaginated(array \$filters = [], int \$page = 1, int \$perPage = 15): array
    {
        \$query = ${SINGULAR}EloquentModel::query()
            ->when(\$filters['search'] ?? null, fn(\$q, \$s) =>
                \$q->where('uuid', 'like', "%{\$s}%")
            )
            ->orderBy(\$filters['sortBy'] ?? 'created_at', \$filters['sortDir'] ?? 'desc');

        \$paginator = \$query->paginate(perPage: \$perPage, page: \$page);

        return [
            'data' => array_map(fn(\$m) => \$this->toDomain(\$m), \$paginator->items()),
            'total' => \$paginator->total(),
            'perPage' => \$paginator->perPage(),
            'currentPage' => \$paginator->currentPage(),
            'lastPage' => \$paginator->lastPage(),
        ];
    }

    public function create(array \$data): ${SINGULAR}
    {
        return \$this->toDomain(${SINGULAR}EloquentModel::create(\$data));
    }

    public function update(int \$id, array \$data): ${SINGULAR}
    {
        \$model = ${SINGULAR}EloquentModel::findOrFail(\$id);
        \$model->update(\$data);
        \$model->refresh();
        return \$this->toDomain(\$model);
    }

    public function softDelete(int \$id): void
    {
        ${SINGULAR}EloquentModel::findOrFail(\$id)->delete();
    }

    private function toDomain(${SINGULAR}EloquentModel \$model): ${SINGULAR}
    {
        return new ${SINGULAR}(
            id: \$model->id,
            uuid: \$model->uuid,
            createdAt: \$model->created_at?->toIso8601String(),
            updatedAt: \$model->updated_at?->toIso8601String(),
        );
    }
}
REPO_EOF

# Permissions Seeder
mkdir -p "${SRC_DIR}/Infrastructure/Persistence/Eloquent/Seeders"
PERMS_UPPER="$(echo "$MODULE_UPPER" | tr '[:lower:]' '[:upper:]')"
cat > "${SRC_DIR}/Infrastructure/Persistence/Eloquent/Seeders/${MODULE_UPPER}PermissionsSeeder.php" << SEED_EOF
<?php

declare(strict_types=1);

namespace Src\\Contexts\\${MODULE_UPPER}\\Infrastructure\\Persistence\\Eloquent\\Seeders;

use Illuminate\\Database\\Seeder;
use Spatie\\Permission\\Models\\Permission;
use Spatie\\Permission\\Models\\Role;

final class ${MODULE_UPPER}PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\\Spatie\\Permission\\PermissionRegistrar::class]->forgetCachedPermissions();

        \$permissions = [
            'VIEW_${PERMS_UPPER}',
            'CREATE_${PERMS_UPPER}',
            'UPDATE_${PERMS_UPPER}',
            'DELETE_${PERMS_UPPER}',
        ];

        foreach (\$permissions as \$p) {
            Permission::firstOrCreate(['name' => \$p, 'guard_name' => 'web']);
        }

        \$role = Role::firstOrCreate(['name' => '${MODULE_UPPER}', 'guard_name' => 'web']);
        \$role->syncPermissions(\$permissions);

        \$superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        \$superAdmin->givePermissionTo(\$permissions);
    }
}
SEED_EOF

# ══════════════════════════════════════════════════════════════
# 5. FRONTEND SCAFFOLD
# ══════════════════════════════════════════════════════════════
echo "🎨 Creating Frontend scaffold..."

# Types
mkdir -p "$(dirname "$FE_TYPES")"
TS_FIELDS=""
for i in "${!ATTR_NAMES[@]}"; do
  tsType="string"
  [[ "${ATTR_TYPES[$i]}" == "integer" || "${ATTR_TYPES[$i]}" == "foreignId" ]] && tsType="number"
  [[ "${ATTR_TYPES[$i]}" == "boolean" ]] && tsType="boolean"
  [[ "${ATTR_TYPES[$i]}" == "decimal" ]] && tsType="number"
  null_suffix=""
  [[ "${ATTR_NULLABLE[$i]}" == "true" ]] && null_suffix=" | null"
  TS_FIELDS+="  ${ATTR_CAMEL[$i]}: ${tsType}${null_suffix};"$'\n'
done

cat > "$FE_TYPES" << TYPES_EOF
export interface ${SINGULAR}ListItem {
  id: number;
  uuid: string;
${TS_FIELDS}  createdAt: string | null;
  updatedAt: string | null;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: { currentPage: number; lastPage: number; perPage: number; total: number };
}

export interface ${SINGULAR}Filters {
  page?: number;
  perPage?: number;
  search?: string;
  dateFrom?: string;
  dateTo?: string;
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
}
TYPES_EOF

# Hooks
mkdir -p "${FE_MODULE}/hooks"
cat > "${FE_MODULE}/hooks/use${MODULE_UPPER}.ts" << HOOK_EOF
import axios from 'axios';
import type { ${SINGULAR}ListItem, PaginatedResponse, ${SINGULAR}Filters } from '@/types/${MODULE_SNAKE}';

export async function fetch${MODULE_UPPER}(params: ${SINGULAR}Filters = {}): Promise<PaginatedResponse<${SINGULAR}ListItem>> {
  const { data } = await axios.get<PaginatedResponse<${SINGULAR}ListItem>>('/api/${MODULE_KEBAB}', { params });
  return data;
}
HOOK_EOF

cat > "${FE_MODULE}/hooks/use${SINGULAR}Mutations.ts" << MUT_EOF
import axios from 'axios';

export async function create${SINGULAR}(payload: Record<string, unknown>) {
  const { data } = await axios.post('/api/${MODULE_SNAKE}', payload);
  return data.data;
}

export async function update${SINGULAR}(id: number, payload: Record<string, unknown>) {
  const { data } = await axios.put(\`/api/${MODULE_SNAKE}/\${id}\`, payload);
  return data.data;
}

export async function delete${SINGULAR}(id: number): Promise<void> {
  await axios.delete(\`/api/${MODULE_SNAKE}/\${id}\`);
}
MUT_EOF

# Pages (minimal scaffold)
mkdir -p "${FE_PAGES}"
cat > "${FE_PAGES}/${MODULE_UPPER}IndexPage.tsx" << PAGE_EOF
import * as React from 'react';
import { type RowSelectionState } from '@tanstack/react-table';
import AppLayout from '@/Pages/layouts/AppLayout';
import { DataTableBulkActions } from '@/components/ui/DataTableBulkActions';
import { DeleteConfirmModal } from '@/components/ui/DeleteConfirmModal';
import { DataTableDateRangeFilter } from '@/components/common/data-table/DataTableDateRangeFilter';
import { ExportButton } from '@/components/common/export/ExportButton';
import { fetch${MODULE_UPPER} } from '@/modules/${MODULE_SNAKE}/hooks/use${MODULE_UPPER}';
import type { ${SINGULAR}ListItem, PaginatedResponse, ${SINGULAR}Filters } from '@/types/${MODULE_SNAKE}';

export default function ${MODULE_UPPER}IndexPage(): React.JSX.Element {
  const [data, setData] = React.useState<${SINGULAR}ListItem[]>([]);
  const [meta, setMeta] = React.useState<PaginatedResponse<${SINGULAR}ListItem>['meta']>({
    currentPage: 1, lastPage: 1, perPage: 15, total: 0,
  });
  const [filters, setFilters] = React.useState<${SINGULAR}Filters>({ page: 1, perPage: 15 });
  const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
  const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
  const [isDeleting, setIsDeleting] = React.useState<boolean>(false);
  const [isExporting, setIsExporting] = React.useState<boolean>(false);
  const [loading, setLoading] = React.useState<boolean>(true);

  const loadData = React.useCallback(async (f: ${SINGULAR}Filters) => {
    setLoading(true);
    try {
      const res = await fetch${MODULE_UPPER}(f);
      setData(res.data);
      setMeta(res.meta);
    } catch (err) {
      console.error('Failed to fetch data', err);
    } finally {
      setLoading(false);
    }
  }, []);

  React.useEffect(() => {
    void loadData(filters);
  }, [filters, loadData]);

  const selectedUuids = Object.keys(rowSelection).filter((k) => rowSelection[k]);

  async function handleExport(format: 'excel' | 'pdf'): Promise<void> {
    setIsExporting(true);
    try {
      const params = new URLSearchParams();
      if (filters.search) params.append('search', filters.search);
      if (filters.dateFrom) params.append('dateFrom', filters.dateFrom);
      if (filters.dateTo) params.append('dateTo', filters.dateTo);
      params.append('format', format);
      window.open(\`/api/${MODULE_KEBAB}/export?\${params.toString()}\`, '_blank');
    } finally {
      setIsExporting(false);
    }
  }

  function handleBulkDelete(): void {
    if (!selectedUuids.length) return;
  }

  function handleConfirmSingleDelete(): void {
    if (!pendingDelete) return;
  }

  return (
    <AppLayout>
      <div style={{ fontFamily: 'var(--font-sans)' }}>
        <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
           <div>
             <h1 className="text-2xl font-bold tracking-tight" style={{ color: 'var(--text-primary)' }}>
               ${MODULE_UPPER}
             </h1>
             <p className="text-sm mt-1" style={{ color: 'var(--text-muted)' }}>
               Total entries: {meta.total}
             </p>
           </div>
        </div>
        
        <div 
          className="mb-4 flex flex-col items-center gap-3 rounded-xl px-4 py-3 sm:flex-row"
          style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
        >
          <div className="flex flex-1 items-center gap-3 w-full">
            <input
              type="text"
              placeholder="Search..."
              className="flex-1 bg-transparent text-sm outline-none"
              style={{ color: 'var(--text-primary)' }}
              onChange={(e) => setFilters(p => ({ ...p, search: e.target.value || undefined, page: 1 }))}
            />
          </div>

          <div className="flex w-full items-center gap-4 sm:w-auto">
            <div className="h-8 w-px hidden sm:block" style={{ background: 'var(--border-subtle)' }} />
            <DataTableDateRangeFilter
              dateFrom={filters.dateFrom || ''}
              dateTo={filters.dateTo || ''}
              onFromChange={(val) => setFilters(p => ({ ...p, dateFrom: val || undefined, page: 1 }))}
              onToChange={(val) => setFilters(p => ({ ...p, dateTo: val || undefined, page: 1 }))}
            />
            <div className="h-8 w-px hidden sm:block" style={{ background: 'var(--border-subtle)' }} />
            <ExportButton onExport={handleExport} isExporting={isExporting} />
          </div>
        </div>

        <DataTableBulkActions
          count={selectedUuids.length}
          onDelete={handleBulkDelete}
          isDeleting={isDeleting}
        />

        <div className="overflow-hidden rounded-xl" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}>
          {loading ? (
             <p className="p-8 text-center" style={{ color: 'var(--text-muted)' }}>Loading...</p>
          ) : (
            <p className="p-8 text-center" style={{ color: 'var(--text-muted)' }}>Implement ${SINGULAR}Table component here.</p>
          )}
        </div>
      </div>

      <DeleteConfirmModal
        open={pendingDelete !== null}
        entityLabel={pendingDelete?.name ?? ''}
        onConfirm={handleConfirmSingleDelete}
        onCancel={() => setPendingDelete(null)}
        isDeleting={isDeleting}
      />
    </AppLayout>
  );
}
PAGE_EOF

cat > "${FE_PAGES}/${SINGULAR}CreatePage.tsx" << CPAGE_EOF
import * as React from 'react';
import AppLayout from '@/Pages/layouts/AppLayout';

export default function ${SINGULAR}CreatePage(): React.JSX.Element {
  return (
    <AppLayout>
      <h1 style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
        Create ${SINGULAR}
      </h1>
      <p style={{ color: 'var(--text-muted)' }}>Create page scaffold — customize this page.</p>
    </AppLayout>
  );
}
CPAGE_EOF

cat > "${FE_PAGES}/${SINGULAR}ShowPage.tsx" << SPAGE_EOF
import * as React from 'react';
import AppLayout from '@/Pages/layouts/AppLayout';

export default function ${SINGULAR}ShowPage(): React.JSX.Element {
  return (
    <AppLayout>
      <h1 style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
        ${SINGULAR} Details
      </h1>
      <p style={{ color: 'var(--text-muted)' }}>Show page scaffold — customize this page.</p>
    </AppLayout>
  );
}
SPAGE_EOF

cat > "${FE_PAGES}/${SINGULAR}EditPage.tsx" << EPAGE_EOF
import * as React from 'react';
import AppLayout from '@/Pages/layouts/AppLayout';

export default function ${SINGULAR}EditPage(): React.JSX.Element {
  return (
    <AppLayout>
      <h1 style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
        Edit ${SINGULAR}
      </h1>
      <p style={{ color: 'var(--text-muted)' }}>Edit page scaffold — customize this page.</p>
    </AppLayout>
  );
}
EPAGE_EOF

echo ""
echo "✅ CRUD module '${MODULE_UPPER}' generated successfully!"
echo ""
echo "📋 Next steps:"
echo "   1. Register the provider in bootstrap/providers.php:"
echo "      Src\\Contexts\\${MODULE_UPPER}\\Providers\\${MODULE_UPPER}ServiceProvider::class,"
echo ""
echo "   2. Add routes to routes/web.php (Inertia pages + API)"
echo ""
echo "   3. Run migration:"
echo "      ./vendor/bin/sail artisan migrate"
echo ""
echo "   4. Seed permissions:"
echo "      ./vendor/bin/sail artisan db:seed --class=Src\\\\Contexts\\\\${MODULE_UPPER}\\\\Infrastructure\\\\Persistence\\\\Eloquent\\\\Seeders\\\\${MODULE_UPPER}PermissionsSeeder"
echo ""
echo "   5. Customize the generated pages in resources/js/Pages/${MODULE_SNAKE}/"
echo ""
