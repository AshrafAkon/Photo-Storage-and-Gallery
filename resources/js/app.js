import "./bootstrap";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

const appName =
    window.document.getElementsByTagName("title")[0]?.innerText || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});

// Sentry.init({
//     app,
//     dsn: process.env.MIX_SENTRY_DSN_PUBLIC,
//     integrations: [
//         //     new Integrations.BrowserTracing({
//         //         routingInstrumentation: Sentry.vueRouterInstrumentation(router),
//         //         tracingOrigins: ["localhost", "my-site-url.com", /^\//],
//         //     }),
//     ],
//     beforeSend: (event, hint) => {

//         if (process.env.MIX_APP_DEBUG == "true") {
//             console.error(hint.originalException || hint.syntheticException);
//             return null; // this drops the event and nothing will be sent to sentry
//         }
//         return event;
//     },
//     // Set tracesSampleRate to 1.0 to capture 100%
//     // of transactions for performance monitoring.
//     // We recommend adjusting this value in production
//     tracesSampleRate: 1.0,
// });
