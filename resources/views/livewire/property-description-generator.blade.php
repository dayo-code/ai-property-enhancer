<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
                AI Property Description Enhancer
            </h1>
            <p class="text-gray-600">Generate compelling, SEO-optimized property descriptions instantly</p>
        </div>

        <!-- Main Form Card -->
        <div class="card mb-6">
            <form wire:submit="generateDescription" class="space-y-6">

                <!-- Title Input -->
                <div>
                    <label for="title" class="label">
                        Property Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" wire:model="title"
                        class="input-field @error('title') border-red-500 @enderror"
                        placeholder="e.g., Luxury 4-Bedroom Duplex">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Property Type Dropdown -->
                <div>
                    <label for="propertyType" class="label">
                        Property Type <span class="text-red-500">*</span>
                    </label>
                    <select id="propertyType" wire:model="propertyType"
                        class="input-field @error('propertyType') border-red-500 @enderror">
                        <option value="">Select property type</option>
                        @foreach ($this->propertyTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('propertyType')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Location Input -->
                <div>
                    <label for="location" class="label">
                        Location <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="location" wire:model="location"
                        class="input-field @error('location') border-red-500 @enderror"
                        placeholder="e.g., Lekki Phase 1, Lagos">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price Input -->
                <div>
                    <label for="price" class="label">
                        Price (₦) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="price" wire:model="price"
                        class="input-field @error('price') border-red-500 @enderror" placeholder="e.g., 50000000"
                        min="0" step="1000">
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Key Features Textarea -->
                <div>
                    <label for="keyFeatures" class="label">
                        Key Features <span class="text-red-500">*</span>
                    </label>
                    <textarea id="keyFeatures" wire:model="keyFeatures" rows="4"
                        class="input-field @error('keyFeatures') border-red-500 @enderror"
                        placeholder="e.g., Swimming pool, 24/7 security, Fully fitted kitchen, Ample parking space"></textarea>
                    @error('keyFeatures')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Error Messages -->
                @error('generation')
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
                        <p class="font-medium">{{ $message }}</p>
                    </div>
                @enderror

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary flex-1 flex items-center justify-center gap-2"
                        wire:loading.attr="disabled" wire:target="generateDescription">
                        <span wire:loading.remove wire:target="generateDescription">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="generateDescription">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                        <span>
                            {{ $isGenerating ? 'Generating...' : 'Generate Description' }}
                        </span>
                    </button>

                    @if ($generatedDescription)
                        <button type="button" wire:click="resetForm" class="btn btn-secondary">
                            Reset
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- Generated Description Card -->
        @if ($generatedDescription)
            <div class="card bg-gradient-to-br from-primary-50 to-blue-50 border border-primary-200"
                x-data="{ copied: false }">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Generated Description
                    </h2>
                </div>

                <div class="bg-white rounded-lg p-6 mb-4 shadow-sm">
                    <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $generatedDescription }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3">
                    <button type="button" wire:click="regenerateDescription"
                        class="btn btn-primary flex items-center gap-2" wire:loading.attr="disabled"
                        wire:target="regenerateDescription">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Regenerate
                    </button>

                    <button type="button"
                        @click="
                        navigator.clipboard.writeText('{{ addslashes($generatedDescription) }}');
                        copied = true;
                        setTimeout(() => copied = false, 2000);
                    "
                        class="btn bg-green-600 text-white hover:bg-green-700 flex items-center gap-2">
                        <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <svg x-show="copied" class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="copied ? 'Copied!' : 'Copy Description'"></span>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
