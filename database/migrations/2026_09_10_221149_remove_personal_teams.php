<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove personal teams so they are not treated as luderias.
     *
     * This data backfill is intentionally irreversible.
     */
    public function up(): void
    {
        $personalTeamIds = DB::table('teams')
            ->where('is_personal', true)
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($personalTeamIds->isEmpty()) {
            return;
        }

        $userIds = DB::table('users')
            ->whereIn('current_team_id', $personalTeamIds)
            ->pluck('id');

        foreach ($userIds as $userId) {
            $fallbackTeamId = DB::table('team_members')
                ->join('teams', 'teams.id', '=', 'team_members.team_id')
                ->where('team_members.user_id', $userId)
                ->whereNotIn('team_members.team_id', $personalTeamIds)
                ->whereNull('teams.deleted_at')
                ->orderByRaw('LOWER(teams.name)')
                ->value('teams.id');

            DB::table('users')
                ->where('id', $userId)
                ->update(['current_team_id' => $fallbackTeamId]);
        }

        DB::table('team_invitations')->whereIn('team_id', $personalTeamIds)->delete();
        DB::table('team_members')->whereIn('team_id', $personalTeamIds)->delete();
        DB::table('teams')->whereIn('id', $personalTeamIds)->update(['deleted_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Personal teams were removed and cannot be restored.
    }
};
