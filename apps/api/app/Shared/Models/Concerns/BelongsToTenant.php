<?php

namespace App\Shared\Models\Concerns;

use App\Shared\Models\Tenant;
use App\Shared\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Todo modelo de negocio (Categoria, Producto, Cliente, Pedido, ...) usa este
 * trait: aplica el TenantScope automático y asigna tenant_id al crear un
 * registro nuevo si hay un tenant resuelto en el contenedor.
 *
 * Ver docs/architecture/vision-tecnica.md (principio 3, multi-tenant desde el
 * modelo de datos) y docs/architecture/base-de-datos.md.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            if (is_null($model->tenant_id) && app()->bound('currentTenantId')) {
                $model->tenant_id = app('currentTenantId');
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
