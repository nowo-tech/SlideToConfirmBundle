import { defineConfig } from 'vite';

export default defineConfig({
  define: {
    __SLIDE_TO_CONFIRM_BUILD_TIME__: JSON.stringify(new Date().toISOString()),
  },
  build: {
    outDir: 'src/Resources/public',
    emptyOutDir: false,
    rollupOptions: {
      input: 'src/Resources/assets/src/slide-to-confirm.ts',
      output: {
        format: 'iife',
        entryFileNames: 'slide-to-confirm.js',
        assetFileNames: 'slide-to-confirm.[ext]',
      },
    },
    minify: true,
    sourcemap: false,
  },
  resolve: {
    extensions: ['.ts'],
  },
});
