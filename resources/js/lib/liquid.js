import { prefersReducedMotion } from '@/lib/motion';

/**
 * v-liquid: a WebGL ripple/warp over a card's image while it's hovered.
 *
 * One shared canvas moves to whichever image is hovered and is laid exactly
 * over it (following parallax transforms), so there is only ever one GL
 * context. Mouse users only; touch and reduced-motion keep the plain image.
 */

const VERT = `
attribute vec2 aPos;
varying vec2 vUv;
void main() {
    vUv = vec2(aPos.x * 0.5 + 0.5, 0.5 - aPos.y * 0.5);
    gl_Position = vec4(aPos, 0.0, 1.0);
}`;

const FRAG = `
precision mediump float;
uniform sampler2D uTex;
uniform vec2 uMouse;     // 0..1 inside the image box
uniform vec2 uVel;       // smoothed mouse velocity
uniform vec2 uBox;       // box size in px
uniform vec2 uImg;       // natural image size
uniform float uStrength; // 0..1 hover amount
uniform float uTime;
varying vec2 vUv;

// object-fit: cover; object-position: top
vec2 cover(vec2 uv) {
    float boxA = uBox.x / uBox.y;
    float imgA = uImg.x / uImg.y;
    vec2 s = boxA > imgA ? vec2(1.0, imgA / boxA) : vec2(boxA / imgA, 1.0);
    return vec2(uv.x * s.x + (1.0 - s.x) * 0.5, uv.y * s.y);
}

void main() {
    float aspect = uBox.x / uBox.y;
    vec2 d = vUv - uMouse;
    d.x *= aspect;
    float dist = length(d);
    vec2 dir = dist > 0.0001 ? d / dist : vec2(0.0);

    float ripple = sin(dist * 34.0 - uTime * 4.5) * exp(-dist * 6.0);
    float bulge = exp(-dist * dist * 14.0);
    vec2 offset = dir * (ripple * 0.010 + bulge * 0.035) + uVel * bulge * 0.9;
    offset *= uStrength;
    offset.x /= aspect;

    float split = (length(uVel) * 0.6 + 0.003) * uStrength * bulge;
    vec2 base = vUv - offset;
    float r = texture2D(uTex, cover(base + vec2(split, 0.0))).r;
    vec2 gb = texture2D(uTex, cover(base)).gb;
    float b = texture2D(uTex, cover(base - vec2(split, 0.0))).b;

    vec3 color = vec3(r, gb.x, b);
    color += vec3(0.86, 0.08, 0.24) * bulge * 0.10 * uStrength; // faint crimson glow under the cursor
    gl_FragColor = vec4(color, 1.0);
}`;

let gl, program, canvas, uniforms;
const textures = new Map();
let state = null; // { el, img, tex, size }
const mouse = { x: 0.5, y: 0.5, tx: 0.5, ty: 0.5, vx: 0, vy: 0 };
let strength = 0;
let target = 0;
let raf = 0;
let last = 0;
const start = performance.now();

// Frame-rate independent smoothing: same feel at 30, 60 or 144 fps.
const ease = (rate, dt) => 1 - Math.pow(1 - rate, dt * 60);

const supported = () =>
    typeof window !== 'undefined' &&
    window.matchMedia('(hover: hover) and (pointer: fine)').matches &&
    !prefersReducedMotion();

function compile(type, source) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) throw new Error(gl.getShaderInfoLog(shader));
    return shader;
}

function init() {
    if (gl !== undefined) return !!gl;
    canvas = document.createElement('canvas');
    canvas.setAttribute('aria-hidden', 'true');
    Object.assign(canvas.style, { position: 'absolute', pointerEvents: 'none', zIndex: '2', opacity: '0', transition: 'opacity .35s ease' });
    gl = canvas.getContext('webgl', { premultipliedAlpha: false, antialias: false }) || null;
    if (!gl) return false;

    try {
        program = gl.createProgram();
        gl.attachShader(program, compile(gl.VERTEX_SHADER, VERT));
        gl.attachShader(program, compile(gl.FRAGMENT_SHADER, FRAG));
        gl.linkProgram(program);
        gl.useProgram(program);
    } catch {
        gl = null;
        return false;
    }

    const buffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
    const loc = gl.getAttribLocation(program, 'aPos');
    gl.enableVertexAttribArray(loc);
    gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

    uniforms = Object.fromEntries(['uTex', 'uMouse', 'uVel', 'uBox', 'uImg', 'uStrength', 'uTime'].map((n) => [n, gl.getUniformLocation(program, n)]));
    return true;
}

