import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    const https =
        env.VITE_DEV_SERVER_CERT && env.VITE_DEV_SERVER_KEY
            ? {
                  cert: env.VITE_DEV_SERVER_CERT,
                  key: env.VITE_DEV_SERVER_KEY,
              }
            : undefined;

    // When the dev server is published through a reverse proxy on 443,
    // asset URLs use the public origin and HMR runs over wss on that port.
    const origin = env.VITE_DEV_SERVER_ORIGIN || undefined;
    const originHost = origin ? new URL(origin).hostname : undefined;

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            tailwindcss(),
        ],
        server: {
            https,
            origin,
            ws: origin
                ? {
                      protocol: 'wss',
                      host: originHost,
                      clientPort: 443,
                      path: '/vite-hmr',
                  }
                : undefined,
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
