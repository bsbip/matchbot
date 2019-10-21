<template>
    <div class="inline-block relative">
        <select
            class="select focus:outline-none focus:shadow-outline hover:border-gray-500"
            @change="change"
        >
            <option v-if="typeof defaultOption !== 'undefined'" :value="{}">{{
                defaultOption
            }}</option>
            <option
                v-for="(option, index) in options"
                :value="JSON.stringify(option)"
                :key="index"
                >{{ renderText(option) }}</option
            >
        </select>
        <div
            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
        >
            <SvgArrowHead></SvgArrowHead>
        </div>
    </div>
</template>

<script>
import SvgArrowHead from '@shared/Svg/ArrowHead.vue';

export default {
    components: {
        SvgArrowHead,
    },
    props: {
        options: Array,
        defaultOption: String,
        customText: Function,
    },
    data() {
        return {
            selected: null,
        };
    },
    methods: {
        change(e) {
            this.selected = e.target.value;
            this.$emit('input', JSON.parse(this.selected));
            this.$emit('change');
        },
        renderText(option) {
            if (typeof this.customText === 'function') {
                return this.customText(option);
            } else {
                return option.text;
            }
        },
    },
};
</script>
