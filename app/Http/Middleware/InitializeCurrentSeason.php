<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\Season;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

final class InitializeCurrentSeason
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $organization = Context::get('organization');

        if (! $organization instanceof Organization) {
            $request->session()->forget('current_season_id');

            return $next($request);
        }

        $currentSeasonId = $request->session()->get('current_season_id');

        if (is_numeric($currentSeasonId)) {
            $selectedSeasonExists = Season::query()
                ->whereBelongsTo($organization)
                ->whereKey((int) $currentSeasonId)
                ->exists();

            if ($selectedSeasonExists) {
                return $next($request);
            }
        }

        $activeSeasonId = Season::query()
            ->whereBelongsTo($organization)
            ->where('active', true)
            ->value('id');

        if (is_int($activeSeasonId)) {
            $request->session()->put('current_season_id', $activeSeasonId);
        } else {
            $request->session()->forget('current_season_id');
        }

        return $next($request);
    }
}
