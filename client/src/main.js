import { InertiaApp } from '@inertiajs/inertia-vue';

import Vue from 'vue';
import './plugins/axios';
import '../main.css';

import ToastService from './services/ToastService';

const app = document.getElementById('app');

Vue.config.productionTip = true;

Vue.prototype.$toastService = new ToastService();

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
