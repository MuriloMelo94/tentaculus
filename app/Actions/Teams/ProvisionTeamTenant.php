<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\Tenant;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class ProvisionTeamTenant
{
    /**
     * Create the Stancl tenant (and database) for a team if it does not exist yet.
     */
    public function handle(Team $team): Tenant
    {
        $tenant = $team->tenant()->first();

        if ($tenant === null) {
            $tenant = Tenant::create([
                'id' => (string) $team->id,
                'team_id' => $team->id,
            ]);
        } else {
            $this->ensureDatabaseExists($tenant);
        }

        $team->setRelation('tenant', $tenant);

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        return $tenant;
    }

    /**
     * Finish provisioning when the tenant row exists but CREATE DATABASE failed.
     */
    protected function ensureDatabaseExists(Tenant $tenant): void
    {
        $name = $tenant->database()->getName();

        if ($tenant->database()->manager()->databaseExists($name)) {
            return;
        }

        CreateDatabase::dispatchSync($tenant);
        MigrateDatabase::dispatchSync($tenant);
    }
}
