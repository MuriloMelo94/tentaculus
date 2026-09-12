<?php

use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view a luderia landing', function () {
    $team = Team::factory()->create(['name' => 'JoyJoy Cafe']);
    provisionTenant($team);

    $response = $this->get(route('landing', ['current_team' => $team->slug]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('landing/Show')
        ->where('team.name', 'JoyJoy Cafe')
        ->where('team.slug', $team->slug),
    );
});

test('landing is not found for an unknown slug', function () {
    $this->get('/missing-luderia')->assertNotFound();
});

test('landing is not found for a team without a tenant', function () {
    $team = Team::factory()->create();

    $this->get(route('landing', ['current_team' => $team->slug]))->assertNotFound();
});

test('landing is not found for a soft deleted team', function () {
    $team = Team::factory()->create();
    provisionTenant($team);
    $team->delete();

    $this->get(route('landing', ['current_team' => $team->slug]))->assertNotFound();
});

test('users without a team can view a luderia landing', function () {
    $team = Team::factory()->create(['name' => 'JoyJoy Cafe']);
    provisionTenant($team);

    $user = User::factory()->withoutTeam()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('landing', ['current_team' => $team->slug]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('landing/Show')
        ->where('team.name', 'JoyJoy Cafe'),
    );
});

test('members of another team can view a luderia landing', function () {
    $visitor = User::factory()->create();
    $luderia = Team::factory()->create(['name' => 'JoyJoy Cafe']);
    provisionTenant($visitor->currentTeam);
    provisionTenant($luderia);

    $response = $this
        ->actingAs($visitor)
        ->get(route('landing', ['current_team' => $luderia->slug]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('landing/Show')
        ->where('team.name', 'JoyJoy Cafe'),
    );
});

test('guests starting a reservation are redirected to login and return afterwards', function () {
    $team = Team::factory()->create(['name' => 'JoyJoy Cafe']);
    provisionTenant($team);

    $user = User::factory()->withoutTeam()->create();

    $this->get(route('reservations.create', ['current_team' => $team->slug]))
        ->assertRedirect(route('login'));

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('reservations.create', ['current_team' => $team->slug]));
});

test('users without a team can start a reservation on a luderia', function () {
    $team = Team::factory()->create(['name' => 'JoyJoy Cafe']);
    provisionTenant($team);

    $user = User::factory()->withoutTeam()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('reservations.create', ['current_team' => $team->slug]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('reservations/Create')
        ->where('team.name', 'JoyJoy Cafe')
        ->where('team.slug', $team->slug),
    );
});

test('home remains the generic welcome page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Welcome'));
});

test('dashboard still requires team membership', function () {
    $luderia = Team::factory()->create();
    provisionTenant($luderia);

    $visitor = User::factory()->create();
    provisionTenant($visitor->currentTeam);

    $this
        ->actingAs($visitor)
        ->get(route('dashboard', ['current_team' => $luderia->slug]))
        ->assertForbidden();
});
