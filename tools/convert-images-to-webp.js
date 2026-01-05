const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..', 'public');
const exts = ['.png', '.jpg', '.jpeg'];
const quality = 85;

function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    const full = path.join(dir, file);
    const stat = fs.statSync(full);
    if (stat && stat.isDirectory()) {
      // skip node_modules, vendor, build folders
      if (['node_modules', 'vendor', 'build'].includes(path.basename(full))) return;
      results = results.concat(walk(full));
    } else {
      results.push(full);
    }
  });
  return results;
}

function isImage(file) {
  const e = path.extname(file).toLowerCase();
  return exts.includes(e);
}

async function convert(file) {
  const out = file.replace(/\.[^.]+$/, '.webp');
  if (fs.existsSync(out)) return; // don't overwrite

  try {
    await sharp(file).webp({ quality }).toFile(out);
    console.log('Converted:', path.relative(root, file), '→', path.relative(root, out));
  } catch (err) {
    console.error('Failed:', file, err.message);
  }
}

async function main() {
  const files = walk(root).filter(isImage);
  for (const f of files) {
    await convert(f);
  }
}

main().catch(err => {
  console.error(err);
  process.exit(1);
});
