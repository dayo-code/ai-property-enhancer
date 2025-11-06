<?php

use App\Models\PropertyDescription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->propertyData = [
        'title' => 'Luxury 5-Bedroom Duplex',
        'property_type' => 'House',
        'location' => 'Lekki Phase 1, Lagos',
        'price' => 85000000,
        'key_features' => 'Swimming pool, 24/7 security, BQ, fitted kitchen',
        'tone' => 'formal',
        'generated_description' => 'This is a beautiful property in Lekki Phase 1. It features a swimming pool and 24/7 security. Perfect for families looking for comfort and security in Lagos.',
        'readability_score' => 75,
        'seo_score' => 82,
        'overall_score' => 79,
        'word_count' => 32,
        'character_count' => 180,
        'sentence_count' => 3,
        'average_sentence_length' => 10.7,
        'keyword_mentions' => 4,
    ];
});

describe('PropertyDescription Model - Creation', function () {

    test('can create property description', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property)->toBeInstanceOf(PropertyDescription::class)
            ->and($property->title)->toBe('Luxury 5-Bedroom Duplex')
            ->and($property->property_type)->toBe('House')
            ->and($property->exists)->toBeTrue();
    });

    test('all fields are fillable', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property->title)->toBe($this->propertyData['title'])
            ->and($property->property_type)->toBe($this->propertyData['property_type'])
            ->and($property->location)->toBe($this->propertyData['location'])
            ->and($property->price)->toBe($this->propertyData['price'])
            ->and($property->tone)->toBe($this->propertyData['tone'])
            ->and($property->readability_score)->toBe($this->propertyData['readability_score'])
            ->and($property->seo_score)->toBe($this->propertyData['seo_score'])
            ->and($property->overall_score)->toBe($this->propertyData['overall_score']);
    });

    test('timestamps are automatically set', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property->created_at)->not->toBeNull()
            ->and($property->updated_at)->not->toBeNull();
    });
});

describe('PropertyDescription Model - Scopes', function () {

    test('recent scope returns latest entries first', function () {
        // Create 3 properties
        PropertyDescription::create($this->propertyData);
        sleep(1);
        PropertyDescription::create(array_merge($this->propertyData, ['title' => 'Second Property']));
        sleep(1);
        PropertyDescription::create(array_merge($this->propertyData, ['title' => 'Third Property']));

        $recent = PropertyDescription::recent(2)->get();

        expect($recent)->toHaveCount(2)
            ->and($recent->first()->title)->toBe('Third Property')
            ->and($recent->last()->title)->toBe('Second Property');
    });

    test('byType scope filters by property type', function () {
        PropertyDescription::create($this->propertyData);
        PropertyDescription::create(array_merge($this->propertyData, ['property_type' => 'Flat']));
        PropertyDescription::create(array_merge($this->propertyData, ['property_type' => 'Flat']));

        $houses = PropertyDescription::byType('House')->get();
        $flats = PropertyDescription::byType('Flat')->get();

        expect($houses)->toHaveCount(1)
            ->and($flats)->toHaveCount(2);
    });

    test('byTone scope filters by tone', function () {
        PropertyDescription::create($this->propertyData);
        PropertyDescription::create(array_merge($this->propertyData, ['tone' => 'casual']));
        PropertyDescription::create(array_merge($this->propertyData, ['tone' => 'casual']));

        $formal = PropertyDescription::byTone('formal')->get();
        $casual = PropertyDescription::byTone('casual')->get();

        expect($formal)->toHaveCount(1)
            ->and($casual)->toHaveCount(2);
    });

    test('highQuality scope returns only high-scoring entries', function () {
        PropertyDescription::create(array_merge($this->propertyData, ['overall_score' => 90]));
        PropertyDescription::create(array_merge($this->propertyData, ['overall_score' => 75]));
        PropertyDescription::create(array_merge($this->propertyData, ['overall_score' => 50]));

        $highQuality = PropertyDescription::highQuality(70)->get();

        expect($highQuality)->toHaveCount(2);
    });

    test('scopes can be chained', function () {
        PropertyDescription::create(array_merge($this->propertyData, [
            'property_type' => 'House',
            'overall_score' => 85,
        ]));
        PropertyDescription::create(array_merge($this->propertyData, [
            'property_type' => 'House',
            'overall_score' => 65,
        ]));
        PropertyDescription::create(array_merge($this->propertyData, [
            'property_type' => 'Flat',
            'overall_score' => 90,
        ]));

        $result = PropertyDescription::byType('House')->highQuality(70)->get();

        expect($result)->toHaveCount(1)
            ->and($result->first()->overall_score)->toBe(85);
    });
});

