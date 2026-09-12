<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCurrentLuderia;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    use ResolvesCurrentLuderia;

    /**
     * Show the public landing page for a luderia.
     */
    public function __invoke(): Response
    {
        $team = $this->luderia();

        return Inertia::render('landing/Show', [
            'team' => [
                'name' => $team->name,
                'slug' => $team->slug,
            ],
        ]);
    }
}
