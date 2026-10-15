# Arunika — Architecture Guide

## Overview

Arunika is a Learning Operating System designed for Indonesian universities. It follows a modular monolith architecture within Laravel, using domain-organized services and actions.

## Architectural Patterns

### Request Flow

```
Browser → Middleware → Livewire/Controller → Action/Service → Model → Database
                                                  ↓
                                            Events → Listeners → Jobs (Queue)
```

### Layer Responsibilities

| Layer | Responsibility | Example |
|-------|---------------|---------|
| **Livewire Components** | UI interaction, form validation, rendering | `AssignmentList`, `QuizTaker` |
| **Controllers** | API endpoints, redirects | `Api\GradeController` |
| **Actions** | Single-purpose business operations | `CalculateOutcomeScores` |
| **Services** | Complex orchestration across multiple actions | `ObeCalculationService` |
| **Models** | Data access, relationships, scopes | `User`, `Class`, `Submission` |
| **Policies** | Authorization logic | `AssignmentPolicy` |
| **Jobs** | Async/heavy processing | `ProcessAiEmbedding` |
| **Events/Listeners** | Side effects, decoupled reactions | `SubmissionGraded → UpdateXp` |

### Key Interfaces

#### AiProvider (`app/Contracts/AiProviderInterface.php`)
```php
interface AiProviderInterface
{
    public function generateText(string $prompt, array $options = []): AiResponse;
    public function generateEmbedding(string $text): array;
    public function streamText(string $prompt, array $options = []): Generator;
}
```
- Default: `GeminiProvider`
- All calls routed through queue + rate limiter + usage logger

#### VectorStore (`app/Contracts/VectorStoreInterface.php`)
```php
interface VectorStoreInterface
{
    public function upsert(string $id, array $embedding, array $metadata = []): void;
    public function search(array $embedding, int $limit = 5, array $filters = []): Collection;
    public function delete(string $id): void;
}
```
- Default: `MysqlVectorStore` (BLOB storage + app-level cosine similarity)
- Optional: `QdrantVectorStore` for scale

### Multi-tenancy Strategy

Soft tenancy using `campus_id` column scoping:
- `BelongsToCampus` trait applies global scope
- `EnsureCampusScope` middleware sets current campus
- All queries automatically filtered by campus
- Upgradeable to `stancl/tenancy` if needed

### ID Strategy

Dual-column approach:
- `id` (bigint, auto-increment): Internal primary key, efficient for joins/indexes
- `ulid` (char 26, unique index): Public-facing identifier, sortable, no count exposure

### Authentication & Authorization

- **Authentication**: Laravel Fortify (web) + Sanctum (API) + Socialite (Google SSO)
- **Authorization**: spatie/laravel-permission (roles) + Laravel Policies (resource-level)
- **2FA**: TOTP-based via Fortify
- **Roles**: Super Admin, Admin Kampus, Kaprodi/GPM, Dosen, Asisten/TA, Dosen PA, Mahasiswa, Mentor Industri

### Data Conventions

- Timestamps stored in UTC, displayed in user timezone (default: Asia/Jakarta)
- Scores/grades use DECIMAL(8,2)
- Status fields use string columns with PHP Enum casts
- JSON columns for semi-structured data (settings, metadata)
- Soft deletes on major entities
- All sensitive actions logged via spatie/activitylog

## Module Map

```
Academic ─── Structure (Campus, Faculty, Program, Course, Class)
         └── Content (Meeting, Material, RPS)
         └── Assessment (Assignment, Quiz, Grade)
         └── OBE (CPL, CPMK, Mapping, Scores)

Engagement ── Gamification (XP, Badge, Quest, Leaderboard)
          └── Forum (Thread, Post, Vote)
          └── Study Room (Presence, Pomodoro)

Intelligence ── AI Copilot (RAG, Socratic, Generation)
            └── Analytics (Risk Score, Heatmap, Item Analysis)
            └── Early Warning (Nudge, Alert)

Operations ── Attendance (QR, Geofence, Anomaly)
          └── Projects (Kanban, Peer Review)
          └── MBKM (Logbook, Mentor, SKS)
          └── Credentials (Portfolio, Certificate, Verification)

Platform ── Auth (SSO, 2FA, Roles)
        └── Notification (In-app, Email, WhatsApp)
        └── Admin (Filament, Import/Export)
        └── PWA (Offline, Low-bandwidth)
```

## Infrastructure

- **Queue**: Redis + Laravel Horizon (monitoring)
- **Cache**: Redis (tagged caching for campus isolation)
- **Search**: Meilisearch via Laravel Scout
- **Real-time**: Laravel Reverb (WebSocket)
- **Monitoring**: Laravel Pulse
- **Feature Flags**: Laravel Pennant (per-campus toggles)
- **File Storage**: spatie/medialibrary with S3-compatible backend
