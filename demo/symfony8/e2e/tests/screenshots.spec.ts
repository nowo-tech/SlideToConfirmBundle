import { test, expect } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { resolve } from 'node:path';

/**
 * REQ-DEMO-013 — full demo frame: the demo platform shell with the use-case
 * navigation and the form with the slide-to-confirm widget (Symfony toolbar hidden).
 */
const outDir = process.env.SCREENSHOT_DIR
  ? resolve(process.env.SCREENSHOT_DIR)
  : resolve(__dirname, '../../../../docs/images/demo');

/** The demo platform shell fills the viewport; the Symfony toolbar is hidden (it is not part of the demo). */
async function prepareDemoPage(page: import('@playwright/test').Page, path: string) {
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.goto(path);
  await page.addStyleTag({ content: '.sf-toolbar,.sf-minitoolbar,[id^="sfwdt"]{display:none!important}' });
  await expect(page.getByTestId('demo-platform')).toBeVisible();
  await expect(page.locator('main .nowo-ui-card')).toBeVisible();
  await expect(page.locator('nowo-slide-to-confirm').first()).toBeVisible();
}

async function captureDemo(page: import('@playwright/test').Page, file: string) {
  await page.screenshot({ path: resolve(outDir, file) });
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
  test('overview — platform shell + payment case, slider idle', async ({ page }) => {
    await prepareDemoPage(page, '/en/payment');
    await expect(page.getByTestId('demo-nav-demo_index-payment')).toHaveAttribute('aria-current', 'page');
    const host = page.locator('nowo-slide-to-confirm').first();
    await expect(host).not.toHaveClass(/is-confirmed/);
    await captureDemo(page, 'overview.png');
  });

  test('interaction — platform shell + legal case, slider confirmed', async ({ page }) => {
    await prepareDemoPage(page, '/en/legal');
    await expect(page.getByTestId('demo-nav-demo_index-legal')).toHaveAttribute('aria-current', 'page');
    await page.evaluate(() => {
      document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (e) => e.preventDefault(), { capture: true });
      });
    });
    await confirmByDrag(page);
    await captureDemo(page, 'interaction.png');
  });
});
