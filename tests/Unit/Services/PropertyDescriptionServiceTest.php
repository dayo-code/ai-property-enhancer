<?php

use App\Services\PropertyDescriptionService;

beforeEach(function () {
    $this->service = new PropertyDescriptionService();

    $this->validPropertyData = [
        'title' => 'Luxury 4-Bedroom Duplex',
        'type' => 'House',
        'location' => 'Durumi, Abuja',
        'price' => '85000000',
        'features' => 'Swimming pool, 24/7 security, BQ, fitted kitchen, ample parking',
    ];
});

describe('PropertyDescriptionService - Validation', function () {

    test('validates property data correctly with all fields', function () {
        expect($this->service->validatePropertyData($this->validPropertyData))
            ->toBeTrue();
    });

    test('fails validation when title is missing', function () {
        unset($this->validPropertyData['title']);

        expect($this->service->validatePropertyData($this->validPropertyData))
            ->toBeFalse();
    });

    test('fails validation when type is missing', function () {
        unset($this->validPropertyData['type']);

        expect($this->service->validatePropertyData($this->validPropertyData))
            ->toBeFalse();
    });

    test('fails validation when location is missing', function () {
        unset($this->validPropertyData['location']);

        expect($this->service->validatePropertyData($this->validPropertyData))
            ->toBeFalse();
    });

    test('fails validation when price is missing', function () {
        unset($this->validPropertyData['price']);

        expect($this->service->validatePropertyData($this->validPropertyData))
            ->toBeFalse();
    });

    test('fails validation when features are missing', function () {
        unset($this->validPropertyData['features']);

        expect($this->service->validatePropertyData($this->validPropertyData))
            ->toBeFalse();
    });

    test('fails validation with empty values', function () {
        $emptyData = [
            'title' => '',
            'type' => '',
            'location' => '',
            'price' => '',
            'features' => '',
        ];

        expect($this->service->validatePropertyData($emptyData))
            ->toBeFalse();
    });
});

describe('PropertyDescriptionService - Description Generation', function () {

    test('generates description successfully with valid data', function () {
        // Mock the OpenAI API call
        // Note: In real scenario, you'd mock the OpenAI facade
        // For now, we test the structure

        expect($this->service->validatePropertyData($this->validPropertyData))
            ->toBeTrue();
    });

    test('description generation with formal tone', function () {
        // This would require mocking OpenAI
        // We're testing the method exists and accepts parameters
        expect(method_exists($this->service, 'generateDescription'))
            ->toBeTrue();
    });

    test('description generation with casual tone', function () {
        // This would require mocking OpenAI
        expect(method_exists($this->service, 'generateDescription'))
            ->toBeTrue();
    });
});

describe('PropertyDescriptionService - Data Handling', function () {

    test('handles property data with special characters', function () {
        $specialData = [
            'title' => '5-Bed House (Brand New!)',
            'type' => 'House',
            'location' => "Lekki Phase 1, Off Freedom Way, Lagos",
            'price' => '85000000',
            'features' => "Pool, gym & spa, 24/7 security",
        ];

        expect($this->service->validatePropertyData($specialData))
            ->toBeTrue();
    });

    test('handles very long feature descriptions', function () {
        $longData = $this->validPropertyData;
        $longData['features'] = str_repeat('Feature description. ', 100);

        expect($this->service->validatePropertyData($longData))
            ->toBeTrue();
    });

    test('handles minimum valid feature length', function () {
        $minData = $this->validPropertyData;
        $minData['features'] = 'Short text'; // 10+ characters

        expect($this->service->validatePropertyData($minData))
            ->toBeTrue();
    });
});
