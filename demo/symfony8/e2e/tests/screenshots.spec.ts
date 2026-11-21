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

/** Confirm with the keyboard (End), the same path as the e2e spec: the thumb ends at the track end. */
async function confirmByKeyboard(page: import('@playwright/test').Page) {
  const host = page.locator('nowo-slide-to-confirm').first();
  await host.locator('[role="slider"]').focus();
  await page.keyboard.press('End');
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
    await confirmByKeyboard(page);
    await captureDemo(page, 'interaction.png');
  });
});
