#!/usr/bin/env node
const fs = require('fs');
const path = process.argv[2];
if (!path) process.exit(0);
try {
  const content = fs.readFileSync(path, 'utf8');
  if (content.includes('—')) {
    const cleaned = content.replace(/—/g, '-');
    fs.writeFileSync(path, cleaned, 'utf8');
    console.log('[sanitize-commit-msg] Replaced em-dash with hyphen in commit message');
  }
} catch (e) {
  // ignore errors
}
