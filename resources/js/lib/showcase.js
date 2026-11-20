/**
 * Draws a project "showcase" cover: three browser windows with screenshots
 * floating over a dark background lit by the project's colour.
 * Everything is drawn at 1920×1200 and scaled by CSS for previews.
 */
export const WIDTH = 1920;
export const HEIGHT = 1200;

/** Window positions, matched to the studio's hand-made covers. */
export const WINDOWS = [
    { key: 'main', name: 'Main window', x: 393, y: 181, w: 1134, h: 718, bar: 50, dot: 7.5 },
    { key: 'left', name: 'Bottom left', x: 181, y: 763, w: 526, h: 292, bar: 42, dot: 7 },
    { key: 'right', name: 'Bottom right', x: 1237, y: 830, w: 502, h: 249, bar: 42, dot: 7 },
];

const SITE_ACCENT = '#DC143C';
const LIGHTS = ['#FF5F57', '#FEBC2E', '#28C840'];

/**
 * @param {HTMLCanvasElement} canvas
 * @param {{ images: (HTMLImageElement|null)[], labels: string[], focus: number[], color: string }} opts
 */
export function renderShowcase(canvas, { images = [], labels = [], focus = [], color = SITE_ACCENT }) {
    canvas.width = WIDTH;
    canvas.height = HEIGHT;
    const ctx = canvas.getContext('2d');
    const rgb = hexToRgb(color) ?? hexToRgb(SITE_ACCENT);

    background(ctx, rgb);

    // With no small windows, centre the main one on its own.
    const solo = !images[1] && !images[2];
    WINDOWS.forEach((win, i) => {
        if (!images[i]) return;
        const box = solo && i === 0 ? { ...win, x: (WIDTH - win.w) / 2, y: (HEIGHT - win.h) / 2 } : win;
        browserWindow(ctx, box, images[i], labels[i] ?? '', focus[i] ?? 0);
    });

    grain(ctx);
    return canvas;
}

function background(ctx, [r, g, b]) {
    ctx.fillStyle = '#0B0B0D';
    ctx.fillRect(0, 0, WIDTH, HEIGHT);

    const glow = (x, y, radius, alpha) => {
        const grad = ctx.createRadialGradient(x, y, 0, x, y, radius);
        grad.addColorStop(0, `rgba(${r},${g},${b},${alpha})`);
        grad.addColorStop(0.45, `rgba(${r},${g},${b},${alpha * 0.45})`);
        grad.addColorStop(1, `rgba(${r},${g},${b},0)`);
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, WIDTH, HEIGHT);
    };
    glow(WIDTH * 0.98, -140, 1250, 0.62); // top right
    glow(WIDTH * 0.4, HEIGHT + 220, 1150, 0.5); // bottom, behind the small windows
    glow(-160, HEIGHT * 0.55, 700, 0.14); // faint left edge

    // Darken the top-left corner a touch, like the hand-made covers.
    const shade = ctx.createRadialGradient(0, 0, 0, 0, 0, 900);
    shade.addColorStop(0, 'rgba(0,0,0,0.55)');
    shade.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = shade;
    ctx.fillRect(0, 0, WIDTH, HEIGHT);
}

function browserWindow(ctx, { x, y, w, h, bar, dot }, image, label, focus) {
    const radius = 18;

    // Soft drop shadow.
    ctx.save();
    ctx.shadowColor = 'rgba(0,0,0,0.6)';
    ctx.shadowBlur = 70;
    ctx.shadowOffsetY = 28;
    roundRect(ctx, x, y, w, h, radius);
    ctx.fillStyle = '#18181B';
    ctx.fill();
    ctx.restore();

    ctx.save();
    roundRect(ctx, x, y, w, h, radius);
    ctx.clip();

    // Title bar.
    const barFill = ctx.createLinearGradient(0, y, 0, y + bar);
    barFill.addColorStop(0, '#232327');
    barFill.addColorStop(1, '#1C1C1F');
    ctx.fillStyle = barFill;
    ctx.fillRect(x, y, w, bar);

    // Screenshot, filling the window and anchored by `focus` (0 top … 1 bottom).
    drawCover(ctx, image, x, y + bar, w, h - bar, focus);
    ctx.fillStyle = 'rgba(0,0,0,0.35)';
    ctx.fillRect(x, y + bar, w, 1);
    ctx.restore();

    // Traffic lights.
    LIGHTS.forEach((c, i) => {
        ctx.beginPath();
        ctx.arc(x + 24 + i * 25, y + bar / 2, dot, 0, Math.PI * 2);
        ctx.fillStyle = c;
        ctx.fill();
    });

    // Address pill with the window's title.
    if (label.trim()) {
        const pw = w * 0.6;
        const ph = Math.round(bar * 0.58);
        const px = x + (w - pw) / 2;
        const py = y + (bar - ph) / 2;
        roundRect(ctx, px, py, pw, ph, ph / 2);
        ctx.fillStyle = 'rgba(255,255,255,0.06)';
        ctx.fill();
        ctx.font = `500 ${Math.round(bar * 0.43)}px "Onest Variable", "Geist Variable", system-ui, sans-serif`;
        ctx.fillStyle = '#C9C9CE';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(ellipsize(ctx, label.trim(), pw - 28), x + w / 2, py + ph / 2 + 1);
    }

    // Light outline, as on a real window.
    roundRect(ctx, x + 1, y + 1, w - 2, h - 2, radius - 1);
    ctx.lineWidth = 2;
    ctx.strokeStyle = 'rgba(232,232,236,0.62)';
    ctx.stroke();
}

