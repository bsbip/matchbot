<template>
    <main>
        <header
            id="header"
            class="fixed w-full z-10 top-0 bg-white border-b border-gray-400"
        >
            <Navigation />
        </header>

        <section
            class="container w-full flex flex-col flex-wrap mx-auto px-2 pt-8 lg:pt-16 mt-8"
        >
            <slot />
        </section>
        <footer
            class="w-full p-8 mt-8 bg-white w-full border-t border-gray-400 pin-b"
        >
            <section></section>
        </footer>
    </main>
</template>

<script>
import Navigation from '@shared/Navigation';

export default {
    name: 'HelloWorld',
    props: {
        msg: String,
    },
    components: {
        Navigation,
    },
};
</script>
