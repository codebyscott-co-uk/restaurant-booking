<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function currentVenue(?Request $request = null): Venue
    {
        $user = ($request ?: request())->user();

        abort_unless($user && $user->venue_id, 403);

        return $user->venue()->firstOrFail();
    }

    protected function ensureVenue(Model $model, Venue $venue): void
    {
        abort_unless((int) $model->getAttribute('venue_id') === (int) $venue->id, 404);
    }
}
