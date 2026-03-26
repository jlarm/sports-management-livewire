<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Context;

trait BelongsToOrganization
{
    /**
     * Boot the trait and add global scope.
     */
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $query): void {
            $organization = Context::get('organization');

            if ($organization) {
                $query->where('organization_id', $organization->id);
            }
        });
    }
}
