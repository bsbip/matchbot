<template>
    <layout>
        <h1 class="text-3xl block mb-4">Spelers</h1>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';

export default {
    name: 'Players',
    components: {
        Layout,
    },
    props: {
        data: Array,
    },
    data: function() {
        return {
            players: this.data,
        };
    },
};
</script>
