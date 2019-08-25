<template>
    <layout>
        <img alt="Vue logo" src="../../assets/logo.png" />
        <h3>{{ msg }}</h3>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';

export default {
    name: 'HelloWorld',
    props: {
        msg: String,
    },
    components: {
        Layout,
    },
};
</script>
