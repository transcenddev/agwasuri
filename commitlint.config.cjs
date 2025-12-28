module.exports = {
    extends: ["@commitlint/config-conventional"],
    rules: {
        // Relax limits to avoid failing existing PRs with long descriptions
        'header-max-length': [2, 'always', 200],
        'body-max-line-length': [2, 'always', 200],
        // Allow WIP as a non-fatal type and don't enforce strict subject-case
        'type-enum': [1, 'always', [
            'build', 'chore', 'ci', 'docs', 'feat', 'fix', 'perf', 'refactor', 'revert', 'style', 'test', 'WIP'
        ]],
        'subject-case': [0, 'always', []],
    },
};
