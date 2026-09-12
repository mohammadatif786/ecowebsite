# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

LinkupFinal is a social networking and event management platform built with Laravel (PHP backend), Vue.js 3 + TypeScript (frontend), and Inertia.js for seamless SPA functionality. The application combines social matching features, event management, live streaming, wallet system, and e-commerce capabilities.

## Architecture

### Backend Structure (Laravel)
- **Framework**: Laravel 12 with PHP 8.2+ requirement  
- **Architecture Pattern**: MVC with additional service/repository layers
- **Key Features**: Multi-role system (Users, Organizers, Admins), Wallet system with transactions, Real-time features with Pusher, Payment processing with Stripe/Cashier, QR code generation and PDF generation

**Route Organization:**
- `routes/web.php` - Frontend user routes with authentication middleware
- `routes/admin.php` - Admin panel routes
- `routes/organizer.php` - Event organizer specific routes  
- `routes/api.php` - API endpoints
- `routes/auth.php` - Authentication routes
- `routes/settings.php` - Application settings

**Application Structure:**
- `app/Http/Controllers/` - Controllers organized by area (Frontend/, Admin/, etc.)
- `app/Models/` - Eloquent models 
- `app/Services/` - Business logic services
- `app/Helpers/` - Helper utilities
- `app/CentralLogics/` - Core business logic
- `app/Events/` & `app/Listeners/` - Event-driven architecture
- `database/migrations/` - Database schema
- `database/seeders/` - Database seeding

### Frontend Structure (Vue.js + Inertia.js)
- **Framework**: Vue.js 3 with Composition API and TypeScript
- **Bundler**: Vite with Laravel integration
- **CSS**: Tailwind CSS 4.x
- **Components**: Reka UI component library, custom components

**Frontend Organization:**
- `resources/js/app.ts` - Main application entry point
- `resources/js/pages/` - Inertia.js page components (auto-resolved)
- `resources/js/components/` - Reusable Vue components
- `resources/js/layouts/` - Layout components
- `resources/js/composables/` - Vue composition functions
- `resources/js/services/` - Frontend service layer
- `resources/js/types/` - TypeScript type definitions
- `resources/js/lib/` - Utility libraries

## Development Commands

### Backend (PHP/Laravel)
```bash
# Install PHP dependencies
composer install

# Start development server with queue worker and frontend
composer run dev

# Start development with SSR support
composer run dev:ssr

# Run tests with Pest
composer run test
# Or directly:
php artisan test

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear caches
php artisan optimize:clear

# Queue commands
php artisan queue:work
php artisan queue:listen --tries=1

# Code formatting with Pint
./vendor/bin/pint

# Start Artisan console
php artisan tinker
```

### Frontend (Node.js/Vue.js)
```bash
# Install Node dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Build with SSR
npm run build:ssr

# Code formatting
npm run format
npm run format:check

# Linting
npm run lint
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test tests/Feature
php artisan test tests/Unit

# Run specific test file
php artisan test tests/Feature/ExampleTest.php
```

## Key Technical Concepts

### Authentication & Authorization
- Multi-role system with Spatie Laravel Permission
- Wizard-based onboarding flow for new users
- KYC verification system for event organizers
- Sanctum for API authentication

### Real-time Features
- Laravel Echo + Pusher for WebSocket connections
- Live streaming with Gumlet integration
- Real-time chat system
- Push notifications (OneSignal integration)

### Payment & Wallet System
- Internal wallet system with 021/laravel-wallet package
- Stripe integration for payments
- Multi-currency coin system
- Request money functionality between users

### Event Management
- Complex event creation with tickets, QR codes
- Event invitation system
- Favorite events functionality
- Booking management with e-tickets

### Frontend State Management
- Inertia.js for seamless SPA experience
- Vue.js 3 Composition API
- VueUse for composable utilities
- Form validation with Vuelidate

## Environment Setup

### Required Services
- **Database**: MySQL/MariaDB (configured in XAMPP)
- **Queue**: Database or Redis for production
- **Broadcasting**: Pusher for real-time features
- **Storage**: Local storage or S3-compatible service
- **Mail**: SMTP configuration required
- **Payment**: Stripe keys for payment processing

### Key Environment Variables
```
DB_CONNECTION=mysql
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
STRIPE_KEY=
STRIPE_SECRET=
MAIL_MAILER=smtp
```

## Common Development Patterns

### Creating New Features
1. Create migration: `php artisan make:migration create_table_name`
2. Create model: `php artisan make:model ModelName`
3. Create controller: `php artisan make:controller FeatureController`
4. Add routes to appropriate route file
5. Create Vue.js page component in `resources/js/pages/`
6. Add TypeScript types in `resources/js/types/`

### Adding New Vue Components
- Place reusable components in `resources/js/components/`
- Use TypeScript with `<script setup lang="ts">`
- Follow existing component patterns with props/emits typing
- Use Tailwind CSS classes for styling

### Database Operations
- Always use migrations for schema changes
- Create seeders for test data: `php artisan make:seeder`
- Use model factories for testing: `php artisan make:factory`

### Event-Driven Architecture
- Events in `app/Events/`
- Listeners in `app/Listeners/`
- Register in `EventServiceProvider`

### API Development
- RESTful routes in `routes/api.php`
- API Resources for response formatting
- Rate limiting and authentication middleware