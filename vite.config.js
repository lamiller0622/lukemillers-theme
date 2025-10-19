import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin'
import path from 'path'

export default defineConfig({
  base: '/clickandbuilds/LukeMillersWebsite/wp-content/themes/lukemillers-theme/public/', // <- important
  build: {
    outDir: 'public',              // emits public/ + manifest.json
    assetsDir: 'assets',           // optional tidy subfolder
    manifest: true,
    rollupOptions: {
      input: [
        path.resolve(__dirname, 'resources/js/app.js'),
        path.resolve(__dirname, 'resources/css/app.scss'),
      ],
    },
  },
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        'resources/js/app.js',
        'resources/js/editor.js',
      ],
      refresh: true,
    }),
    wordpressPlugin(),
    wordpressThemeJson({
      disableTailwindColors: false,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
    }),
  ],
  resolve: {
    alias: {
      '@scripts': path.resolve(__dirname, 'resources/js'),
      '@styles': path.resolve(__dirname, 'resources/css'),
      '@fonts': path.resolve(__dirname, 'resources/fonts'),
      '@images': path.resolve(__dirname, 'resources/images'),
    },
  },
  publicDir: 'resources/static',
})