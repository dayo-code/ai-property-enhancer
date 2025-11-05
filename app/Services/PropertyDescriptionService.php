<?php

namespace App\Services;

//use OpenAI\Laravel\Facades\OpenAI;
use OpenAI;
use Illuminate\Support\Facades\Log;
use Exception;

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
    protected $aiClient;

    public function __construct(){
        $apiKey = config('services.openai.api_key');
        $this->aiClient = OpenAI::client($apiKey);
    }

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
        $toneInstructions = $this->getToneInstructions($tone);
        $formattedPrice = '₦' . number_format((float)$propertyData['price']);

        return <<<PROMPT
            You are a professional real estate copywriter specializing in Nigerian property listings.
            Create a compelling, SEO-optimized property description based on the following details:

            Property Title: {$propertyData['title']}
            Property Type: {$propertyData['type']}
            Location: {$propertyData['location']}
            Price: {$formattedPrice}
            Key Features: {$propertyData['features']}

            Tone: {$toneInstructions}

            Requirements:
            1. Write a natural, engaging description (150-250 words)
            2. Highlight the property's unique selling points
            3. Include location benefits and nearby amenities when relevant
            4. Use persuasive but honest language
            5. Incorporate SEO-friendly keywords naturally (e.g., "{$propertyData['type']} in {$propertyData['location']}")
            6. End with a compelling call-to-action
            7. Use Nigerian English and context
            8. Do not use generic phrases like "don't miss out" or "once in a lifetime"
            9. Focus on lifestyle benefits and practical advantages

            Write ONLY the property description. Do not include labels, titles, or meta-commentary.
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
            $response = $this->aiClient->chat()->create([
                'model' => 'gpt-4o-mini', // Cost-effective and fast
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert real estate copywriter specializing in creating compelling, SEO-optimized property descriptions for the Nigerian market.'
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
