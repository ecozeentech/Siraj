// Generates PWA / favicon PNGs from public/brand/siraj-mark.svg (run: node scripts/make-icons.mjs)
import sharp from 'sharp';
import { readFileSync, mkdirSync } from 'node:fs';

const mark = readFileSync('public/brand/siraj-mark.svg');
mkdirSync('public/icons', { recursive: true });

const bg = (size, radius) => Buffer.from(
  `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}">
     <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#014e2e"/><stop offset="1" stop-color="#00281a"/></linearGradient></defs>
     <rect width="${size}" height="${size}" rx="${radius}" fill="url(#g)"/>
   </svg>`);

async function icon(file, size, { padding = 0.2, radius = 0 } = {}) {
  const inner = Math.round(size * (1 - padding * 2));
  const m = await sharp(mark, { density: 600 }).resize({ height: inner, fit: 'inside' }).png().toBuffer();
  await sharp(bg(size, radius)).composite([{ input: m, gravity: 'center' }]).png().toFile(file);
}

await icon('public/icons/icon-192.png', 192, { padding: 0.16, radius: 40 });
await icon('public/icons/icon-512.png', 512, { padding: 0.16, radius: 108 });
await icon('public/icons/icon-maskable-512.png', 512, { padding: 0.24, radius: 0 });
await icon('public/icons/apple-touch-icon.png', 180, { padding: 0.16, radius: 0 });
await icon('public/icons/favicon-32.png', 32, { padding: 0.08, radius: 7 });
await icon('public/icons/shortcut-quran.png', 96, { padding: 0.2, radius: 20 });
console.log('icons generated');
