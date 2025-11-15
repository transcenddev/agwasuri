# Contributing to Agwasuri

Thanks for your interest in contributing! This file mirrors the project's contributor guidance and lives in `.github/` so it appears in GitHub's UI.

## How to contribute

1. Fork the repository and create a feature branch:

```bash
git checkout -b feat/short-description
```

2. Make small, focused changes. Run tests locally and ensure new code is covered by tests when applicable.

3. Commit messages:

-   Use descriptive commit messages. Example: `feat: add endpoint validation for salinity`.
-   Keep commits small and atomic.

4. Open a Pull Request against `main` or the target branch and include:

-   A short description of the change
-   Any relevant screenshots (for UI changes)
-   Reproduction steps for bugs
-   Which tests you ran locally

## Tests & linters

-   Run tests with Pest:

```pwsh
php artisan test
# or
./vendor/bin/pest
```

-   Format or lint code if applicable before opening a PR. Follow existing project style.

## Branching & workflow

-   Branch names: `feat/`, `fix/`, `chore/`, `refactor/`.
-   Open a PR from your fork. Maintainers will review and run CI (tests + static analysis) before merging.

## Code review checklist (for contributors)

-   [ ] Tests pass locally
-   [ ] New behaviour has tests, or you explain why tests are unnecessary
-   [ ] Linting/formatting follows project conventions
-   [ ] PR description includes motivation and testing steps

## Getting help

If you're unsure where to start, open an issue describing your idea or ask for an onboarding task.

Thanks maintainers ❤️ contributions!
