# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.


## Quick commands

- Install dependencies: composer install
- Run unit tests: composer test (runs phpunit)
- Run tests with coverage: composer run test:coverage
- Run static analysis: composer run phpstan (phpstan analyse --memory-limit=1G)
- Run code style checks: composer run cs-fixer:check
- Auto-fix code style: composer run cs-fixer:fix
- Full check: composer run check (runs cs-fixer check, phpstan, and test coverage)
- Lint (style + static analysis): composer run lint

To run a single PHPUnit test (by file):
- ./vendor/bin/phpunit path/to/TestFile.php

To run a single test method:
- ./vendor/bin/phpunit --filter testMethodName path/to/TestFile.php


## Project overview — high level

This repository provides a small PHP SDK for Staffbase plugin Single Sign-On (SSO) token parsing and validation.

High-level structure:

- src/: Core library code. Primary classes:
  - src/SSOToken.php — main JWT parsing & validation wrapper for plugin SSO tokens (uses lcobucci/jwt)
  - src/SSOTokenGenerator.php — utility to generate test tokens for unit tests
  - src/PluginSession.php — session wrapper for persisting SSO data between requests
  - src/RemoteCall/* — handlers and interfaces for app-initiated remote calls (delete-instance, etc.)
  - src/SSOData/* — data interfaces/traits for shared and SSO specific claims
  - src/Exceptions/* — domain exceptions (SSOAuthenticationException, SSOException, ...)
  - src/Validation/* — custom validation constraints used by php-jwt/token validation
  - src/AbstractToken.php — base token parsing/verification logic used by SSOToken and others

- test/: PHPUnit test suite for the library. Tests instantiate SSOToken, SSOTokenGenerator and validate behavior.

- composer.json: Composer metadata and scripts (lint, phpstan, tests, cs-fixer). Key runtime deps: lcobucci/jwt, lcobucci/clock.

- phpstan.neon.dist: phpstan configuration (analyse src and test at level 4 by default).

- README.md: user-facing documentation and examples (installation, usage examples, remote calls).


## CI and hooks

- GitHub Actions: a workflow badge exists in README, check .github/workflows for exact CI steps if needed.
- Composer hooks: composer.json registers post-install and post-update hooks for composer-git-hooks. Pre-commit hooks in composer.extra.hooks run "composer fix" and "composer phpstan".


## Notes for future Claude Code instances

- Use Composer scripts defined in composer.json for all common operations (tests, lint, phpstan, cs-fixer). Prefer the scripts so local project configuration is respected.
- When adding or editing PHP code, run CS Fixer and PHPStan locally (composer run fix; composer run phpstan) before running tests.
- Tests are run with phpunit (vendor/bin/phpunit). For debugging a single test, prefer running vendor/bin/phpunit --filter.

Key files to inspect for behavior changes:
- src/SSOToken.php: constructor, token parsing and validation flow
- src/AbstractToken.php: core token parsing/verification logic
- src/Validation/HasInstanceId.php: custom constraint used by SSOToken
- README.md and doc/api.md: user-visible behavior and API


## When editing code

- Prefer editing existing files rather than creating new ones unless a new module is required.
- Follow PSR-4 autoloading in composer.json (namespace Staffbase\plugins\sdk\ -> src/)
- Keep phpstan.neon.dist level consideration in mind


## Contact and references

- Project homepage: https://github.com/Staffbase/plugins-sdk-php
- Composer entry: composer.json

