# Contributing

Thanks for contributing! This project follows standard GitHub workflows.

How to contribute

1. Fork the repository and create a feature branch from main:

   git checkout -b feature/your-change

2. Make changes and run tests locally:

   composer install
   npm install
   composer test

3. Keep commits focused and include helpful messages.

4. Push and open a pull request. Describe the change, testing steps, and any migrations or environment changes.

Branching and PRs

- Use descriptive branch names: feature/, fix/, docs/
- Target branch: main (or as project policy requires)
- Run tests and ensure linting/pint passes before requesting review.

Coding standards

- Follow PSR-12 for PHP
- Use Laravel conventions for controllers, services and models
- Run Pint where appropriate: vendor/bin/pint

Database changes

- Add migrations for schema changes and include seeders if necessary.
- Describe migration steps in the PR description if manual steps are required.

Adding documentation

- Add docs to docs/ and keep README updated for high-level project changes.

Contact and support

- Use Issues for bugs and feature requests.
- Use Pull Requests for code changes.
