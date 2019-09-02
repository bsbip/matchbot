<template>
    <main>
        <Toasts />

        <header
            id="header"
            class="fixed w-full z-10 top-0 bg-white border-b border-gray-400"
        >
            <Navigation />
        </header>

        <section
            class="container w-full flex-1 flex-col flex-wrap mx-auto px-2 pt-8 lg:pt-16 mt-8"
        >
            <slot />
        </section>
        <footer
            class="w-full p-8 mt-8 bg-white w-full border-t border-gray-400 pin-b"
        >
            <section class="container w-full flex flex-row justify-between">
                <p>
                    Powered by
                    <a class="underline" href="https://www.bsbip.com">BSBIP</a>
                </p>
                <div class="flex flex-row justify-between">
                    <a
                        href="https://www.linkedin.com/company/bsbip-nl"
                        target="_blank"
                        class="mr-4"
                        ><img
                            height="25"
                            width="25"
                            src="../../assets/linkedin.svg"
                    /></a>
                    <a href="https://github.com/bsbip" target="_blank"
                        ><img
                            height="25"
                            width="25"
                            src="../../assets/github.svg"
                    /></a>
                </div>
            </section>
        </footer>
    </main>
</template>

<script>
import Navigation from '@shared/Navigation';
import Toasts from '@shared/Toasts.vue';

export default {
    name: 'Layout',
    components: {
        Navigation,
        Toasts,
    },
};
</script>
