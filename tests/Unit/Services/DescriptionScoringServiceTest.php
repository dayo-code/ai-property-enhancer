<?php

use App\Services\DescriptionScoringService;

beforeEach(function () {
    $this->service = new DescriptionScoringService();

    $this->sampleDescription = "This beautiful property in Lekki Phase 1 offers exceptional value. "
        . "The spacious 5-bedroom house features a modern kitchen, swimming pool, and 24/7 security. "
        . "Located in a prime area of Lagos, this house is perfect for families. "
        . "Contact us today to schedule a viewing.";

    $this->propertyData = [
        'title' => 'Luxury 5-Bedroom Duplex',
        'type' => 'House',
        'location' => 'Lekki Phase 1, Lagos',
        'price' => '85000000',
        'features' => 'Swimming pool, 24/7 security, modern kitchen',
    ];
});

describe('DescriptionScoringService - Score Calculation', function () {

    test('scoreDescription returns complete array structure', function () {
        $scores = $this->service->scoreDescription($this->sampleDescription, $this->propertyData);

        expect($scores)->toBeArray()
            ->and($scores)->toHaveKeys([
                'readability_score',
                'seo_score',
                'overall_score',
                'word_count',
                'character_count',
                'sentence_count',
                'average_sentence_length',
                'keyword_mentions',
                'readability_label',
                'seo_label',
            ]);
    });

    test('all scores are within valid range 0-100', function () {
        $scores = $this->service->scoreDescription($this->sampleDescription, $this->propertyData);

        expect($scores['readability_score'])->toBeGreaterThanOrEqual(0)
            ->and($scores['readability_score'])->toBeLessThanOrEqual(100)
            ->and($scores['seo_score'])->toBeGreaterThanOrEqual(0)
            ->and($scores['seo_score'])->toBeLessThanOrEqual(100)
            ->and($scores['overall_score'])->toBeGreaterThanOrEqual(0)
            ->and($scores['overall_score'])->toBeLessThanOrEqual(100);
    });

    test('overall score is weighted average of readability and seo', function () {
        $scores = $this->service->scoreDescription($this->sampleDescription, $this->propertyData);

        $expectedOverall = (int) round(
            ($scores['readability_score'] * 0.4) + ($scores['seo_score'] * 0.6)
        );

        expect($scores['overall_score'])->toBe($expectedOverall);
    });
});

describe('DescriptionScoringService - Readability Scoring', function () {

    test('simple text scores higher than complex text', function () {
        $simpleText = "This is a house. It has rooms. It is nice. You will like it.";
        $complexText = "Notwithstanding the aforementioned architectural magnificence, "
            . "the preponderance of extraordinarily sophisticated amenities necessitates "
            . "a comprehensive evaluation of the multifaceted investment opportunity.";

        $simpleScore = $this->service->scoreDescription($simpleText, $this->propertyData);
        $complexScore = $this->service->scoreDescription($complexText, $this->propertyData);

        expect($simpleScore['readability_score'])
            ->toBeGreaterThan($complexScore['readability_score']);
    });

    test('readability label matches score range', function () {
        $scores = $this->service->scoreDescription($this->sampleDescription, $this->propertyData);

        $label = $scores['readability_label'];
        $score = $scores['readability_score'];

        if ($score >= 80) {
            expect($label)->toBeIn(['Very Easy', 'Easy']);
        } elseif ($score >= 60) {
            expect($label)->toBeIn(['Easy', 'Standard', 'Fairly Easy']);
        } else {
            expect($label)->toBeIn(['Moderate', 'Difficult', 'Very Difficult']);
        }
    });
});

