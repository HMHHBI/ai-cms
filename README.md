# 🚀 AI-Powered SaaS Support CRM

A modern, high-performance Support Ticket Management System built with Laravel 12, Livewire Volt, and Gemini AI.

## Features

- AI Auto-Drafts using Gemini 2.0 Flash
- Sentiment Analysis (Positive, Neutral, Negative)
- Multi-Tenancy (Company-based system)
- AI Business Insights for admins
- Web Dashboard (Livewire Volt SPA-like)
- REST API secured with Laravel Sanctum
- Service Layer Architecture

## Tech Stack

Backend: Laravel 12 (PHP 8.4)
Frontend: Livewire Volt (TALL Stack)
Database: MySQL
AI: Google Gemini API
Auth: Laravel Sanctum
Styling: Tailwind CSS

## API Endpoints

POST /api/register - Create User & Company (No Auth)
POST /api/login - Get Token (No Auth)
GET /api/tickets - List Tickets (Auth Required)
POST /api/tickets - Create Ticket (Auth Required)

Postman collection available in /api-docs folder.

## Installation

git clone https://github.com/hmhhbi/ai-cms.git
cd ai-cms

composer install
npm install
npm run dev

## Setup

Copy .env.example to .env and add:

GEMINI_API_KEY=your_api_key_here

Run migrations:

php artisan migrate

## Author

Hassan - Laravel & AI Specialist