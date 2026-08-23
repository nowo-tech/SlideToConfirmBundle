import { defineConfig } from 'vitest/config';

/**
 * Vitest configuration for SlideToConfirmBundle TypeScript unit tests.
 * Runs all `*.test.ts` under src/Resources/assets with jsdom and coverage.
 */
export default defineConfig({
  define: {
    __SLIDE_TO_CONFIRM_BUILD_TIME__: JSON.stringify(new Date().toISOString()),
  },
  test: {
    environment: 'jsdom',
    globals: true,
    include: ['src/Resources/assets/**/*.test.ts'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'text-summary', 'html'],
      reportsDirectory: './coverage-ts',
      include: ['src/Resources/assets/**/*.ts'],
      exclude: [
        'src/Resources/assets/**/*.test.ts',
        '**/node_modules/**',
        'src/Resources/assets/src/slide-to-confirm.ts',
      ],
      thresholds: {
        lines: 100,
        functions: 100,
        branches: 100,
        statements: 100,
      },
    },
  },
  resolve: {
    extensions: ['.ts'],
  },
});
