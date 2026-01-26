# Vue 3 + Vite

This template should help get you started developing with Vue 3 in Vite. The template uses Vue 3 `<script setup>` SFCs, check out the [script setup docs](https://v3.vuejs.org/api/sfc-script-setup.html#sfc-script-setup) to learn more.

## Recommended IDE Setup

- [VS Code](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur) + [TypeScript Vue Plugin (Volar)](https://marketplace.visualstudio.com/items?itemName=Vue.vscode-typescript-vue-plugin).

## Deploy on Vercel

This Vue/Vite app is configured as a separate Vercel project within the nabla-site-apache monorepo.

### Deployment Steps

1. **Link this directory as a separate Vercel project:**
   ```bash
   cd vue-client
   vercel link
   ```

2. **Deploy to preview:**
   ```bash
   vercel
   ```

3. **Deploy to production:**
   ```bash
   vercel --prod
   ```

The `vercel.json` configuration in this directory defines:
- Build command: `npm run build`
- Framework: Vite (SPA)
- Output directory: `dist`
- Region: fra1 (Europe)
- SPA routing: All routes redirect to `/index.html`
