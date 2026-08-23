import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
  base: '/build/',
  define: {
    __SLIDE_TO_CONFIRM_BUILD_TIME__: JSON.stringify(new Date().toISOString()),
  },
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: false,
    rollupOptions: {
      input: 'assets/app.ts',
      output: {
        entryFileNames: 'assets/[name].js',
        chunkFileNames: 'assets/[name].js',
        assetFileNames: 'assets/[name].[ext]',
      },
    },
  },
  resolve: {
    alias: {
      '@bundle': path.resolve(__dirname, '../../src/Resources/assets'),
    },
    extensions: ['.ts', '.js'],
  },
});
