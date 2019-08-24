import { InertiaApp } from '@inertiajs/inertia-vue';

import Vue from 'vue';
const app = document.getElementById('app');

Vue.config.productionTip = true;

new Vue({
    render: (h) =>
        h(InertiaApp, {
            props: {
                initialPage: JSON.parse(app.dataset.page),
                resolveComponent: (name) =>
                    import(`./components/Pages/${name}`).then(
                        (module) => module.default,
                    ),
            },
        }),
}).$mount(app);
