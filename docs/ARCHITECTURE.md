# Enterprise CMS Architecture

## Folder Structure

```text
app/
├── Contracts/Repositories/       # Domain persistence contracts
├── Http/
│   ├── Controllers/Api/V1/       # Versioned REST controllers
│   ├── Requests/                 # Validation and request authorization
│   └── Resources/                # Stable API response representations
├── Models/                       # Eloquent entities and relationships
├── Notifications/               # Database and email notifications
├── Policies/                    # Model authorization rules
├── Repositories/Eloquent/       # MySQL-backed repository implementations
├── Services/                    # Application use cases and transactions
└── Support/                     # API envelopes and shared infrastructure
database/
├── factories/                   # Test data builders
├── migrations/                  # MySQL schema
└── seeders/                     # Roles, permissions, and bootstrap admin
docs/
├── API.md                       # Setup and usage guide
├── ARCHITECTURE.md              # This document
├── DATABASE.md                  # Schema reference
└── openapi.yaml                 # OpenAPI 3.1 contract
```

## Request Flow

`Route -> Sanctum -> Permission middleware -> FormRequest -> Policy -> Controller -> Service -> Repository -> Model`

Controllers only coordinate HTTP concerns. Services own use cases, transactions, file
operations, and relationship synchronization. Repositories own query construction and
persistence. Resources prevent database structure from leaking into the API contract.

## API Conventions

- Base URL: `/api/v1`
- Admin URL: `/api/v1/admin`
- Authentication: Sanctum bearer tokens
- Pagination: `data.items` and `data.meta`
- Errors: JSON envelope with `success`, `message`, and `errors`
- Dates: ISO 8601 UTC
- Deletes: soft delete for business records
- Authorization: Spatie permissions backed by Laravel policies and gates