describe('PropertyDescription Model - Accessors', function () {

    test('formatted price accessor returns naira format', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property->formatted_price)->toContain('₦')
            ->and($property->formatted_price)->toContain('85,000,000');
    });

    test('short description accessor truncates long text', function () {
        $longDescription = str_repeat('This is a long description. ', 20);
        $property = PropertyDescription::create(array_merge(
            $this->propertyData,
            ['generated_description' => $longDescription]
        ));

        expect(strlen($property->short_description))->toBeLessThanOrEqual(103) // 100 + '...'
            ->and($property->short_description)->toEndWith('...');
    });

    test('short description accessor does not truncate short text', function () {
        $shortDescription = 'Short text.';
        $property = PropertyDescription::create(array_merge(
            $this->propertyData,
            ['generated_description' => $shortDescription]
        ));

        expect($property->short_description)->toBe($shortDescription)
            ->and($property->short_description)->not->toEndWith('...');
    });

    test('time ago accessor returns human readable time', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property->time_ago)->toContain('ago')
            ->or($property->time_ago)->toContain('second');
    });

    test('score badge color accessor returns correct colors', function () {
        $excellent = PropertyDescription::create(array_merge($this->propertyData, ['overall_score' => 85]));
        $good = PropertyDescription::create(array_merge($this->propertyData, ['overall_score' => 70]));
        $fair = PropertyDescription::create(array_merge($this->propertyData, ['overall_score' => 50]));
        $poor = PropertyDescription::create(array_merge($this->propertyData, ['overall_score' => 30]));

        expect($excellent->score_badge_color)->toBe('green')
            ->and($good->score_badge_color)->toBe('blue')
            ->and($fair->score_badge_color)->toBe('yellow')
            ->and($poor->score_badge_color)->toBe('red');
    });
});

describe('PropertyDescription Model - Casts', function () {

    test('price is cast to decimal', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property->price)->toBeFloat();
    });

    test('scores are cast to integers', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property->readability_score)->toBeInt()
            ->and($property->seo_score)->toBeInt()
            ->and($property->overall_score)->toBeInt();
    });

    test('timestamps are cast to datetime', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
            ->and($property->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });

    test('average sentence length is cast to decimal', function () {
        $property = PropertyDescription::create($this->propertyData);

        expect($property->average_sentence_length)->toBeFloat();
    });
});

describe('PropertyDescription Model - Database Operations', function () {

    test('can update property description', function () {
        $property = PropertyDescription::create($this->propertyData);

        $property->update(['title' => 'Updated Title']);

        expect($property->fresh()->title)->toBe('Updated Title');
    });

    test('can delete property description', function () {
        $property = PropertyDescription::create($this->propertyData);
        $id = $property->id;

        $property->delete();

        expect(PropertyDescription::find($id))->toBeNull();
    });

    test('can count property descriptions', function () {
        PropertyDescription::create($this->propertyData);
        PropertyDescription::create($this->propertyData);
        PropertyDescription::create($this->propertyData);

        expect(PropertyDescription::count())->toBe(3);
    });
});

describe('PropertyDescription Model - Edge Cases', function () {

    test('handles null scores gracefully', function () {
        $property = PropertyDescription::create(array_merge(
            $this->propertyData,
            [
                'readability_score' => null,
                'seo_score' => null,
                'overall_score' => null,
            ]
        ));

        expect($property->readability_score)->toBeNull()
            ->and($property->seo_score)->toBeNull()
            ->and($property->overall_score)->toBeNull();
    });

    test('handles very large prices', function () {
        $property = PropertyDescription::create(array_merge(
            $this->propertyData,
            ['price' => 999999999.99]
        ));

        expect($property->price)->toBe(999999999.99);
    });

    test('handles special characters in text fields', function () {
        $property = PropertyDescription::create(array_merge(
            $this->propertyData,
            [
                'title' => '5-Bed House @ Lekki!',
                'location' => "Off Freedom Way, Lekki Phase 1",
                'key_features' => "Pool & Gym, 24/7 Security",
            ]
        ));

        expect($property->title)->toBe('5-Bed House @ Lekki!')
            ->and($property->location)->toContain('Freedom Way')
            ->and($property->key_features)->toContain('&');
    });
});
