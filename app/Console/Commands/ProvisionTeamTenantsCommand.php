<?php

namespace App\Console\Commands;

use App\Actions\Teams\ProvisionTeamTenant;
use App\Models\Team;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('teams:provision-tenants')]
#[Description('Create tenant databases for teams that do not have one')]
class ProvisionTeamTenantsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ProvisionTeamTenant $provisionTeamTenant): int
    {
        $teams = Team::query()->with('tenant')->get();

        $pending = $teams->filter(fn (Team $team) => $this->needsProvisioning($team));

        if ($pending->isEmpty()) {
            $this->info('All teams already have a tenant database.');

            return self::SUCCESS;
        }

        $pending->each(function (Team $team) use ($provisionTeamTenant): void {
            $provisionTeamTenant->handle($team);

            $this->info("Provisioned tenant database for team {$team->slug}.");
        });

        return self::SUCCESS;
    }

    /**
     * Determine whether the team is missing a tenant record or its database.
     */
    protected function needsProvisioning(Team $team): bool
    {
        $tenant = $team->tenant;

        if ($tenant === null) {
            return true;
        }

        return ! $tenant->database()->manager()->databaseExists($tenant->database()->getName());
    }
}
