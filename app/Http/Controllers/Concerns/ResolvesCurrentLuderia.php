<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Team;
use App\Models\Tenant;

trait ResolvesCurrentLuderia
{
    /**
     * Resolve the luderia for the initialized tenant.
     */
    protected function luderia(): Team
    {
        $tenant = tenancy()->tenant;

        abort_if(! $tenant instanceof Tenant, 404);

        return $tenant->team;
    }
}
