<template>
    <div class="flex flex-col justify-between">
        <span
            :class="{
                'toggle-active': value,
                'toggle-inactive': !value,
            }"
            @click="$emit('change', !value)"
            class="shadow border rounded border-grey flex items-center cursor-pointer w-12 "
        >
            <span
                class="rounded border w-6 h-6 border-grey shadow-inner bg-white shadow"
            >
            </span>
        </span>
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
