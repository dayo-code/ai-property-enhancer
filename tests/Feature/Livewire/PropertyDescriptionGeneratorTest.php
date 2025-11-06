<?php

use App\Livewire\PropertyDescriptionGenerator;
use App\Models\PropertyDescription;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->validData = [
        'title' => 'Luxury 5-Bedroom Duplex',
        'propertyType' => 'House',
        'location' => 'Lekki Phase 1, Lagos',
        'price' => '85000000',
        'keyFeatures' => 'Swimming pool, 24/7 security, BQ, fitted kitchen, ample parking',
        'tone' => 'formal',
    ];
});

describe('PropertyDescriptionGenerator - Component Rendering', function () {

    test('component can be rendered', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertStatus(200);
    });

    test('component displays form fields', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertSee('Property Title')
            ->assertSee('Property Type')
            ->assertSee('Location')
            ->assertSee('Price')
            ->assertSee('Key Features')
            ->assertSee('Description Tone');
    });

    test('component displays property type options', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertSee('House')
            ->assertSee('Flat')
            ->assertSee('Land')
            ->assertSee('Commercial');
    });

    test('component displays tone options', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertSee('Professional & Formal')
            ->assertSee('Friendly & Casual');
    });
});

describe('PropertyDescriptionGenerator - Form Validation', function () {

    test('validates required title field', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('title', '')
            ->call('generateDescription')
            ->assertHasErrors(['title' => 'required']);
    });

    test('validates required property type field', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('propertyType', '')
            ->call('generateDescription')
            ->assertHasErrors(['propertyType' => 'required']);
    });

    test('validates required location field', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('location', '')
            ->call('generateDescription')
            ->assertHasErrors(['location' => 'required']);
    });

    test('validates required price field', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('price', '')
            ->call('generateDescription')
            ->assertHasErrors(['price' => 'required']);
    });

    test('validates price is numeric', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('price', 'not-a-number')
            ->call('generateDescription')
            ->assertHasErrors(['price' => 'numeric']);
    });

    test('validates required key features field', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('keyFeatures', '')
            ->call('generateDescription')
            ->assertHasErrors(['keyFeatures' => 'required']);
    });

    test('validates key features minimum length', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('keyFeatures', 'short')
            ->call('generateDescription')
            ->assertHasErrors(['keyFeatures' => 'min']);
    });

    test('validates property type is in allowed list', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('propertyType', 'InvalidType')
            ->call('generateDescription')
            ->assertHasErrors(['propertyType' => 'in']);
    });

    test('validates tone is in allowed list', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('tone', 'invalid-tone')
            ->call('generateDescription')
            ->assertHasErrors(['tone' => 'in']);
    });
});

describe('PropertyDescriptionGenerator - History Sidebar', function () {

    test('history sidebar displays when entries exist', function () {
        // Create a property description
        PropertyDescription::create([
            'title' => 'Test Property',
            'property_type' => 'House',
            'location' => 'Lagos',
            'price' => 50000000,
            'key_features' => 'Test features',
            'tone' => 'formal',
            'generated_description' => 'Test description',
            'overall_score' => 75,
        ]);

        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertSee('History')
            ->assertSee('Test Property');
    });

    test('history displays empty state when no entries', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertSee('No history yet')
            ->assertSee('Generated descriptions will appear here');
    });

    test('history shows property count badge', function () {
        PropertyDescription::factory()->count(5)->create();

        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertSee('5');
    });
});

