module.exports = {
    extends: ["@commitlint/config-conventional"],
    rules: {
        // Fail if header contains the em-dash character
        "header-pattern": [2, "always", "^[^—]+$"],
    },
};
