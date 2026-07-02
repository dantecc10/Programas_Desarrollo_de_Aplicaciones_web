import puppeteer from "puppeteer";
import { mkdirSync } from "fs";

const BASE = "http://localhost:8080";
const OUT = "docs/assets/img";
mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({ headless: true, args: ["--no-sandbox"] });
const page = await browser.newPage();
await page.setViewport({ width: 1366, height: 768 });

async function snap(name, url, waitFor, pre) {
  if (pre) await pre(page);
  await page.goto(url, { waitUntil: "networkidle0", timeout: 15000 });
  if (waitFor) await page.waitForSelector(waitFor, { timeout: 5000 }).catch(() => {});
  await page.screenshot({ path: `${OUT}/${name}.png`, fullPage: true });
  console.log(`  ✓ ${name}.png`);
}

// ── Public pages ──
console.log("=== Public pages ===");
await snap("01-homepage", `${BASE}/`);
await snap("02-registro", `${BASE}/pages/registro.php`);
await snap("03-login", `${BASE}/pages/login.php`);
await snap("04-canchas", `${BASE}/pages/canchas.php`);
await snap("05-reservar", `${BASE}/pages/reservar.php?cancha_id=1`, "#calendar");
await snap("20-recuperar", `${BASE}/pages/recuperar.php`);

// ── Login ──
console.log("=== Login as admin ===");
await page.goto(`${BASE}/api/login.php`, { waitUntil: "networkidle0" });
await page.type('input[name="email"]', "admin@canchas.com");
await page.type('input[name="password"]', "password");
await Promise.all([
  page.waitForNavigation({ waitUntil: "networkidle0", timeout: 10000 }),
  page.click('button[type="submit"]'),
]);
console.log("  ✓ Logged in");

// ── Authenticated pages (user) ──
console.log("=== User pages ===");
await snap("07-mis-reservaciones", `${BASE}/pages/mis_reservaciones.php`);
await snap("08-historial", `${BASE}/pages/historial.php`);
await snap("09-perfil", `${BASE}/pages/perfil.php`);

// ── Admin pages ──
console.log("=== Admin pages ===");
await snap("10-admin-dashboard", `${BASE}/admin/index.php`);
await snap("11-admin-canchas", `${BASE}/admin/canchas.php`);
await snap("12-admin-precios", `${BASE}/admin/precios.php`);
await snap("13-admin-horarios", `${BASE}/admin/horarios.php`);
await snap("14-admin-reservaciones", `${BASE}/admin/reservaciones.php`);
await snap("15-admin-usuarios", `${BASE}/admin/usuarios.php`);
await snap("16-admin-reportes", `${BASE}/admin/reportes.php`);
await snap("17-admin-resenas", `${BASE}/admin/resenas.php`);
await snap("18-admin-festivos", `${BASE}/admin/festivos.php`);

// ── Dark mode on homepage ──
console.log("=== Dark mode ===");
await snap("19-darkmode", `${BASE}/`, null, async (p) => {
  await p.evaluate(() => {
    document.documentElement.classList.add("dark-mode");
    try { localStorage.setItem("dark-mode", "1"); } catch(e) {}
  });
});

// ── Payment page ──
console.log("=== Payment ===");
await snap("06-pago", `${BASE}/pages/pago.php?reservacion_id=1`, null, async (p) => {
  await p.goto(`${BASE}/pages/mis_reservaciones.php`, { waitUntil: "networkidle0" });
  const payLink = await p.$('a[href*="pago.php"]');
  if (payLink) {
    const href = await payLink.evaluate(el => el.getAttribute("href"));
    if (href) await snap("06-pago", `${BASE}/${href.replace(/^\//, "")}`);
  }
});

await browser.close();
console.log("\n=== Done ===");
