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

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
h3 {
    margin: 40px 0 0;
}
ul {
    list-style-type: none;
    padding: 0;
}
li {
    display: inline-block;
    margin: 0 10px;
}
a {
    color: #42b983;
}
</style>
