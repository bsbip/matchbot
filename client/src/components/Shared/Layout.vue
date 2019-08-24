<template>
    <main>
        <header>
            <inertia-link href="/players">Spelers</inertia-link>
            <inertia-link href="/match">Match aanmaken</inertia-link>
            <inertia-link href="/results">Resultaten toevoegen</inertia-link>
        </header>

        <section>
            <slot />
        </section>
    </main>
</template>

<script>
import InertiaLink from '@inertiajs/inertia-vue/src/link';

export default {
    name: 'HelloWorld',
    props: {
        msg: String,
    },
    components: {
        InertiaLink,
    },
};
</script>
