<template>
    <layout>
        <h1 class="text-3xl block">Overzicht</h1>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';
export default {
    components: {
        Layout,
    },
    props: {
        collection: Array,
    },
    mounted: function() {
        console.log(this.collection);
    },
};
</script>
