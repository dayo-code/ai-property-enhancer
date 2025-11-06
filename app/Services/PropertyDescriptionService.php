<?php

namespace App\Services;

use Exception;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\PropertyDescription;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for generating AI-powered property descriptions
 *
 * This class handles all OpenAI API interactions and prompt engineering
 * for creating compelling, SEO-optimized property descriptions.
 */
class PropertyDescriptionService
{
    private const MAX_RETRIES = 3;
    private const TIMEOUT = 30;

    /**
     * Generates a property description using OpenAI
     *
     * @param array $propertyData Array containing title, type, location, price, features
     * @param string $tone The tone of the description (formal or casual)
     * @return string Generated description
     * @throws Exception When API call fails after retries
     */
    public function generateDescription(array $propertyData, string $tone = 'formal'): string
    {
        $prompt = $this->buildPrompt($propertyData, $tone);

        return $this->callOpenAI($prompt);
    }

    /**
     * Build a structured prompt for OpenAI
     *
     * This method creates SEO-optimized prompts that guide the AI
     * to generate professional, engaging property descriptions.
     *
     * @param array $propertyData Property information
     * @param string $tone Desired tone (formal or casual)
     * @return string Formatted prompt
     */
    private function buildPrompt(array $propertyData, string $tone): string
    {
        // Fetch last few generated descriptions
        $previousDescriptions = PropertyDescription::latest()
            ->take(2)
            ->pluck('generated_description')
            ->implode("\n\n---\n\n");

        $toneInstructions = $this->getToneInstructions($tone);
        $formattedPrice = '₦' . number_format((float)$propertyData['price']);

        return <<<PROMPT
            You are a top-tier Nigerian real estate copywriter who deeply understands the Nigerian property market, urban lifestyle, and buyer psychology across different cities and states.

            Your task is to write a captivating, SEO-friendly property description using the details below.

            Below are examples of previously generated descriptions.
            Each time you generate, make it sound naturally different — vary your structure, phrasing, and focus points even if the same data is used.

            Previous examples:
            {$previousDescriptions}

            Property Details:
            - Title: {$propertyData['title']}
            - Type: {$propertyData['type']}
            - Location: {$propertyData['location']}
            - Price: {$formattedPrice}
            - Key Features: {$propertyData['features']}

            Tone: {$toneInstructions}

            Context Rules:
            1. Adapt your writing to the local reality of the given location:
            - **Lagos:** Mention lifestyle appeal (island living, proximity to Lekki, Victoria Island, Ikoyi, or mainland convenience), security, road networks, traffic access, and neighbourhood prestige.
            - **Abuja:** Highlight serenity, modern infrastructure, government presence, accessibility, and quiet residential appeal (e.g., Gwarinpa, Wuse, Maitama, Lokogoma, Lugbe).
            - **Port Harcourt:** Emphasize investment potential, oil city lifestyle, good roads, safety, and peaceful environment.
            - **Ibadan or other South-West cities:** Focus on affordability, space, family comfort, and steady development.
            - **Other cities:** Use realistic Nigerian context — infrastructure, electricity stability, access to transport, schools, markets, and job centres.

            Writing Guidelines:
            1. Keep it between 150–250 words.
            2. Use authentic Nigerian English — smooth, engaging, and believable.
            3. Present the property’s strongest features with storytelling and lifestyle context — space, comfort, modern design, and quality finishing.
            4. Include benefits of the area — nearby landmarks, schools, malls, places of worship, major roads, or business hubs.
            5. Use SEO keywords naturally (e.g., "{$propertyData['type']} for sale in {$propertyData['location']}").
            6. Write persuasively but honestly — reflect real Nigerian property values and buyer needs.
            7. Avoid generic clichés like “once in a lifetime” or “don’t miss out.”
            8. Vary sentence structure, vocabulary, and rhythm across outputs to ensure uniqueness.
            9. End with a confident, motivating call-to-action that fits Nigerian buyer behavior — e.g., *“Schedule an inspection today”*, *“Call now to book a viewing”*, or *“Secure this home before the price goes up.”*
            10. Focus on lifestyle, comfort, and practicality — not just features, but how it feels to live or invest there.

            Output ONLY the property description — no titles, meta notes, or commentary.
            PROMPT;

        }

    /**
     * Get tone-specific instructions for the prompt
     *
     * @param string $tone Desired tone
     * @return string Tone instructions
     */
    private function getToneInstructions(string $tone): string
    {
        return match(strtolower($tone)) {
            'casual' => 'Use a friendly, conversational tone. Write as if speaking to a friend. Use contractions and relaxed language while maintaining professionalism.',
            'formal' => 'Use a professional, sophisticated tone. Employ elegant language and proper grammar. Maintain a polished, corporate style.',
            default => 'Use a balanced, professional yet approachable tone.',
        };
    }

    /**
     * Call OpenAI API with retry logic
     *
     * Implements exponential backoff for handling temporary failures
     * and rate limiting from OpenAI.
     *
     * @param string $prompt The prompt to send
     * @param int $attempt Current attempt number
     * @return string Generated text
     * @throws Exception When all retries are exhausted
     */
    private function callOpenAI(string $prompt, int $attempt = 1): string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini', // Cost-effective and fast
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert Nigerian real estate copywriter. Write persuasive, SEO-optimized property descriptions in Nigerian English based on user-provided property data.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7, // Balance creativity with consistency
                'top_p' => 0.9,
                'frequency_penalty' => 0.3, // Reduce repetition
                'presence_penalty' => 0.2, // Encourage topic diversity
            ]);

            $description = $response->choices[0]->message->content ?? '';

            if (empty($description)) {
                throw new Exception('Empty response from OpenAI');
            }

            // Clean up the response
            return $this->cleanDescription($description);

        } catch (Exception $e) {
            Log::warning('OpenAI API call failed', [
                'attempt' => $attempt,
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt)
            ]);

            // Retry with exponential backoff
            if ($attempt < self::MAX_RETRIES) {
                $waitTime = pow(2, $attempt); // 2, 4, 8 seconds
                sleep($waitTime);

                return $this->callOpenAI($prompt, $attempt + 1);
            }

            // All retries exhausted
            Log::error('OpenAI API failed after all retries', [
                'error' => $e->getMessage(),
                'attempts' => $attempt
            ]);

            throw new Exception(
                'Unable to generate description at this time. Please try again in a few moments.',
                0,
                $e
            );
        }
    }

    /**
     * Clean and format the generated description
     *
     * @param string $description Raw description from AI
     * @return string Cleaned description
     */
    private function cleanDescription(string $description): string
    {
        // Remove common AI artifacts
        $description = trim($description);

        // Remove quotes if the entire text is wrapped in them
        if (
            (str_starts_with($description, '"') && str_ends_with($description, '"')) ||
            (str_starts_with($description, "'") && str_ends_with($description, "'"))
        ) {
            $description = substr($description, 1, -1);
        }

        // Remove any markdown formatting
        $description = preg_replace('/\*\*(.*?)\*\*/', '$1', $description);
        $description = preg_replace('/\*(.*?)\*/', '$1', $description);

        return trim($description);
    }

    /**
     * Validate property data before sending to API
     *
     * @param array $propertyData Data to validate
     * @return bool
     */
    public function validatePropertyData(array $propertyData): bool
    {
        $required = ['title', 'type', 'location', 'price', 'features'];

        foreach ($required as $field) {
            if (empty($propertyData[$field])) {
                return false;
            }
        }

        return true;
    }
}
