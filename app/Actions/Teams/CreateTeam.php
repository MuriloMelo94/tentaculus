<?php

namespace App\Actions\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTeam
{
    public function __construct(private ProvisionTeamTenant $provisionTeamTenant) {}

    /**
     * Create a new team and add the user as owner.
     */
    public function handle(User $user, string $name): Team
    {
        $team = DB::transaction(function () use ($user, $name) {
            $team = Team::create([
                'name' => $name,
            ]);

            $team->memberships()->create([
                'user_id' => $user->id,
                'role' => TeamRole::Owner,
            ]);

            $user->switchTeam($team);

            return $team;
        });

        try {
            $this->provisionTeamTenant->handle($team);
        } catch (Throwable $exception) {
            $user->clearCurrentTeam();
            $team->unsetRelation('tenant');
            $team->tenant?->delete();
            $team->memberships()->delete();
            $team->forceDelete();

            throw $exception;
        }

        return $team;
    }
}
