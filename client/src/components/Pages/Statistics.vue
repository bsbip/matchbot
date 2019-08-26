<template>
    <layout>
        <h1 class="text-3xl block">Statistieken</h1>
        <section class="flex flex-row items-center">
            <div class="relative">
                <select
                    class="block appearance-none w-full mr-8 bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                    v-model="selectedPeriod"
                    v-on:change="changeFilter()"
                >
                    <option
                        v-for="period in periods"
                        v-bind:value="period"
                        v-bind:key="period.code"
                        >{{ period.name }}</option
                    >
                </select>
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                >
                    <svg
                        class="fill-current h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
                        />
                    </svg>
                </div>
            </div>
            <div class="relative">
                <select
                    class="block appearance-none w-full m-4 bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                    v-model="selectedOrderOption"
                    v-on:change="changeFilter()"
                >
                    <option
                        v-for="orderOption in orderOptions"
                        v-bind:value="orderOption"
                        v-bind:key="orderOption.field"
                        >{{ orderOption.name }}</option
                    >
                </select>
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
                >
                    <svg
                        class="fill-current h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
                        />
                    </svg>
                </div>
            </div>
        </section>
        <div class="bg-white w-full shadow-md rounded my-6 overflow-x-auto">
            <table
                class="text-left w-full border-collapse"
                v-if="data.length > 0"
            >
                <thead>
                    <tr>
                        <TableHeader field="Nr." />
                        <TableHeader
                            v-for="field in fields"
                            v-bind:key="field.text"
                            :field="field.text"
                        />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="hover:bg-gray-300"
                        v-for="(statistic, index) of data"
                        v-bind:key="statistic.id"
                    >
                        <TableColumn :value="index + 1" />
                        <TableColumn
                            v-for="field in fields"
                            v-bind:key="field.text"
                            :field="field.property"
                            :value="statistic[field.property]"
                        />
                    </tr>
                </tbody>
            </table>
            <div
                v-if="data.length === 0"
                class="flex items-center bg-blue-500 text-white text-sm font-bold px-4 py-3"
                role="alert"
            >
                <p>Geen statistieken gevonden</p>
            </div>
        </div>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';
import TableHeader from '@shared/Table/TableHeader.vue';
import TableColumn from '@shared/Table/TableColumn.vue';

import statistics from '../../config/statistics';

import { Inertia } from '@inertiajs/inertia';

export default {
    name: 'Statistics',
    components: {
        Layout,
        TableHeader,
        TableColumn,
    },
    props: {
        data: Array,
    },
    data: function() {
        return {
            selectedOrderOption: {},
            selectedPeriod: {},
            orderOptions: statistics.orderOptions,
            periods: statistics.periods,
            fields: statistics.fields,
        };
    },
    methods: {
        changeFilter() {
            const url = Inertia.page.url.split('?')[0];

            Inertia.visit(url, {
                data: {
                    period: this.selectedPeriod.code,
                    orderBy: this.selectedOrderOption.field,
                    orderDirection: this.selectedOrderOption.direction,
                },
            });
        },
    },
    mounted: function() {
        this.selectedOrderOption = this.orderOptions[0];
        this.selectedPeriod = this.periods.find(
            (period) => period.code === 'all-time',
        );

        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('orderBy') && urlParams.has('orderDirection')) {
            this.selectedOrderOption = this.orderOptions.find(
                (orderOption) =>
                    orderOption.field === urlParams.get('orderBy') &&
                    orderOption.direction === urlParams.get('orderDirection'),
            );
        }

        if (urlParams.has('period')) {
            this.selectedPeriod = this.periods.find(
                (period) => period.code === urlParams.get('period'),
            );
        }
    },
};
</script>
