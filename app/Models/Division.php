<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Carbon\CarbonInterface;
use Database\Factories\DivisionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property-read int $id
 * @property-read string $uuid
 * @property-read int $organization_id
 * @property-read string $name
 * @property-read int $display_order
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Division extends Model
{
    /** @use HasFactory<DivisionFactory> */
    use BelongsToOrganization, HasFactory;

    public static function canonicalName(string $value): string
    {
        $trimmedValue = Str::of($value)
            ->trim()
            ->replaceMatches('/\s+/', ' ')
            ->toString();

        $ageDivision = Str::of($trimmedValue)
            ->upper()
            ->replace(' ', '')
            ->toString();

        if (preg_match('/^\d+U?$/', $ageDivision) === 1) {
            return mb_rtrim($ageDivision, 'U').'U';
        }

        return $trimmedValue;
    }

    public function casts(): array
    {
        return [
            'id' => 'integer',
            'uuid' => 'string',
            'organization_id' => 'integer',
            'name' => 'string',
            'display_order' => 'integer',
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
     * @return HasMany<Team, $this>
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value): string => self::canonicalName($value),
            set: fn (string $value): string => self::canonicalName($value),
        );
    }
}
