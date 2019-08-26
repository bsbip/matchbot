<template>
    <td class="py-4 px-6 border-b border-gray-300 text-sm">
        {{ value }}
    </td>
</template>

<script>
export default {
    props: {
        value: String,
    },
};
</script>
