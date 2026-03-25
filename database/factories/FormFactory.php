<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Form;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Form>
 */
final class FormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'organization_id' => Organization::factory(),
            'name' => $this->faker->word(),
            'status' => $this->faker->randomElement(Status::cases()),
            'schema' => [
                'fields' => [
                    [
                        'id' => Str::uuid()->toString(),
                        'type' => 'text',
                        'label' => $this->faker->words(3, true),
                        'required' => $this->faker->boolean(),
                    ],
                    [
                        'id' => Str::uuid()->toString(),
                        'type' => 'email',
                        'label' => 'Email Address',
                        'required' => true,
                    ],
                ],
            ],
            'price' => $this->faker->numberBetween(1, 10000),
        ];
    }
}
