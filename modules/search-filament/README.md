# Search Filament

This optional package is the bounded filament adapter for liberusoftware/search. It presents only the matching core capability; authorization, validation, tenancy, persistence, and business rules remain in the core package.

## Installation

composer require liberusoftware/search-filament

GitHub repository: https://github.com/liberusoftware/module-search-filament.

Compatibility: PHP 8.5+, Laravel 13+, Filament 5.

<!-- module-presentation-guide:begin -->
## Presentation architecture and project hosts

- Capability/source key: `liberusoftware/search`.
- Actual Composer identity: `liberusoftware/search-filament`.
- [Filament capability plan](https://github.com/liberusoftware/documentation/blob/main/projects/boilerplate/filament/search.md) · [Filament standard](https://github.com/liberusoftware/documentation/blob/main/standards/FILAMENT.md) · [Module architecture](https://github.com/liberusoftware/documentation/blob/main/architecture/MODULES.md).

The application owns panel composition, routing, session integration and deployment. This module owns its focused presentation and delegates domain behavior to reviewed public owner contracts. Keep host App aliases, duplicate records and client-only authorization outside reusable package boundaries.

Documented consumer project families:

- [Boilerplate](https://github.com/liberusoftware/project-boilerplate-laravel-filament): Shared application foundation and module composition.
- [Accounting ERP](https://github.com/liberusoftware/project-accounting-erp-laravel-filament): Ledgers, tax, reporting and finance workflows.
- [Automation](https://github.com/liberusoftware/project-automation-laravel-filament): AI gateways, approvals, rules and integrations.
- [Billing](https://github.com/liberusoftware/project-billing-laravel-filament): Subscriptions, pricing, payments and provisioning.
- [Browser Game](https://github.com/liberusoftware/project-browser-game-laravel-filament): Game worlds, players and community workflows.
- [Control Panel](https://github.com/liberusoftware/project-control-panel-laravel-filament): Infrastructure and hosting operations.
- [CRM](https://github.com/liberusoftware/project-crm-laravel-filament): Relationships, sales pipelines and customer service.
- [Genealogy](https://github.com/liberusoftware/project-genealogy-laravel-filament): Family trees, research, evidence and collaboration.
- [Maintenance](https://github.com/liberusoftware/project-maintenance-laravel-filament): Assets, work orders, schedules and field operations.
- [Real Estate](https://github.com/liberusoftware/project-real-estate-laravel-filament): Listings, sales, lettings and agency operations.
- [Social Network](https://github.com/liberusoftware/project-social-network-laravel-filament): Profiles, feeds, communities and moderation.

Other web, mobile and desktop presentations are listed in the [project catalog](https://github.com/liberusoftware/documentation/blob/main/projects/REPOSITORIES.md). Those skeletons provide starting structures; links do not establish feature parity, compatible releases or completed device/API integrations. Independent presentation packages keep their own Composer, npm or pub metadata.
<!-- module-presentation-guide:end -->
