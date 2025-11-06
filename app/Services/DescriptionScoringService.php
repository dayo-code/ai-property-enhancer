<?php

namespace App\Services;

/**
 * Service for calculating quality metrics for property descriptions
 *
 * Provides readability scores, SEO analysis, and various text metrics
 * to help users understand the quality of generated descriptions.
 */
class DescriptionScoringService
{
    /**
     * Calculate comprehensive score for a description
     *
     * @param string $description The property description to analyze
     * @param array $propertyData Original property data for keyword analysis
     * @return array Comprehensive scoring data
     */
    public function scoreDescription(string $description, array $propertyData): array
    {
        $readabilityScore = $this->calculateReadabilityScore($description);
        $seoScore = $this->calculateSeoScore($description, $propertyData);

        // Overall score is weighted average
        $overallScore = (int) round(($readabilityScore * 0.4) + ($seoScore * 0.6));

        return [
            'readability_score' => $readabilityScore,
            'seo_score' => $seoScore,
            'overall_score' => $overallScore,
            'word_count' => $this->countWords($description),
            'character_count' => $this->countCharacters($description),
            'sentence_count' => $this->countSentences($description),
            'average_sentence_length' => $this->averageSentenceLength($description),
            'keyword_mentions' => $this->countKeywordMentions($description, $propertyData),
            'readability_label' => $this->getReadabilityLabel($readabilityScore),
            'seo_label' => $this->getSeoLabel($seoScore),
        ];
    }

    /**
     * Calculate Flesch Reading Ease score
     *
     * Formula: 206.835 - 1.015(total words/total sentences) - 84.6(total syllables/total words)
     * Score interpretation:
     * 90-100: Very Easy (5th grade)
     * 80-89: Easy (6th grade)
     * 70-79: Fairly Easy (7th grade)
     * 60-69: Standard (8th-9th grade)
     * 50-59: Fairly Difficult (10th-12th grade)
     * 30-49: Difficult (College)
     * 0-29: Very Confusing (College graduate)
     *
     * @param string $text Text to analyze
     * @return int Score from 0-100
     */
    private function calculateReadabilityScore(string $text): int
    {
        $wordCount = $this->countWords($text);
        $sentenceCount = max(1, $this->countSentences($text));
        $syllableCount = $this->countSyllables($text);

        // Avoid division by zero
        if ($wordCount === 0) {
            return 0;
        }

        // Flesch Reading Ease formula
        $score = 206.835
            - (1.015 * ($wordCount / $sentenceCount))
            - (84.6 * ($syllableCount / $wordCount));

        // Clamp score between 0 and 100
        return max(0, min(100, (int) round($score)));
    }

    /**
     * Calculate SEO score based on multiple factors
     *
     * @param string $description Description text
     * @param array $propertyData Property information for keyword extraction
     * @return int Score from 0-100
     */
    private function calculateSeoScore(string $description, array $propertyData): int
    {
        $score = 0;
        $descriptionLower = strtolower($description);

        // Factor 1: Optimal length (150-250 words) - 30 points
        $wordCount = $this->countWords($description);
        if ($wordCount >= 150 && $wordCount <= 250) {
            $score += 30;
        } elseif ($wordCount >= 100 && $wordCount < 150) {
            $score += 20;
        } elseif ($wordCount > 250 && $wordCount <= 300) {
            $score += 25;
        } else {
            $score += 10;
        }

        // Factor 2: Property type mentioned - 15 points
        if (isset($propertyData['type']) &&
            str_contains($descriptionLower, strtolower($propertyData['type']))) {
            $score += 15;
        }

        // Factor 3: Location mentioned - 15 points
        if (isset($propertyData['location'])) {
            $locationParts = explode(',', $propertyData['location']);
            foreach ($locationParts as $part) {
                if (str_contains($descriptionLower, strtolower(trim($part)))) {
                    $score += 15;
                    break;
                }
            }
        }

        // Factor 4: Price context (investment, value, etc.) - 10 points
        $valueKeywords = ['investment', 'value', 'price', 'affordable', 'premium', 'luxury'];
        foreach ($valueKeywords as $keyword) {
            if (str_contains($descriptionLower, $keyword)) {
                $score += 10;
                break;
            }
        }

        // Factor 5: Call to action present - 10 points
        $ctaKeywords = ['contact', 'call', 'schedule', 'visit', 'inquire', 'reach out'];
        foreach ($ctaKeywords as $keyword) {
            if (str_contains($descriptionLower, $keyword)) {
                $score += 10;
                break;
            }
        }

        // Factor 6: Key features mentioned - 10 points
        if (isset($propertyData['features'])) {
            $featureWords = str_word_count(strtolower($propertyData['features']), 1);
            $mentionedCount = 0;
            foreach ($featureWords as $word) {
                if (strlen($word) > 4 && str_contains($descriptionLower, $word)) {
                    $mentionedCount++;
                }
            }
            if ($mentionedCount >= 3) {
                $score += 10;
            } elseif ($mentionedCount >= 1) {
                $score += 5;
            }
        }

        // Factor 7: Good sentence structure (not too long) - 10 points
        $avgSentenceLength = $this->averageSentenceLength($description);
        if ($avgSentenceLength >= 15 && $avgSentenceLength <= 25) {
            $score += 10;
        } elseif ($avgSentenceLength >= 10 && $avgSentenceLength <= 30) {
            $score += 5;
        }

        return min(100, $score);
    }

