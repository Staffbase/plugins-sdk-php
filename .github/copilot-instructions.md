# GitHub Copilot instructions for plugins-sdk-php

This guidance file helps Copilot generate code aligned with project standards and domain design for the Staffbase Plugin SDK for PHP.

## General Copilot goals
- Follow project coding style and PSR-4 namespace conventions.
- Prefer using and extending the existing src/SSOToken, PluginSession, and RemoteCall infrastructure.
- When creating new code, integrate with provided interfaces, traits, and classes (see src/SSOData, src/RemoteCall, src/Exceptions).
- All code should work with PHP 8.3, strict_types, and Composer autoloading.
- When suggesting test code, match the structure of test/ files and use PHPUnit 10+ only.

## Style and static analysis
- Conform to rules in .php-cs-fixer.dist.php (array_syntax short, strict_types, remove unused imports, use strict parameters).
- Code should pass `composer run cs-fixer:check` and `composer run lint` on commit.
- Code should pass PHPStan at level 4 (phpstan.neon.dist), including for new types, traits, and test cases.

## Domain guidance
- For SSO authentication, use and extend SSOToken, PluginSession, and SSODataTrait as the foundation — avoid duplicating token logic.
- Remote call support should use AbstractRemoteCallHandler or interfaces from src/RemoteCall if you need plugin event handling (e.g., deletion).
- When handling sessions, use PluginSession and its methods for SSO data. Avoid manual $_SESSION logic except for advanced cases.
- Exceptions should inherit src/Exceptions base classes as relevant.

## Recommended code generation practices
- Prefer composition and interface-driven design (see src/SSOData, src/RemoteCall, src/Exceptions).
- Follow README.md example patterns for token creation, session management, and error handling.
- Place new classes in src/ with Staffbase\plugins\sdk\ namespace; place tests in test/ with Staffbase\plugins\test\ namespace.

## Copilot DON'Ts
- Do not add new dependencies unless absolutely required and justified in code comments.
- Do not use deprecated PHP practices or legacy global state.
- Do not bypass static analysis rules for quick fixes.
- Do not create code outside of src/ and test/ unless explicitly requested. Never create example or playground code that is not integrated into the SDK or its tests.

## Documentation
- Consult CLAUDE.md at project root for further architectural guidance and standard commands.
- Reference README.md and inline docblocks for API documentation style.
