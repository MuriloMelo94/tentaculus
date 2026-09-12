<?php

use App\Enums\TeamPermission;
use App\Enums\TeamRole;

test('owners and admins can manage tables and reservations', function (TeamRole $role) {
    expect($role->hasPermission(TeamPermission::ManageTables))->toBeTrue();
    expect($role->hasPermission(TeamPermission::ManageReservations))->toBeTrue();
})->with([
    TeamRole::Owner,
    TeamRole::Admin,
]);

test('members cannot manage tables or reservations', function () {
    expect(TeamRole::Member->hasPermission(TeamPermission::ManageTables))->toBeFalse();
    expect(TeamRole::Member->hasPermission(TeamPermission::ManageReservations))->toBeFalse();
});

test('all team roles can check in and view history', function (TeamRole $role) {
    expect($role->hasPermission(TeamPermission::CheckInReservations))->toBeTrue();
    expect($role->hasPermission(TeamPermission::ViewHistory))->toBeTrue();
})->with(TeamRole::cases());
