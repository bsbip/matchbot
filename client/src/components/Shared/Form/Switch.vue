<template>
    <div
        :class="value ? 'bg-green' : 'bg-grey'"
        class="w-12 h-6 rounded-full shadow inline-block cursor-pointer"
        @click="$emit('change', !value)"
    >
        <div
            :style="{ transform: `translateX(${value ? 100 : 0}%)` }"
            class="w-1/2 h-full bg-white rounded-full shadow"
        />
    </div>
</template>

<script>
export default {
    model: {
        event: 'change',
    },
    props: {
        value: {
            type: Boolean,
            required: true,
        },
    },
};
</script>
