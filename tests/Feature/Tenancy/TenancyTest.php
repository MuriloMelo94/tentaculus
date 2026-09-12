<?php

use App\Actions\Teams\CreateTeam;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('creating a team via factory does not provision a tenant database', function () {
    $team = Team::factory()->create();

    expect($team->tenant)->toBeNull();
});

test('creating a team via http provisions a tenant database', function () {
    $user = User::factory()->withoutTeam()->create();

    $this
        ->actingAs($user)
        ->post(route('teams.store'), [
            'name' => 'Joyjoy Cafe',
        ])
        ->assertRedirect();

    $team = $user->fresh()->currentTeam;

    expect($team)->not->toBeNull();
    expect($team->tenant)->not->toBeNull();
    expect(tenantDatabaseExists($team))->toBeTrue();
});

test('two teams use isolated tenant databases', function () {
    $createTeam = app(CreateTeam::class);

    $ownerA = User::factory()->withoutTeam()->create();
    $ownerB = User::factory()->withoutTeam()->create();

    $teamA = $createTeam->handle($ownerA, 'Alpha Luderia');
    $teamB = $createTeam->handle($ownerB, 'Beta Luderia');

    $databaseA = $teamA->tenant->database()->getName();
    $databaseB = $teamB->tenant->database()->getName();

    expect($databaseA)->not->toBe($databaseB);

    tenancy()->initialize($teamA->tenant);
    Schema::create('isolation_probe', function (Blueprint $table) {
        $table->id();
    });
    expect(Schema::hasTable('isolation_probe'))->toBeTrue();

    tenancy()->initialize($teamB->tenant);
    expect(Schema::hasTable('isolation_probe'))->toBeFalse();

    tenancy()->end();
});

test('deleting a team removes the tenant database', function () {
    $user = User::factory()->withoutTeam()->create();
    $team = app(CreateTeam::class)->handle($user, 'Doomed Luderia');
    $tenant = $team->tenant;
    $databaseName = $tenant->database()->getName();
    $manager = $tenant->database()->manager();

    expect($manager->databaseExists($databaseName))->toBeTrue();

    $this
        ->actingAs($user)
        ->delete(route('teams.destroy', $team), [
            'name' => $team->name,
        ])
        ->assertRedirect(route('teams.index'));

    $this->assertSoftDeleted($team);

    expect(Tenant::query()->whereKey($tenant->id)->exists())->toBeFalse();
    expect($manager->databaseExists($databaseName))->toBeFalse();
});

test('the current team dashboard initializes that team tenant', function () {
    $user = User::factory()->withoutTeam()->create();
    $team = app(CreateTeam::class)->handle($user, 'Joyjoy Cafe');

    $this
        ->actingAs($user)
        ->get(route('dashboard', ['current_team' => $team->slug]))
        ->assertOk();
});

test('a member of one team cannot visit another team dashboard', function () {
    $createTeam = app(CreateTeam::class);

    $ownerA = User::factory()->withoutTeam()->create();
    $ownerB = User::factory()->withoutTeam()->create();

    $teamA = $createTeam->handle($ownerA, 'Alpha Luderia');
    $teamB = $createTeam->handle($ownerB, 'Beta Luderia');

    $this
        ->actingAs($ownerA)
        ->get(route('dashboard', ['current_team' => $teamB->slug]))
        ->assertForbidden();

    $this
        ->actingAs($ownerA)
        ->get(route('dashboard', ['current_team' => $teamA->slug]))
        ->assertOk();
});

test('settings and auth stay on the central connection', function () {
    $user = User::factory()->withoutTeam()->create();
    $team = app(CreateTeam::class)->handle($user, 'Joyjoy Cafe');

    tenancy()->initialize($team->tenant);

    expect(DB::getDefaultConnection())->toBe('tenant');
    expect($user->fresh()->email)->toBe($user->email);
    expect(Team::query()->whereKey($team->id)->exists())->toBeTrue();

    tenancy()->end();

    $this
        ->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk();

    $this
        ->actingAs($user)
        ->get(route('teams.index'))
        ->assertOk();
});

test('the provision command creates tenants for teams without one', function () {
    $team = Team::factory()->create();

    expect($team->tenant)->toBeNull();

    $this->artisan('teams:provision-tenants')
        ->assertSuccessful();

    $team->refresh();

    expect($team->tenant)->not->toBeNull();
    expect(tenantDatabaseExists($team))->toBeTrue();
});

test('the provision command creates a missing database when the tenant row already exists', function () {
    $team = Team::factory()->create();

    Tenant::withoutEvents(function () use ($team): void {
        Tenant::create([
            'id' => (string) $team->id,
            'team_id' => $team->id,
        ]);
    });

    $team->refresh();

    expect($team->tenant)->not->toBeNull();
    expect(tenantDatabaseExists($team))->toBeFalse();

    $this->artisan('teams:provision-tenants')
        ->assertSuccessful();

    expect(tenantDatabaseExists($team->fresh()))->toBeTrue();
});
