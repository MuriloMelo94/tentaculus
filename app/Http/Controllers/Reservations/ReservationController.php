<?php

namespace App\Http\Controllers\Reservations;

use App\Http\Controllers\Concerns\ResolvesCurrentLuderia;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    use ResolvesCurrentLuderia;

    /**
     * Show the reservation form for a luderia.
     */
    public function create(): Response
    {
        $team = $this->luderia();

        return Inertia::render('reservations/Create', [
            'team' => [
                'name' => $team->name,
                'slug' => $team->slug,
            ],
        ]);
    }
}