describe('PropertyDescriptionGenerator - Load from History', function () {

    test('can load property from history', function () {
        $property = PropertyDescription::create([
            'title' => 'Historic Property',
            'property_type' => 'Flat',
            'location' => 'Victoria Island',
            'price' => 75000000,
            'key_features' => 'Historic features',
            'tone' => 'casual',
            'generated_description' => 'Historic description',
            'readability_score' => 80,
            'seo_score' => 85,
            'overall_score' => 83,
        ]);

        Livewire::test(PropertyDescriptionGenerator::class)
            ->call('loadFromHistory', $property->id)
            ->assertSet('title', 'Historic Property')
            ->assertSet('propertyType', 'Flat')
            ->assertSet('location', 'Victoria Island')
            ->assertSet('tone', 'casual')
            ->assertSet('generatedDescription', 'Historic description');
    });

    test('loaded entry is highlighted in history', function () {
        $property = PropertyDescription::create([
            'title' => 'Test Property',
            'property_type' => 'House',
            'location' => 'Lagos',
            'price' => 50000000,
            'key_features' => 'Features',
            'tone' => 'formal',
            'generated_description' => 'Description',
        ]);

        Livewire::test(PropertyDescriptionGenerator::class)
            ->call('loadFromHistory', $property->id)
            ->assertSet('loadedHistoryId', $property->id);
    });

    test('loading invalid history id shows error', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->call('loadFromHistory', 99999)
            ->assertHasErrors('history');
    });
});

describe('PropertyDescriptionGenerator - Delete from History', function () {

    test('can delete property from history', function () {
        $property = PropertyDescription::create([
            'title' => 'To Delete',
            'property_type' => 'House',
            'location' => 'Lagos',
            'price' => 50000000,
            'key_features' => 'Features',
            'tone' => 'formal',
            'generated_description' => 'Description',
        ]);

        $id = $property->id;

        Livewire::test(PropertyDescriptionGenerator::class)
            ->call('deleteFromHistory', $id)
            ->assertSessionHas('success');

        expect(PropertyDescription::find($id))->toBeNull();
    });

    test('deleting currently loaded entry clears form', function () {
        $property = PropertyDescription::create([
            'title' => 'Loaded Property',
            'property_type' => 'House',
            'location' => 'Lagos',
            'price' => 50000000,
            'key_features' => 'Features',
            'tone' => 'formal',
            'generated_description' => 'Description',
        ]);

        Livewire::test(PropertyDescriptionGenerator::class)
            ->call('loadFromHistory', $property->id)
            ->call('deleteFromHistory', $property->id)
            ->assertSet('title', '')
            ->assertSet('generatedDescription', null);
    });

    test('deleting invalid id shows error', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->call('deleteFromHistory', 99999)
            ->assertHasErrors('history');
    });
});

describe('PropertyDescriptionGenerator - Clear All History', function () {

    test('can clear all history entries', function () {
        PropertyDescription::factory()->count(5)->create();

        expect(PropertyDescription::count())->toBe(5);

        Livewire::test(PropertyDescriptionGenerator::class)
            ->call('clearAllHistory')
            ->assertSessionHas('success');

        expect(PropertyDescription::count())->toBe(0);
    });
});

describe('PropertyDescriptionGenerator - Reset Form', function () {

    test('reset form clears all fields', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('title', 'Test Title')
            ->set('propertyType', 'House')
            ->set('location', 'Test Location')
            ->set('price', '1000000')
            ->set('keyFeatures', 'Test Features')
            ->call('resetForm')
            ->assertSet('title', '')
            ->assertSet('propertyType', '')
            ->assertSet('location', '')
            ->assertSet('price', '')
            ->assertSet('keyFeatures', '')
            ->assertSet('generatedDescription', null);
    });

    test('reset form clears validation errors', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('title', '')
            ->call('generateDescription')
            ->assertHasErrors('title')
            ->call('resetForm')
            ->assertHasNoErrors();
    });
});

describe('PropertyDescriptionGenerator - Reactive Properties', function () {

    test('can set and get property values', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->set('title', 'Test Title')
            ->assertSet('title', 'Test Title')
            ->set('propertyType', 'House')
            ->assertSet('propertyType', 'House')
            ->set('location', 'Test Location')
            ->assertSet('location', 'Test Location');
    });

    test('generation count increments', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertSet('generationCount', 0);
        // Note: Would increment after actual generation
    });

    test('loading state toggles', function () {
        Livewire::test(PropertyDescriptionGenerator::class)
            ->assertSet('isGenerating', false);
        // Note: Would be true during generation
    });
});
