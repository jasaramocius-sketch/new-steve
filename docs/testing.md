# Testing

This repository uses PHPUnit for automated testing and includes Laravel test scaffolding.

Run tests

- Run full test suite:

  composer test

- You can also run PHPUnit directly:

  vendor/bin/phpunit

Test configuration

- phpunit.xml at the project root controls test settings.

Writing tests

- Feature tests: tests/Feature
- Unit tests: tests/Unit
- Use factories under database/factories for test data generation.

Common commands

- Run a single test file:

  vendor/bin/phpunit tests/Feature/ExampleTest.php

- Run tests with coverage (requires Xdebug or PCOV):

  vendor/bin/phpunit --coverage-html coverage

CI

- Ensure tests run in CI (GitHub Actions or other). Add composer install and test steps to pipelines.