function texture(img) {
    const key = img.currentSrc || img.src;
    if (textures.has(key)) return textures.get(key);
    const tex = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, tex);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img);
    const entry = { tex, w: img.naturalWidth, h: img.naturalHeight };
    textures.set(key, entry);
    return entry;
}

/** Lay the canvas exactly over the <img>, including its transforms. */
function place() {
    const box = state.el.getBoundingClientRect();
    const r = state.img.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    Object.assign(canvas.style, { left: `${r.left - box.left}px`, top: `${r.top - box.top}px`, width: `${r.width}px`, height: `${r.height}px` });
    const w = Math.round(r.width * dpr);
    const h = Math.round(r.height * dpr);
    if (canvas.width !== w || canvas.height !== h) {
        canvas.width = w;
        canvas.height = h;
        gl.viewport(0, 0, w, h);
    }
    // The img's own CSS size (before transforms) decides the cover crop.
    state.size = [state.img.offsetWidth, state.img.offsetHeight];
}

function frame(now = performance.now()) {
    if (!state) return;
    const dt = Math.min(0.1, (now - (last || now)) / 1000) || 1 / 60;
    last = now;
    strength += (target - strength) * ease(0.08, dt);
    const px = mouse.x;
    const py = mouse.y;
    mouse.x += (mouse.tx - mouse.x) * ease(0.18, dt);
    mouse.y += (mouse.ty - mouse.y) * ease(0.18, dt);
    // Velocity per 60fps frame, so the warp strength doesn't depend on refresh rate.
    const scale = 1 / (dt * 60);
    mouse.vx += ((mouse.x - px) * scale - mouse.vx) * ease(0.25, dt);
    mouse.vy += ((mouse.y - py) * scale - mouse.vy) * ease(0.25, dt);

    place();
    gl.uniform1i(uniforms.uTex, 0);
    gl.uniform2f(uniforms.uMouse, mouse.x, mouse.y);
    gl.uniform2f(uniforms.uVel, mouse.vx, mouse.vy);
    gl.uniform2f(uniforms.uBox, state.size[0], state.size[1]);
    gl.uniform2f(uniforms.uImg, state.tex.w, state.tex.h);
    gl.uniform1f(uniforms.uStrength, strength);
    gl.uniform1f(uniforms.uTime, (performance.now() - start) / 1000);
    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);

    if (target === 0 && strength < 0.01) {
        canvas.style.opacity = '0';
        state = null;
        last = 0;
        return;
    }
    raf = requestAnimationFrame(frame);
}

function track(e, el) {
    const img = el.querySelector('img');
    if (!img) return;
    const r = img.getBoundingClientRect();
    mouse.tx = (e.clientX - r.left) / r.width;
    mouse.ty = (e.clientY - r.top) / r.height;
}

function enter(e) {
    const el = e.currentTarget;
    const img = el.querySelector('img');
    if (!img || !img.complete || !img.naturalWidth) return;
    if (new URL(img.currentSrc || img.src, location.href).origin !== location.origin) return; // WebGL needs same-origin pixels
    if (!init()) return;

    cancelAnimationFrame(raf);
    if (canvas.parentElement !== el) el.appendChild(canvas);
    track(e, el);
    if (!state || state.el !== el) {
        mouse.x = mouse.tx;
        mouse.y = mouse.ty;
        mouse.vx = mouse.vy = 0;
        strength = 0;
    }
    gl.activeTexture(gl.TEXTURE0);
    const tex = texture(img);
    gl.bindTexture(gl.TEXTURE_2D, tex.tex);
    state = { el, img, tex, size: [1, 1] };
    target = 1;
    canvas.style.opacity = '1';
    last = 0;
    raf = requestAnimationFrame(frame);
}

export const liquid = {
    mounted(el) {
        if (!supported()) return;
        el._liquid = {
            enter,
            move: (e) => state?.el === el && track(e, el),
            leave: () => state?.el === el && (target = 0),
        };
        el.addEventListener('pointerenter', el._liquid.enter);
        el.addEventListener('pointermove', el._liquid.move);
        el.addEventListener('pointerleave', el._liquid.leave);
    },
    unmounted(el) {
        if (!el._liquid) return;
        el.removeEventListener('pointerenter', el._liquid.enter);
        el.removeEventListener('pointermove', el._liquid.move);
        el.removeEventListener('pointerleave', el._liquid.leave);
        if (state?.el === el) {
            cancelAnimationFrame(raf);
            state = null;
            canvas?.remove();
        }
    },
};
