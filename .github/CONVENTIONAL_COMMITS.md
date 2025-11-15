# Conventional Commit Guidelines

Use Conventional Commits to keep history consistent, human-friendly and automation-ready.

Rules

-   Format: `type(scope): description`
-   Allowed types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`
-   Use imperative mood (e.g., `Add` not `Added`)
-   Keep subject under 50 characters
-   Create separate commits for each distinct change type
-   If a commit contains multiple notable changes, include a body with bullet points describing them

Examples

-   `feat(api): add salinity validation to WaterQualityDataController`
-   `fix(readme): correct sqlite setup command`
-   `docs(contributing): add conventional commit guidelines`

Reference: https://www.freecodecamp.org/news/writing-good-commit-messages-a-practical-guide/

Optional enforcement

-   Add `commitlint` + `husky` or a CI commit-message check to validate messages automatically (recommended for larger contributor bases).

How Copilot should use this

-   When asked to generate commit messages for staged changes, produce messages that follow the rules above.
-   If multiple change types are staged, suggest separate commit messages (one per change type) and include a short body listing the grouped changes.
