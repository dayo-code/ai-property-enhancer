<?php

namespace App\Livewire;

use App\Services\PropertyDescriptionService;
use Livewire\Component;
use Livewire\Attributes\Validate;
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

    /**
     * Property Description Service
     */
    private PropertyDescriptionService $descriptionService;

    /**
     * Boot the component with dependency injection
     */
    public function boot(PropertyDescriptionService $descriptionService): void
    {
        $this->descriptionService = $descriptionService;
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

            // Increment generation counter
            $this->generationCount++;

            // Dispatch success event for potential frontend handling
            $this->dispatch('description-generated', [
                'count' => $this->generationCount
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

        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Regenerate a new version of the description
     */
    public function regenerateDescription(): void
    {
        if ($this->generatedDescription) {
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
            $this->generateDescription();
        }
    }

    public function render()
    {
        return view('livewire.property-description-generator');
    }

}
