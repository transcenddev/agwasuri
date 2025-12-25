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

Punctuation & style notes

-   Avoid special punctuation in the subject line such as the em-dash (—) or fancy quotation marks. Use plain ASCII characters only (for example, use `-` not `—`). This keeps commit messages portable across terminals, CI tools, and changelog parsers.
-   Keep the subject line short (<= 50 chars) and on a single line. Use the body to explain details when needed.

Custom types and team tags

-   It's fine to add a small set of repository-specific types if your team agrees (for example `ci`, `build`, `perf`, `infra`, `ops`, `meta`).
-   Avoid adding overly personal or ambiguous types (for example `dad`) unless every contributor understands and expects that meaning. Custom types should map clearly to actions that automation or human reviewers can treat consistently.

Ideas for useful custom types

-   `ci`: changes to CI config, workflows
-   `build`: changes to build system / dependencies
-   `infra` or `ops`: infrastructure, deployment, runtime configuration
-   `perf`: performance improvements
-   `meta`: repository metadata, contributor docs, templates

Examples (good):

-   `feat(api): add salinity validation to WaterQualityDataController`
-   `fix(readme): correct sqlite setup command`
-   `ci(github): add commitlint check to workflow`
-   `perf(db): speed up average computation query`

If you want a playful or team-internal tag

-   You can add a playful tag like `dad` but treat it as an alias for a clear type (for example `docs` or `meta`). Document the alias in this file so external contributors and tooling understand it. Example: `docs(dad): add playful examples for commit messages` — but prefer clarity over whimsy for public commits.
