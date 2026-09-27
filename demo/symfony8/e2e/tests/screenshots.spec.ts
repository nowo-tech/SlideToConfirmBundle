import { test, expect } from '@playwright/test';
import { mkdirSync } from 'node:fs';
import { resolve } from 'node:path';

/** REQ-DEMO-013 — demo use-case card (case form + slide widget). */
const outDir = process.env.SCREENSHOT_DIR
  ? resolve(process.env.SCREENSHOT_DIR)
  : resolve(__dirname, '../../../../docs/images/demo');

function useCasePanel(page: import('@playwright/test').Page) {
  return page.locator('main .card').first();
}

test.beforeAll(() => {
  mkdirSync(outDir, { recursive: true });
});

test.describe('SlideToConfirm screenshots (use-case context)', () => {
  test('overview — idle payment slider in demo card', async ({ page }) => {
    await page.goto('/en?case=payment');
    const panel = useCasePanel(page);
    await expect(panel).toBeVisible();
    await expect(panel.locator('nowo-slide-to-confirm').first()).not.toHaveClass(/is-confirmed/);
    await panel.screenshot({ path: resolve(outDir, 'overview.png') });
  });

  test('interaction — confirmed legal consent in demo card', async ({ page }) => {
    await page.goto('/en?case=legal');
    const panel = useCasePanel(page);
    await expect(panel).toBeVisible();
    const host = panel.locator('nowo-slide-to-confirm').first();
    await page.evaluate(() => {
      document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (e) => e.preventDefault(), { capture: true });
      });
    });
    await host.locator('[role="slider"]').focus();
    await page.keyboard.press('End');
    await expect(host).toHaveClass(/is-confirmed/, { timeout: 5000 });
    await panel.screenshot({ path: resolve(outDir, 'interaction.png') });
  });
});
