<template>
    <nav
        class="w-full container mx-auto flex flex-wrap items-center justify-between mt-0 py-4"
    >
        <div class="pl-4 flex items-center">
            <img
                src="../../assets/logo.svg"
                class="h-5 pr-3 fill-current text-purple-500"
            />
            <inertia-link
                href="/"
                class="text-gray-900 text-base no-underline hover:no-underline font-extrabold text-xl"
                >Matchbot</inertia-link
            >
        </div>
        <div class="lg:hidden pr-4">
            <button
                id="nav-toggle"
                v-on:click="toggleVisibility()"
                class="flex items-center px-3 py-2 border rounded text-gray-500 border-gray-600 hover:text-gray-900 hover:border-purple-500 appearance-none focus:outline-none"
            >
                <svg
                    class="fill-current h-3 w-3"
                    viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <title>Menu</title>
                    <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" />
                </svg>
            </button>
        </div>
        <div
            class="w-full flex-grow lg:flex lg:content-center lg:items-center lg:w-auto lg:block mt-2 lg:mt-0 z-20"
            :class="{ hidden: navbarHidden }"
            id="nav-content"
        >
            <ul class="list-reset lg:flex justify-end items-center">
                <li class="mr-3 py-2 lg:py-0">
                    <inertia-link
                        href="/players"
                        class="inline-block py-2 px-4 text-gray-900 font-bold no-underline"
                        >Spelers</inertia-link
                    >
                </li>
                <li class="mr-3 py-2 lg:py-0">
                    <inertia-link
                        href="/match"
                        class="inline-block text-gray-600 no-underline hover:text-gray-900 hover:underline py-2 px-4"
                        >Match aanmaken</inertia-link
                    >
                </li>
                <li class="mr-3 py-2 lg:py-0">
                    <inertia-link
                        href="/results"
                        class="inline-block text-gray-600 no-underline hover:text-gray-900 hover:underline py-2 px-4"
                        >Resultaten toevoegen</inertia-link
                    >
                </li>
                <li class="mr-3 py-2 lg:py-0">
                    <inertia-link
                        href="/stats"
                        class="inline-block text-gray-600 no-underline hover:text-gray-900 hover:underline py-2 px-4"
                        >Statistieken</inertia-link
                    >
                </li>
                <li class="mr-3 py-2 lg:py-0">
                    <inertia-link
                        href="/standings"
                        class="inline-block text-gray-600 no-underline hover:text-gray-900 hover:underline py-2 px-4"
                        >Standen</inertia-link
                    >
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
import InertiaLink from '@inertiajs/inertia-vue/src/link';

export default {
    data: function() {
        return {
            navbarHidden: true,
        };
    },
    methods: {
        toggleVisibility() {
            this.navbarHidden = !this.navbarHidden;
        },
    },
    components: {
        InertiaLink,
    },
};
</script>
