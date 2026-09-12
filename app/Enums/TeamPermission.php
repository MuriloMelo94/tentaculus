<?php

namespace App\Enums;

enum TeamPermission: string
{
    case UpdateTeam = 'team:update';
    case DeleteTeam = 'team:delete';

    case AddMember = 'member:add';
    case UpdateMember = 'member:update';
    case RemoveMember = 'member:remove';

    case CreateInvitation = 'invitation:create';
    case CancelInvitation = 'invitation:cancel';

    case ManageTables = 'tables:manage';
    case ManageReservations = 'reservations:manage';
    case CheckInReservations = 'reservations:check-in';
    case ViewHistory = 'history:view';
}
