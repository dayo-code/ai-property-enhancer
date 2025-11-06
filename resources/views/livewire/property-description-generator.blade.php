<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="flex flex-col lg:flex-row gap-6">

        <!-- History Sidebar -->
        <div class="lg:w-80 order-2 lg:order-1">
            <div class="card sticky top-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        History
                        @if ($this->history->count() > 0)
                            <span class="text-xs bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full">
                                {{ $this->history->count() }}
                            </span>
                        @endif
                    </h3>

                    @if ($this->history->count() > 0)
                        <button wire:click="clearAllHistory" wire:confirm="Are you sure you want to delete all history?"
                            class="text-xs text-red-600 hover:text-red-700 font-medium">
                            Clear All
                        </button>
                    @endif
                </div>

                @if ($this->history->count() > 0)
                    <div class="space-y-3 max-h-[600px] overflow-y-auto">
                        @foreach ($this->history as $item)
                            <div
                                class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition border border-gray-200 @if ($loadedHistoryId === $item->id) ring-2 ring-primary-500 @endif">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h4 class="font-medium text-sm text-gray-900 line-clamp-1">
                                        {{ ucwords($item->title) }}
                                    </h4>
                                    <button wire:click="deleteFromHistory({{ $item->id }})"
                                        wire:confirm="Delete this history entry?"
                                        class="flex-shrink-0 text-gray-400 hover:text-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                                    <span class="px-2 py-0.5 bg-white rounded border border-gray-200">
                                        {{ $item->property_type }}
                                    </span>
                                    @if ($item->overall_score)
                                        <span
                                            class="px-2 py-0.5 rounded font-medium
                                            @if ($item->overall_score >= 80) bg-green-100 text-green-700
                                            @elseif($item->overall_score >= 60) bg-blue-100 text-blue-700
                                            @elseif($item->overall_score >= 40) bg-yellow-100 text-yellow-700
                                            @else bg-red-100 text-red-700 @endif">
                                            {{ $item->overall_score }}/100
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-gray-600 mb-2 line-clamp-2">
                                    {{ $item->short_description }}
                                </p>

                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400">
                                        {{ $item->time_ago }}
                                    </span>
                                    <button wire:click="loadFromHistory({{ $item->id }})"
                                        class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        Load
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-gray-500">No history yet</p>
                        <p class="text-xs text-gray-400 mt-1">Generated descriptions will appear here</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 order-1 lg:order-2">
            <!-- Header -->
            <div class="mb-8 text-center lg:text-left">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
                    AI Property Description Enhancer
                </h1>
                <p class="text-gray-600">Generate compelling, SEO-optimized property descriptions instantly</p>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg animate-fade-in"
                    role="alert">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @error('history')
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg animate-fade-in"
                    role="alert">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium">{{ $message }}</p>
                    </div>
                </div>
            @enderror

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
                            placeholder="e.g., Durumi, Abuja">
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
                            min="0" step="100000">
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

                    <!-- Tone Selector -->
                    <div>
                        <label class="label">
                            Description Tone <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($this->tones as $value => $label)
                                <label class="relative flex items-center cursor-pointer">
                                    <input type="radio" name="tone" value="{{ $value }}"
                                        wire:model.live="tone" class="peer sr-only" />

                                    <div
                                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-300
                    transition-all duration-200 hover:border-gray-400
                    peer-checked:border-primary-600 peer-checked:bg-primary-100
                    peer-checked:shadow-md peer-checked:scale-[1.02]">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-4 h-4 rounded-full border-2 border-gray-300
                            flex items-center justify-center
                            peer-checked:border-primary-600 peer-checked:bg-primary-600">
                                                <div class="w-2 h-2 rounded-full bg-white hidden peer-checked:block">
                                                </div>
                                            </div>
                                            <span
                                                class="text-sm font-medium text-gray-700
                            peer-checked:text-primary-700">
                                                {{ $label }}
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @error('tone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Error Messages -->
                    @error('generation')
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="font-medium">{{ $message }}</p>
                                    <p class="text-sm mt-1">Please check your internet connection and try again.</p>
                                </div>
                            </div>
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
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="generateDescription">
                                Generate Description
                            </span>
                            <span wire:loading wire:target="generateDescription">
                                Generating...
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
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Generated Description
                        </h2>
                    </div>

                    <div class="bg-white rounded-lg p-6 mb-4 shadow-sm">
                        <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $generatedDescription }}</p>

                        @if ($generationCount > 0)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Generated {{ $generationCount }} {{ $generationCount === 1 ? 'time' : 'times' }} •
                                    {{ ucfirst($tone) }} tone
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Quality Scores -->
                    @if ($scores)
                        <div class="bg-white rounded-lg p-6 mb-4 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Quality Scores
                            </h3>

                            <!-- Overall Score -->
                            <div
                                class="mb-4 p-4 bg-gradient-to-r from-{{ $this->scoringService->getScoreColor($scores['overall_score']) }}-50 to-{{ $this->scoringService->getScoreColor($scores['overall_score']) }}-100 rounded-lg border border-{{ $this->scoringService->getScoreColor($scores['overall_score']) }}-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Overall Score</span>
                                    <span
                                        class="text-2xl font-bold text-{{ $this->scoringService->getScoreColor($scores['overall_score']) }}-700">
                                        {{ $scores['overall_score'] }}/100
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Readability Score -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Readability</span>
                                        <span
                                            class="text-sm font-semibold text-{{ $this->scoringService->getScoreColor($scores['readability_score']) }}-700">
                                            {{ $scores['readability_score'] }}/100
                                        </span>
                                    </div>
                                    {{-- <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-{{ $this->scoringService->getScoreColor($scores['readability_score']) }}-600 h-2.5 rounded-full transition-all duration-500"
                                            style="width: {{ $scores['readability_score'] }}%"></div>
                                    </div> --}}
                                    <p class="text-xs text-gray-500 mt-1">{{ $scores['readability_label'] }}</p>
                                </div>

                                <!-- SEO Score -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">SEO Strength</span>
                                        <span
                                            class="text-sm font-semibold text-{{ $this->scoringService->getScoreColor($scores['seo_score']) }}-700">
                                            {{ $scores['seo_score'] }}/100
                                        </span>
                                    </div>
                                    {{-- <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-{{ $this->scoringService->getScoreColor($scores['seo_score']) }}-600 h-2.5 rounded-full transition-all duration-500"
                                            style="width: {{ $scores['seo_score'] }}%"></div>
                                    </div> --}}
                                    <p class="text-xs text-gray-500 mt-1">{{ $scores['seo_label'] }}</p>
                                </div>
                            </div>

                            <!-- Detailed Metrics -->
                            <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">{{ $scores['word_count'] }}</p>
                                    <p class="text-xs text-gray-500">Words</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">{{ $scores['sentence_count'] }}</p>
                                    <p class="text-xs text-gray-500">Sentences</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ $scores['average_sentence_length'] }}</p>
                                    <p class="text-xs text-gray-500">Avg Length</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold text-gray-900">{{ $scores['keyword_mentions'] }}</p>
                                    <p class="text-xs text-gray-500">Keywords</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3">
                        <button type="button" wire:click="regenerateDescription"
                            class="btn btn-primary flex items-center justify-center gap-2"
                            wire:loading.attr="disabled" wire:target="regenerateDescription">
                            <!-- Normal Icon -->
                            <span wire:loading.remove wire:target="regenerateDescription">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </span>

                            <!-- Spinner Icon -->
                            <span wire:loading wire:target="regenerateDescription">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="regenerateDescription">
                                Regenerate
                            </span>
                            <span wire:loading wire:target="regenerateDescription">
                                Regenerating...
                            </span>
                        </button>

                        <div x-data="{
                            copied: false,
                            copyDescription() {
                                const text = @js($generatedDescription);
                                navigator.clipboard.writeText(text)
                                    .then(() => {
                                        this.copied = true;
                                        setTimeout(() => this.copied = false, 2000);
                                    })
                                    .catch(err => console.error('Copy failed', err));
                            }
                        }" key="{{ md5($generatedDescription) }}">
                            <button type="button" @click="copyDescription"
                                class="btn bg-green-600 text-white hover:bg-green-700 flex items-center gap-2">
                                <!-- Copy icon -->
                                <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>

                                <!-- Check icon -->
                                <svg x-show="copied" x-cloak class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>

                                <span x-text="copied ? 'Copied!' : 'Copy Description'"></span>
                            </button>
                        </div>


                    </div>
                </div>
            @endif
        </div><!-- End Main Content -->
    </div><!-- End Flex Container -->
</div><!-- End Max Width Container -->
