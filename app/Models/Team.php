<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use App\Observers\TeamObserver;
use Carbon\CarbonInterface;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property string $uuid
 * @property-read int $organization_id
 * @property-read int $season_id
 * @property-read int $division_id
 * @property-read string $name
 * @property-read string $slug
 * @property-read int $head_coach_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
#[ObservedBy(TeamObserver::class)]
final class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use BelongsToOrganization, HasFactory;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'uuid' => 'string',
            'organization_id' => 'integer',
            'season_id' => 'integer',
            'division_id' => 'integer',
            'name' => 'string',
            'slug' => 'string',
            'head_coach_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<Season, $this>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * @return BelongsTo<Division, $this>
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function headCoach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_coach_id');
    }

    protected static function booted(): void
    {
        self::addGlobalScope('current_season', static function (Builder $builder): void {
            $currentSeasonId = session('current_season_id');

            if (is_numeric($currentSeasonId)) {
                $builder->where('season_id', (int) $currentSeasonId);
            }
        });
    }
}
