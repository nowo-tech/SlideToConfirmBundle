import { test, expect } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { resolve } from 'node:path';

/**
 * REQ-DEMO-013 — full demo frame: navbar toolbar + case pills + form with
 * slide-to-confirm widget (+ Symfony WebProfiler when present).
 */
const outDir = process.env.SCREENSHOT_DIR
  ? resolve(process.env.SCREENSHOT_DIR)
  : resolve(__dirname, '../../../../docs/images/demo');

type Box = { x: number; y: number; width: number; height: number };

async function boxOf(
  page: import('@playwright/test').Page,
  selector: string,
): Promise<Box | null> {
  const loc = page.locator(selector).first();
  if ((await loc.count()) === 0) {
    return null;
  }
  return loc.boundingBox();
}

async function fitViewportToDemo(page: import('@playwright/test').Page) {
  const main = await boxOf(page, 'main');
  const nav = await boxOf(page, 'nav.navbar');
  const top = nav?.y ?? 0;
  const bottom = (main?.y ?? 0) + (main?.height ?? 800);
  const height = Math.min(1600, Math.max(900, Math.ceil(bottom - top + 96)));
  await page.setViewportSize({ width: 1280, height });
}

/** Navbar → main → profiler (union), padded. */
async function clipDemoFrame(page: import('@playwright/test').Page): Promise<Box> {
  const nav = await boxOf(page, 'nav.navbar');
  const main = await boxOf(page, 'main');
  const profiler =
    (await boxOf(page, '.sf-toolbar')) ?? (await boxOf(page, '.sf-minitoolbar'));
  if (!nav || !main) {
    throw new Error('Missing nav.navbar or main for SlideToConfirm screenshot clip');
  }
  const boxes = [nav, main, profiler].filter(Boolean) as Box[];
  const x = Math.min(...boxes.map((b) => b.x));
  const y = Math.min(...boxes.map((b) => b.y));
  const right = Math.max(...boxes.map((b) => b.x + b.width));
  const bottom = Math.max(...boxes.map((b) => b.y + b.height));
  const pad = 8;
  return {
    x: Math.max(0, x - pad),
    y: Math.max(0, y - pad),
    width: right - x + pad * 2,
    height: bottom - y + pad * 2,
  };
}

async function prepareDemoPage(page: import('@playwright/test').Page, path: string) {
  await page.setViewportSize({ width: 1280, height: 1100 });
  await page.goto(path);
  await expect(page.locator('nav.navbar .navbar-brand')).toBeVisible();
  await expect(page.locator('main .card')).toBeVisible();
  await expect(page.locator('nowo-slide-to-confirm').first()).toBeVisible();
  await page
    .locator('.sf-toolbar .sf-toolbar-block, .sf-toolbar-status, .sf-minitoolbar')
    .first()
    .waitFor({ state: 'visible', timeout: 10000 })
    .catch(() => {});
  await fitViewportToDemo(page);
}

async function captureDemo(page: import('@playwright/test').Page, file: string) {
  await fitViewportToDemo(page);
  const clip = await clipDemoFrame(page);
  await page.screenshot({ path: resolve(outDir, file), clip });
}

/** Drag thumb to the end of the track for a clear confirmed visual. */
async function confirmByDrag(page: import('@playwright/test').Page) {
  const host = page.locator('nowo-slide-to-confirm').first();
  const thumb = host.locator('[role="slider"], .nowo-slide-to-confirm__thumb').first();
  const track = host.locator('.nowo-slide-to-confirm__track').first();
  await expect(thumb).toBeVisible();
  const thumbBox = await thumb.boundingBox();
  const trackBox = await track.boundingBox();
  if (!thumbBox || !trackBox) {
    throw new Error('Missing thumb/track boxes for slide confirm');
  }
  const startX = thumbBox.x + thumbBox.width / 2;
  const startY = thumbBox.y + thumbBox.height / 2;
  const endX = trackBox.x + trackBox.width - thumbBox.width / 2;
  await page.mouse.move(startX, startY);
  await page.mouse.down();
  await page.mouse.move(endX, startY, { steps: 12 });
  await page.mouse.up();
  await expect(host).toHaveClass(/is-confirmed/, { timeout: 5000 });
}

test.beforeAll(() => {
  mkdirSync(outDir, { recursive: true });
});

test.describe('SlideToConfirm screenshots (full demo context)', () => {
  test('overview — navbar + payment case, slider idle', async ({ page }) => {
    await prepareDemoPage(page, '/en?case=payment');
    await expect(page.locator('main .nav-pills .nav-link.active')).toContainText(/Pay|Pagar/i);
    const host = page.locator('nowo-slide-to-confirm').first();
    await expect(host).not.toHaveClass(/is-confirmed/);
    await captureDemo(page, 'overview.png');
  });

  test('interaction — navbar + legal case, slider confirmed', async ({ page }) => {
    await prepareDemoPage(page, '/en?case=legal');
    await expect(page.locator('main .nav-pills .nav-link.active')).toContainText(/Legal/i);
    await page.evaluate(() => {
      document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (e) => e.preventDefault(), { capture: true });
      });
    });
    await confirmByDrag(page);
    await captureDemo(page, 'interaction.png');
  });
});
