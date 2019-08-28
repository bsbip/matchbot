import { InertiaApp } from '@inertiajs/inertia-vue';

import Vue from 'vue';
import './plugins/axios';

import '../main.css';

const app = document.getElementById('app');

Vue.config.productionTip = true;

Vue.filter('date', function(value) {
    return new Date(value).toLocaleString('nl-NL');
});

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
