<?php

namespace App\Shared\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtra automáticamente por tenant_id cuando hay un tenant resuelto en el
 * contenedor (ver App\Shared\Http\Middleware\ResolveTenant). Sin tenant
 * resuelto (comandos artisan, seeders, tests fuera de un request HTTP) no
 * filtra nada — es responsabilidad de quien llama especificar el tenant
 * explícitamente en ese contexto.
 *
 * Ver docs/architecture/base-de-datos.md, caso límite "Query que
 * accidentalmente omite el filtro de tenant_id": este scope existe
 * precisamente para que ese error no dependa de la disciplina manual.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->bound('currentTenantId')) {
            $builder->where($model->getTable().'.tenant_id', app('currentTenantId'));
        }
    }
}
