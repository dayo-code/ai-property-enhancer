<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;

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

    public ?string $generatedDescription = null;
    public bool $isGenerating = false;

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
     * Generate AI-powered property description
     */
    public function generateDescription(): void
    {
        // Validate form inputs
        $this->validate();

        // Set loading state
        $this->isGenerating = true;

        try {
            // TODO: Implement AI generation service call (Day 2)
            // For now, create a placeholder
            $this->generatedDescription = $this->createPlaceholderDescription();

            // Dispatch success event
            $this->dispatch('description-generated');

        } catch (\Exception $e) {
            // Handle errors gracefully
            $this->addError('generation', 'Failed to generate description. Please try again.');

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
            'generatedDescription'
        ]);
        $this->resetValidation();
    }

    /**
     * Placeholder description (to be replaced with AI in Day 2)
     */
    private function createPlaceholderDescription(): string
    {
        return "Beautiful {$this->propertyType} located in {$this->location}. "
            . "This stunning property is priced at ₦" . number_format((float)$this->price) . ". "
            . "Key features include: {$this->keyFeatures}. "
            . "Perfect for discerning buyers looking for quality and comfort.";
    }

    public function render()
    {
        return view('livewire.property-description-generator');
    }

}
