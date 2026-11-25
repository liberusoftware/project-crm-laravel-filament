# Usage Wallet and Rebilling · Filament

Filament presentation package for Usage Wallet and Rebilling. The root Composer manifest declares `liberusoftware/module-crm-usage-wallet-and-rebilling-filament`. Review its source and tests for implemented behavior, supported contracts and current release compatibility; the capability plan describes the intended scope.

## Develop locally

Use the PHP requirement in composer.json and a compatible Laravel/Filament host. From a clone of this package:

```bash
composer install
composer validate
composer run-script --list
```

The consuming application selects a reviewed package version and registers its public Filament plugin/resources through its existing panel composition. Inspect the package's actual src entry points; do not infer class names or install identities from the repository slug. Follow the [Composer module lifecycle](https://github.com/liberusoftware/documentation/blob/main/guides/COMPOSER.md) for the approved installer and module placement.

Installation, enablement, entitlements and authorization are separate. Keep tenant context, policies, validation, transactions and records in the canonical domain owner; presentation code calls reviewed public actions/queries. Verify allowed, denied and wrong-tenant behavior when changing protected workflows.

<!-- module-presentation-guide:begin -->
## Presentation architecture and project hosts

- Capability/source key: `liberusoftware/module-crm-usage-wallet-and-rebilling`.
- Actual Composer identity: `liberusoftware/module-crm-usage-wallet-and-rebilling-filament`.
- [Filament capability plan](https://github.com/liberusoftware/documentation/blob/main/projects/crm/filament/usage-wallet-and-rebilling.md) · [Filament standard](https://github.com/liberusoftware/documentation/blob/main/standards/FILAMENT.md) · [Module architecture](https://github.com/liberusoftware/documentation/blob/main/architecture/MODULES.md).

The application owns panel composition, routing, session integration and deployment. This module owns its focused presentation and delegates domain behavior to reviewed public owner contracts. Keep host App aliases, duplicate records and client-only authorization outside reusable package boundaries.

Documented consumer project families:

- [CRM](https://github.com/liberusoftware/project-crm-laravel-filament): Relationships, sales pipelines and customer service.

Other web, mobile and desktop presentations are listed in the [project catalog](https://github.com/liberusoftware/documentation/blob/main/projects/REPOSITORIES.md). Those skeletons provide starting structures; links do not establish feature parity, compatible releases or completed device/API integrations. Independent presentation packages keep their own Composer, npm or pub metadata.
<!-- module-presentation-guide:end -->
