<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# AI Property Description Enhancer

A production-ready Laravel 11 + Livewire 3 application that generates compelling, SEO-optimized property descriptions using OpenAI GPT-4.

[![Tests](https://img.shields.io/badge/tests-80%2B%20passing-success)](https://github.com)
[![Coverage](https://img.shields.io/badge/coverage-75%25%2B-success)](https://github.com)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-purple)](https://livewire.laravel.com)

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Testing](#-testing)
- [Architecture](#-architecture)
- [API Integration](#-api-integration)
- [Reflection](#-reflection)

---

## ✨ Features

### Core Functionality
- **AI-Powered Generation**: Leverages OpenAI GPT-4o-mini for intelligent description creation
- **Dual Tone Support**: Professional/Formal and Friendly/Casual writing styles
- **Real-Time Validation**: Comprehensive form validation with instant feedback
- **Retry Logic**: Exponential backoff for handling API failures gracefully

### Advanced Features
- **Description Scoring System** ⭐
  - Flesch Reading Ease algorithm for readability analysis
  - Multi-factor SEO strength calculation (7 factors, 100 points)
  - Detailed metrics: word count, sentences, keywords, character count

- **History Management** ⭐
  - Automatic saving to SQLite database
  - Browse 10 most recent generations
  - Load previous descriptions
  - Delete individual or all entries
  - Score tracking and display

### User Experience
- **Mobile-Responsive**: Optimized for all screen sizes
- **Copy to Clipboard**: One-click description copying
- **Loading States**: Clear visual feedback during operations
- **Error Handling**: User-friendly error messages

---

## 🛠️ Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Backend** | Laravel 11 | Application framework |
| **Frontend** | Livewire 3 | Reactive components |
| **Styling** | Tailwind CSS |
| **JavaScript** | Alpine.js | Minimal JS interactions |
| **Database** | SQLite | Lightweight persistence |
| **AI** | OpenAI GPT-4o-mini | Natural language generation |
| **Testing** | PEST | Modern PHP testing |

---

## 🚀 Installation

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM or Yarn
- SQLite3

### Step 1: Clone Repository
```bash
git clone https://github.com/dayo-code/ai-property-enhancer.git
cd ai-property-enhancer
```

### Step 2: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 3: Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite
```

### Step 4: Configure OpenAI
Open `.env` and add your OpenAI API key:
```env
OPENAI_API_KEY=sk-proj-your_actual_api_key_here
OPENAI_REQUEST_TIMEOUT=30
```

**Get API Key**: Visit [OpenAI Platform](https://platform.openai.com/api-keys)

### Step 5: Database Setup
```bash
# Run migrations
php artisan migrate
```

### Step 6: Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### Step 7: Start Server
```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

## ⚙️ Configuration

### OpenAI Settings
Configure in `.env`:
```env
# Required: Your OpenAI API key
OPENAI_API_KEY=sk-proj-xxxxx

# Optional: Request timeout (seconds)
OPENAI_REQUEST_TIMEOUT=30
```

### Database
Default: SQLite (used for simplicity)

---

## 📖 Usage

### Generating Descriptions

1. **Fill Property Details**
   - **Title**: Descriptive property name (e.g., "Luxury 5-Bedroom Duplex")
   - **Property Type**: House, Flat, Land, or Commercial
   - **Location**: Full address or area (e.g., "Lekki Phase 1, Lagos")
   - **Price**: Amount in Naira (numeric only)
   - **Key Features**: Comma-separated amenities (minimum 10 characters)

2. **Select Tone**
   - **Professional & Formal**: Elegant, sophisticated language for high-end properties
   - **Friendly & Casual**: Conversational, approachable style for family homes

3. **Generate**: Click "Generate Description" (takes 2-10 seconds)

4. **Review Quality Scores**
   - **Overall Score**: Weighted average (60% SEO, 40% readability)
   - **Readability**: Flesch Reading Ease (0-100, higher = easier)
   - **SEO Strength**: Multi-factor analysis (0-100, higher = better optimized)
   - **Metrics**: Word count, sentences, average length, keywords

5. **Actions**
   - **Regenerate**: Create alternative version
   - **Copy**: Copy description to clipboard
   - **Reset**: Clear form and start over

### Using History

- **Auto-Save**: Every generation saves automatically
- **Browse**: View 10 most recent in left sidebar
- **Load**: Click any entry to populate form
- **Delete**: Remove individual entries
- **Clear All**: Remove entire history

### Understanding Scores

#### Readability Score (Flesch Reading Ease)
```
90-100: Very Easy    (5th grade level)
80-89:  Easy         (6th grade)
70-79:  Fairly Easy  (7th grade)
60-69:  Standard     (8th-9th grade) ← Target
50-59:  Moderate     (10th-12th grade)
30-49:  Difficult    (College level)
0-29:   Very Hard    (Graduate level)
```

#### SEO Score Factors (Total: 100 points)
- Optimal Length (150-250 words): 30 points
- Property Type Mentioned: 15 points
- Location Mentioned: 15 points
- Value/Investment Keywords: 10 points
- Call-to-Action Present: 10 points
- Key Features Mentioned: 10 points
- Good Sentence Structure: 10 points

---

## 🧪 Testing

### Run Tests
```bash
# Run all tests (80+ tests)
php artisan test

# Run with coverage report
php artisan test --coverage

# Run specific suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific file
php artisan test tests/Unit/Services/DescriptionScoringServiceTest.php

# Run in parallel (faster)
php artisan test --parallel
```

### Test Suites
1. **PropertyDescriptionService** (15 tests)
   - Validation logic
   - Data handling
   - Edge cases

2. **DescriptionScoringService** (20 tests)
   - Readability algorithm
   - SEO scoring
   - Metrics calculation
   - Helper methods

3. **PropertyDescription Model** (19 tests)
   - CRUD operations
   - Query scopes
   - Accessors and mutators
   - Data integrity

4. **Livewire Component** (20+ tests)
   - Rendering
   - Form validation
   - History operations
   - User interactions

---

## 🏗️ Architecture

### Design Principles
- **SOLID Principles**: Single Responsibility, Open/Closed, Dependency Inversion
- **Clean Architecture**: Clear separation of concerns
- **DRY**: Reusable components and utilities
- **Service Layer**: Business logic isolated from controllers

### Project Structure
```
app/
├── Livewire/
│   └── PropertyDescriptionGenerator.php    # Main component
├── Models/
│   └── PropertyDescription.php             # Eloquent model
├── Providers/
│   └── PropertyServiceProvider.php         # Service registration
└── Services/
    ├── PropertyDescriptionService.php      # AI integration
    └── DescriptionScoringService.php       # Quality analysis

database/
├── factories/
│   └── PropertyDescriptionFactory.php      # Test data factory
└── migrations/
    └── xxxx_create_property_descriptions_table.php

resources/
└── views/
    ├── layouts/
    │   └── app.blade.php                   # Main layout
    └── livewire/
        └── property-description-generator.blade.php

tests/
├── Feature/
│   └── Livewire/
│       └── PropertyDescriptionGeneratorTest.php
└── Unit/
    ├── Models/
    │   └── PropertyDescriptionTest.php
    └── Services/
        ├── PropertyDescriptionServiceTest.php
        └── DescriptionScoringServiceTest.php
```

## 🤖 API Integration

### OpenAI Configuration
```php
Model: gpt-4o-mini              // Cost-effective and fast
Temperature: 0.7                // Balanced creativity
Max Tokens: 500                 // ~300-350 words output
Frequency Penalty: 0.3          // Reduce repetition
Presence Penalty: 0.2           // Topic diversity
```

### Prompt Engineering Strategy

#### User Prompt Structure
1. **Context**: Property details (type, location, price, features)
2. **Tone Instructions**: Formal or casual style guidance
3. **Requirements**:
   - Length: 150-250 words
   - SEO optimization
   - Natural keyword integration
   - Nigerian English and context
   - Lifestyle benefits focus
   - Call-to-action
   - No generic phrases

#### Error Handling
- **Retry Logic**: 3 attempts with exponential backoff
- **Timeout**: 30 seconds configurable
- **Graceful Degradation**: User-friendly error messages
- **Logging**: Comprehensive error tracking

## 💭 Reflection: How I Approached This Task
I started by splitting the project into clear phases: setting up the foundation, adding AI integration, building optional features, and finally testing. This made it easier to stay focused and keep the code clean throughout.
The toughest part was the scoring algorithms. For readability, I researched the Flesch Reading Ease formula and created a syllable counter using vowel group patterns, The SEO scoring was tricky because it has to balance things like length, keywords, and structure without feeling random, so I weighted each factor based on real SEO best practices and also used Claude AI as a coding partner to speed things up and explore solutions for the readability and SEO scoring. This shows how senior developement works today, using tools efficiently is just as important as strong coding ability.
The service layer setup was intentional as it helped to separate OpenAI integration, scoring logic, and data storage, making everything easier to test and maintain. Livewire hooks also came in handy for regenerating content when switching tones.
I wrote over 70 tests, which caught some edge cases early and really improved code quality and then using the factory pattern made generating test data super easy.

## 🚨 Troubleshooting

### OpenAI API Issues

**Invalid API Key**
```bash
# Verify key in .env
grep OPENAI_API_KEY .env

# Clear config cache
php artisan config:clear
```

### General Issues

**Clear Everything**
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
composer dump-autoload
```

---

## 📊 Performance

### Optimization
- Service layer caching (singleton)
- Database indexes on common queries
- Livewire reactivity for minimal re-renders
- SQLite for fast local operations
- In-memory database for testing

---

## 📄 License

This project was developed as part of a technical assessment for Dilmak Solutions / Property Centre.

---
