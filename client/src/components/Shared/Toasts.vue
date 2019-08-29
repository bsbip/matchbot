<template>
    <div class="fixed bottom-0 mb-16" style="left: 50%">
        <div class="relative" style="left: -50%">
            <p
                :class="{
                    'alert--success': toast.type === 'success',
                    'alert--failure': toast.type === 'failure',
                }"
                class="p-4 shadow rounded mb-4 font-bold text-gray-100"
                v-for="toast in toasts"
                v-bind:key="toast.message"
            >
                {{ toast.message }}
            </p>
        </div>
    </div>
</template>

<script>
export default {
    data: function() {
        return {
            toasts: [],
        };
    },
    methods: {
        updateToasts(toasts) {
            this.toasts = toasts;
        },
    },
    mounted: function() {
        this.$toastService.eventEmitter.addListener(
            'toastsUpdated',
            this.updateToasts,
        );
    },
    beforeDestroy: function() {
        this.$toastService.eventEmitter.removeListener(
            'toastsUpdated',
            this.updateToasts,
        );
    },
};
</script>
