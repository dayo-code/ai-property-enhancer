<?php

namespace App\Livewire;

use App\Models\PropertyDescription;
use App\Services\PropertyDescriptionService;
use App\Services\DescriptionScoringService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use Exception;

class PropertyDescriptionGenerator extends Component
{
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|in:House,Flat,Land,Commercial')]
    public string $propertyType = '';

    #[Validate('required|string|max:255')]
    public string $location = '';

    #[Validate('required|numeric|min:0')]
    public string $price = '';

    #[Validate('required|string|min:10')]
    public string $keyFeatures = '';

    #[Validate('required|in:formal,casual')]
    public string $tone = 'formal';

    public ?string $generatedDescription = null;
    public bool $isGenerating = false;
    public int $generationCount = 0;
    public ?array $scores = null;
    public bool $showHistory = false;
    public ?int $loadedHistoryId = null;

    /**
     * Property Description Service
     */
    private PropertyDescriptionService $descriptionService;
    private DescriptionScoringService $scoringService;

    /**
     * Boot the component with dependency injection
     */
    public function boot(PropertyDescriptionService $descriptionService, DescriptionScoringService $scoringService): void
    {
        $this->descriptionService = $descriptionService;
        $this->scoringService = $scoringService;
    }

    /**
     * Make scoring service available to view
     */
    public function getScoringServiceProperty(): DescriptionScoringService
    {
        return $this->scoringService;
    }

    /**
     * Available property types
     */
    public function getPropertyTypesProperty(): array
    {
        return [
            'House' => 'House',
            'Flat' => 'Flat',
            'Land' => 'Land',
            'Commercial' => 'Commercial',
        ];
    }

    /**
     * Available tone options
     */
    public function getTonesProperty(): array
    {
        return [
            'formal' => 'Professional & Formal',
            'casual' => 'Friendly & Casual',
        ];
    }

    /**
     * Get recent history
     */
    public function getHistoryProperty()
    {
        return PropertyDescription::recent(20)->get();
    }


    /**
     * Generate AI-powered property description
     */
    public function generateDescription(): void
    {
        // Validate form inputs
        $this->validate();

        // Set loading state
        $this->isGenerating = true;
        $this->resetErrorBag();

        try {
            // Prepare property data
            $propertyData = [
                'title' => $this->title,
                'type' => $this->propertyType,
                'location' => $this->location,
                'price' => $this->price,
                'features' => $this->keyFeatures,
            ];

            // Validate data structure
            if (!$this->descriptionService->validatePropertyData($propertyData)) {
                throw new Exception('Invalid property data provided');
            }

            // Generate description using AI service
            $this->generatedDescription = $this->descriptionService->generateDescription(
                $propertyData,
                $this->tone
            );

            // Calculate scores
            $this->scores = $this->scoringService->scoreDescription(
                $this->generatedDescription,
                $propertyData
            );

            // Save to history
            $this->saveToHistory($propertyData);

            // Increment generation counter
            $this->generationCount++;

            // Dispatch success event for potential frontend handling
            $this->dispatch('description-generated', [
                'count' => $this->generationCount,
                'scores' => $this->scores
            ]);

            // Show success message
            session()->flash('success', 'Description generated successfully!');

        } catch (Exception $e) {
            // Log error for debugging
            logger()->error('Description generation failed', [
                'error' => $e->getMessage(),
                'property' => $this->title,
            ]);

            // Show user-friendly error message
            $this->addError('generation', $e->getMessage());

            // Reset description on error
            $this->generatedDescription = null;
            $this->scores = null;

        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Save generated description to history
     */
    private function saveToHistory(array $propertyData): void
    {
        try {
            PropertyDescription::create([
                'title' => $propertyData['title'],
                'property_type' => $propertyData['type'],
                'location' => $propertyData['location'],
                'price' => $propertyData['price'],
                'key_features' => $propertyData['features'],
                'tone' => $this->tone,
                'generated_description' => $this->generatedDescription,
                'readability_score' => $this->scores['readability_score'] ?? null,
                'seo_score' => $this->scores['seo_score'] ?? null,
                'overall_score' => $this->scores['overall_score'] ?? null,
                'word_count' => $this->scores['word_count'] ?? null,
                'character_count' => $this->scores['character_count'] ?? null,
                'sentence_count' => $this->scores['sentence_count'] ?? null,
                'average_sentence_length' => $this->scores['average_sentence_length'] ?? null,
                'keyword_mentions' => $this->scores['keyword_mentions'] ?? null,
            ]);
        } catch (Exception $e) {
            // Log but don't fail generation if history save fails
            logger()->warning('Failed to save to history', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Load a description from history
     */
    public function loadFromHistory(int $id): void
    {
        try {
            $history = PropertyDescription::findOrFail($id);

            $this->title = $history->title;
            $this->propertyType = $history->property_type;
            $this->location = $history->location;
            $this->price = (string) $history->price;
            $this->keyFeatures = $history->key_features;
            $this->tone = $history->tone;
            $this->generatedDescription = $history->generated_description;

            $this->scores = [
                'readability_score' => $history->readability_score,
                'seo_score' => $history->seo_score,
                'overall_score' => $history->overall_score,
                'word_count' => $history->word_count,
                'character_count' => $history->character_count,
                'sentence_count' => $history->sentence_count,
                'average_sentence_length' => $history->average_sentence_length,
                'keyword_mentions' => $history->keyword_mentions,
                'readability_label' => $this->scoringService->getReadabilityLabel($history->readability_score),
                'seo_label' => $this->scoringService->getSeoLabel($history->seo_score),
            ];

            $this->loadedHistoryId = $id;

            session()->flash('success', 'Loaded from history!');
        } catch (Exception $e) {
            $this->addError('history', 'Failed to load from history');
        }
    }

    /**
     * Delete a history entry
     */
    public function deleteFromHistory(int $id): void
    {
        try {
            PropertyDescription::findOrFail($id)->delete();

            // If currently loaded description was deleted, clear it
            if ($this->loadedHistoryId === $id) {
                $this->resetForm();
            }

            session()->flash('success', 'History entry deleted!');

        } catch (Exception $e) {
            $this->addError('history', 'Failed to delete history entry');
        }
    }

    /**
     * Clear all history
     */
    public function clearAllHistory(): void
    {
        try {
            PropertyDescription::truncate();

            session()->flash('success', 'All history cleared!');

        } catch (Exception $e) {
            $this->addError('history', 'Failed to clear history');
        }
    }

    /**
     * Toggle history sidebar
     */
    public function toggleHistory(): void
    {
        $this->showHistory = !$this->showHistory;
    }

    /**
     * Regenerate a new version of the description
     */
    public function regenerateDescription(): void
    {
        if ($this->generatedDescription) {
            $this->loadedHistoryId = null; // Clear loaded flag
            $this->generateDescription();
        }
    }

    /**
     * Reset the form
     */
    public function resetForm(): void
    {
        $this->reset([
            'title',
            'propertyType',
            'location',
            'price',
            'keyFeatures',
            'tone',
            'generatedDescription',
            'generationCount'
        ]);
        $this->resetValidation();
        $this->resetErrorBag();

        session()->forget('success');
    }

    /**
     * Update tone and regenerate if description exists
     */
    public function updatedTone(): void
    {
        // If a description already exists, regenerate with new tone
        if ($this->generatedDescription && !$this->isGenerating) {
            $this->loadedHistoryId = null; // Clear loaded flag
            $this->generateDescription();
        }
    }

    public function render()
    {
        return view('livewire.property-description-generator');
    }

}
