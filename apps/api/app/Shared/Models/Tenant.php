<?php

namespace App\Shared\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * El tenant es el negocio (ej. la tienda de skincare del piloto) que opera
 * sobre la plataforma. Ver docs/business/diccionario-del-negocio.md:
 * tenant/negocio != cliente != emprendedor.
 *
 * Este modelo NO usa BelongsToTenant (no tiene sentido que un tenant
 * pertenezca a sí mismo).
 */
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
    ];

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }
}