function drawCover(ctx, img, x, y, w, h, focus) {
    const iw = img.naturalWidth || img.width;
    const ih = img.naturalHeight || img.height;
    if (!iw || !ih) return;
    const scale = Math.max(w / iw, h / ih);
    const sw = w / scale;
    const sh = h / scale;
    const sx = (iw - sw) / 2;
    const sy = (ih - sh) * Math.min(1, Math.max(0, focus));
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
}

/** Fine noise so the big gradients don't band once compressed. */
function grain(ctx) {
    const tile = document.createElement('canvas');
    tile.width = tile.height = 128;
    const t = tile.getContext('2d');
    const data = t.createImageData(128, 128);
    for (let i = 0; i < data.data.length; i += 4) {
        const v = Math.random() * 255;
        data.data[i] = data.data[i + 1] = data.data[i + 2] = v;
        data.data[i + 3] = 10;
    }
    t.putImageData(data, 0, 0);
    ctx.fillStyle = ctx.createPattern(tile, 'repeat');
    ctx.fillRect(0, 0, WIDTH, HEIGHT);
}

function roundRect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
}

function ellipsize(ctx, text, max) {
    if (ctx.measureText(text).width <= max) return text;
    let t = text;
    while (t.length > 1 && ctx.measureText(`${t}…`).width > max) t = t.slice(0, -1);
    return `${t.trimEnd()}…`;
}

export function hexToRgb(hex) {
    const m = /^#?([0-9a-f]{6})$/i.exec(hex ?? '');
    if (!m) return null;
    const n = parseInt(m[1], 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
}

/**
 * The most prominent vivid colour in an image, or null if it's mostly grey.
 * Mirrors App\Support\BrandColor::fromImage so previews match the server.
 */
export function brandColour(img) {
    const w = 64;
    const h = Math.max(1, Math.round((img.naturalHeight / img.naturalWidth) * w));
    const c = document.createElement('canvas');
    c.width = w;
    c.height = h;
    const ctx = c.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(img, 0, 0, w, h);
    const px = ctx.getImageData(0, 0, w, h).data;

    const buckets = new Map();
    for (let i = 0; i < px.length; i += 4) {
        const [hu, s, l] = toHsl(px[i], px[i + 1], px[i + 2]);
        if (s < 0.35 || l < 0.18 || l > 0.85) continue;
        const weight = s * (1 - Math.abs(l - 0.5) * 2);
        const k = Math.floor(hu * 18) % 18;
        const b = buckets.get(k) ?? { w: 0, x: 0, y: 0, s: 0 };
        b.w += weight;
        b.x += Math.cos(hu * 2 * Math.PI) * weight;
        b.y += Math.sin(hu * 2 * Math.PI) * weight;
        b.s += s * weight;
        buckets.set(k, b);
    }

    const top = [...buckets.values()].sort((a, b) => b.w - a.w)[0];
    if (!top || top.w < 6) return null;
    const hue = (Math.atan2(top.y, top.x) / (2 * Math.PI) + 1) % 1;
    return fromHsl(hue, Math.min(0.85, Math.max(0.6, top.s / top.w)), 0.55);
}

function toHsl(r, g, b) {
    r /= 255;
    g /= 255;
    b /= 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const l = (max + min) / 2;
    if (max === min) return [0, 0, l];
    const d = max - min;
    const s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    const h = max === r ? (g - b) / d + (g < b ? 6 : 0) : max === g ? (b - r) / d + 2 : (r - g) / d + 4;
    return [h / 6, s, l];
}

function fromHsl(h, s, l) {
    const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
    const p = 2 * l - q;
    const channel = (t) => {
        t = (t + 1) % 1;
        const v = t < 1 / 6 ? p + (q - p) * 6 * t : t < 1 / 2 ? q : t < 2 / 3 ? p + (q - p) * (2 / 3 - t) * 6 : p;
        return Math.round(v * 255).toString(16).padStart(2, '0');
    };
    return `#${channel(h + 1 / 3)}${channel(h)}${channel(h - 1 / 3)}`.toUpperCase();
}
