<template>
    <th
        class="py-2 px-3 bg-gray-200 font-bold uppercase text-xs text-gray-dark border-b border-gray-300"
    >
        {{ field }}
    </th>
</template>

<script>
export default {
    props: {
        field: String,
    },
};
</script>
