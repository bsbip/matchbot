<template>
    <td class="py-2 px-3 border-b border-gray-300 text-sm">
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
