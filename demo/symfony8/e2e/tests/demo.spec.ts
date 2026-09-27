import { test, expect } from '@playwright/test';

test.describe('SlideToConfirm demo', () => {
  test('payment case shows slide widget', async ({ page }) => {
    const response = await page.goto('/en?case=payment');
    expect(response?.ok()).toBeTruthy();
    await expect(page.locator('nowo-slide-to-confirm').first()).toBeVisible();
    await expect(page.locator('[role="slider"]').first()).toBeVisible();
  });

  test('End key confirms the slider', async ({ page }) => {
    await page.goto('/en?case=legal');
    const host = page.locator('nowo-slide-to-confirm').first();
    await expect(host).toBeVisible();
    await page.evaluate(() => {
      document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (e) => e.preventDefault(), { capture: true });
      });
    });
    await host.locator('[role="slider"]').focus();
    await page.keyboard.press('End');
    await expect(host).toHaveClass(/is-confirmed/, { timeout: 5000 });
  });
});
