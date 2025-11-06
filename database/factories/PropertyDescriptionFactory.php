<?php

namespace Database\Factories;

use App\Models\PropertyDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PropertyDescription>
 */
class PropertyDescriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PropertyDescription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $propertyTypes = ['House', 'Flat', 'Land', 'Commercial'];
        $tones = ['formal', 'casual'];
        $locations = [
            'Lekki Phase 1, Lagos',
            'Victoria Island, Lagos',
            'Ikoyi, Lagos',
            'Banana Island, Lagos',
            'Maitama, Abuja',
            'Asokoro, Abuja',
            'GRA, Port Harcourt',
        ];

        return [
            'title' => fake()->randomElement([
                'Luxury 5-Bedroom Duplex',
                'Modern 3-Bedroom Apartment',
                'Executive 4-Bedroom Detached House',
                'Prime Commercial Plot',
                'Serviced 2-Bedroom Flat',
                'Spacious 6-Bedroom Mansion',
            ]),
            'property_type' => fake()->randomElement($propertyTypes),
            'location' => fake()->randomElement($locations),
            'price' => fake()->randomFloat(2, 10000000, 500000000),
            'key_features' => fake()->randomElement([
                'Swimming pool, 24/7 security, BQ, fitted kitchen, ample parking',
                'Gym, elevator, balcony, study room, walk-in closet',
                'Garden, garage, solar panels, smart home system',
                'Sea view, terrace, jacuzzi, home cinema',
            ]),
            'tone' => fake()->randomElement($tones),
            'generated_description' => fake()->paragraph(5),
            'readability_score' => fake()->numberBetween(40, 95),
            'seo_score' => fake()->numberBetween(50, 98),
            'overall_score' => fake()->numberBetween(45, 95),
            'word_count' => fake()->numberBetween(100, 300),
            'character_count' => fake()->numberBetween(600, 1800),
            'sentence_count' => fake()->numberBetween(5, 15),
            'average_sentence_length' => fake()->randomFloat(1, 10, 25),
            'keyword_mentions' => fake()->numberBetween(2, 8),
        ];
    }

    /**
     * Indicate that the property has high quality scores.
     */
    public function highQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'readability_score' => fake()->numberBetween(80, 95),
            'seo_score' => fake()->numberBetween(80, 98),
            'overall_score' => fake()->numberBetween(80, 95),
        ]);
    }

    /**
     * Indicate that the property has low quality scores.
     */
    public function lowQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'readability_score' => fake()->numberBetween(20, 50),
            'seo_score' => fake()->numberBetween(30, 55),
            'overall_score' => fake()->numberBetween(25, 50),
        ]);
    }

    public function house(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_type' => 'House',
            'title' => 'Luxury Detached House',
        ]);
    }

    public function flat(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_type' => 'Flat',
            'title' => 'Modern Apartment',
        ]);
    }

    public function formal(): static
    {
        return $this->state(fn (array $attributes) => [
            'tone' => 'formal',
        ]);
    }

    public function casual(): static
    {
        return $this->state(fn (array $attributes) => [
            'tone' => 'casual',
        ]);
    }
}