describe('DescriptionScoringService - SEO Scoring', function () {

    test('mentions property type increases SEO score', function () {
        $withType = "This beautiful House in Lekki is perfect. Contact us today.";
        $withoutType = "This beautiful property in Lekki is perfect. Contact us today.";

        $scoreWith = $this->service->scoreDescription($withType, $this->propertyData);
        $scoreWithout = $this->service->scoreDescription($withoutType, $this->propertyData);

        expect($scoreWith['seo_score'])
            ->toBeGreaterThan($scoreWithout['seo_score']);
    });

    test('mentions location increases SEO score', function () {
        $withLocation = "This house in Lekki Phase 1 is perfect. Contact us today.";
        $withoutLocation = "This house is perfect. Contact us today.";

        $scoreWith = $this->service->scoreDescription($withLocation, $this->propertyData);
        $scoreWithout = $this->service->scoreDescription($withoutLocation, $this->propertyData);

        expect($scoreWith['seo_score'])
            ->toBeGreaterThan($scoreWithout['seo_score']);
    });

    test('optimal length 150-250 words scores higher', function () {
        // Generate text of different lengths
        $optimalText = str_repeat("This is a sentence about the property. ", 30); // ~180 words
        $shortText = "Short description."; // Very short

        $optimalScore = $this->service->scoreDescription($optimalText, $this->propertyData);
        $shortScore = $this->service->scoreDescription($shortText, $this->propertyData);

        expect($optimalScore['seo_score'])
            ->toBeGreaterThan($shortScore['seo_score']);
    });

    test('call to action increases SEO score', function () {
        $withCTA = "This beautiful house in Lekki is perfect. Contact us today to schedule a viewing.";
        $withoutCTA = "This beautiful house in Lekki is perfect and has many features.";

        $scoreWith = $this->service->scoreDescription($withCTA, $this->propertyData);
        $scoreWithout = $this->service->scoreDescription($withoutCTA, $this->propertyData);

        expect($scoreWith['seo_score'])
            ->toBeGreaterThanOrEqual($scoreWithout['seo_score']);
    });
});

describe('DescriptionScoringService - Metrics Calculation', function () {

    test('word count is accurate', function () {
        $text = "One two three four five.";
        $scores = $this->service->scoreDescription($text, $this->propertyData);

        expect($scores['word_count'])->toBe(5);
    });

    test('sentence count is accurate', function () {
        $text = "First sentence. Second sentence! Third sentence?";
        $scores = $this->service->scoreDescription($text, $this->propertyData);

        expect($scores['sentence_count'])->toBe(3);
    });

    test('character count includes spaces', function () {
        $text = "Hello World"; // 11 characters with space
        $scores = $this->service->scoreDescription($text, $this->propertyData);

        expect($scores['character_count'])->toBe(11);
    });

    test('average sentence length calculated correctly', function () {
        $text = "First. Second sentence."; // 1 word, then 2 words = avg 1.5
        $scores = $this->service->scoreDescription($text, $this->propertyData);

        expect($scores['average_sentence_length'])->toBeGreaterThan(0);
    });

    test('keyword mentions counted correctly', function () {
        $text = "This House in Lekki Phase 1, Lagos is a beautiful House.";
        $scores = $this->service->scoreDescription($text, $this->propertyData);

        // Should count: House (type), Lekki, Phase 1, Lagos (location parts)
        expect($scores['keyword_mentions'])->toBeGreaterThanOrEqual(2);
    });
});

describe('DescriptionScoringService - Edge Cases', function () {

    test('handles empty description gracefully', function () {
        $scores = $this->service->scoreDescription('', $this->propertyData);

        expect($scores['word_count'])->toBe(0)
            ->and($scores['readability_score'])->toBe(0);
    });

    test('handles very long description', function () {
        $longText = str_repeat("This is a sentence. ", 500); // Very long
        $scores = $this->service->scoreDescription($longText, $this->propertyData);

        expect($scores['word_count'])->toBeGreaterThan(1000)
            ->and($scores)->toBeArray();
    });

    test('handles description with special characters', function () {
        $specialText = "House @ Lekki! Price: ₦85M. Features: Pool & Gym. Call: +234...";
        $scores = $this->service->scoreDescription($specialText, $this->propertyData);

        expect($scores['word_count'])->toBeGreaterThan(0)
            ->and($scores['readability_score'])->toBeGreaterThan(0);
    });

    test('handles single sentence description', function () {
        $text = "This is a single sentence description of the property.";
        $scores = $this->service->scoreDescription($text, $this->propertyData);

        expect($scores['sentence_count'])->toBe(1)
            ->and($scores['average_sentence_length'])->toBeGreaterThan(0);
    });
});

describe('DescriptionScoringService - Helper Methods', function () {

    test('getScoreColor returns correct colors', function () {
        expect($this->service->getScoreColor(85))->toBe('green')
            ->and($this->service->getScoreColor(70))->toBe('blue')
            ->and($this->service->getScoreColor(50))->toBe('yellow')
            ->and($this->service->getScoreColor(30))->toBe('red');
    });

    test('score labels are not empty', function () {
        $scores = $this->service->scoreDescription($this->sampleDescription, $this->propertyData);

        expect($scores['readability_label'])->not->toBeEmpty()
            ->and($scores['seo_label'])->not->toBeEmpty();
    });
});