    /**
     * Count total words in text
     */
    private function countWords(string $text): int
    {
        return str_word_count($text);
    }

    /**
     * Count total characters in text
     */
    private function countCharacters(string $text): int
    {
        return mb_strlen($text);
    }

    /**
     * Count sentences in text
     */
    private function countSentences(string $text): int
    {
        // Count sentence-ending punctuation
        $parts = preg_split('/(?<=[.!?])\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_map('trim', $parts);

        return count($sentences);
    }

    /**
     * Calculate average sentence length
     */
    private function averageSentenceLength(string $text): float
    {
        $wordCount = $this->countWords($text);
        $sentenceCount = max(1, $this->countSentences($text));

        return round($wordCount / $sentenceCount, 1);
    }

    /**
     * Count syllables in text (approximation)
     *
     * Uses a simplified algorithm:
     * - Count vowel groups
     * - Subtract silent 'e' at end of words
     * - Each word has at least 1 syllable
     */
    private function countSyllables(string $text): int
    {
        $words = str_word_count(strtolower($text), 1);
        $totalSyllables = 0;

        foreach ($words as $word) {
            $syllables = 0;

            // Count vowel groups
            $syllables = preg_match_all('/[aeiouy]+/', $word);

            // Subtract silent e at end
            if (preg_match('/[^aeiou]e$/', $word)) {
                $syllables--;
            }

            // Ensure at least 1 syllable per word
            $syllables = max(1, $syllables);

            $totalSyllables += $syllables;
        }

        return $totalSyllables;
    }

    /**
     * Count keyword mentions in description
     */
    private function countKeywordMentions(string $description, array $propertyData): int
    {
        $descriptionLower = strtolower($description);
        $mentions = 0;

        // Check property type
        if (isset($propertyData['type']) &&
            str_contains($descriptionLower, strtolower($propertyData['type']))) {
            $mentions++;
        }

        // Check location parts
        if (isset($propertyData['location'])) {
            $locationParts = explode(',', $propertyData['location']);
            foreach ($locationParts as $part) {
                if (str_contains($descriptionLower, strtolower(trim($part)))) {
                    $mentions++;
                }
            }
        }

        return $mentions;
    }

    /**
     * Get readability label based on score
     */
    public function getReadabilityLabel(int $score): string
    {
        return match(true) {
            $score >= 80 => 'Very Easy',
            $score >= 70 => 'Easy',
            $score >= 60 => 'Standard',
            $score >= 50 => 'Moderate',
            $score >= 30 => 'Difficult',
            default => 'Very Difficult',
        };
    }

    /**
     * Get SEO label based on score
     */
    public function getSeoLabel(int $score): string
    {
        return match(true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Needs Improvement',
            default => 'Poor',
        };
    }

    /**
     * Get color class for score display
     */
    public function getScoreColor(int $score): string
    {
        return match(true) {
            $score >= 80 => 'green',
            $score >= 60 => 'blue',
            $score >= 40 => 'yellow',
            default => 'red',
        };
    }
}
