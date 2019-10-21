<template>
    <layout>
        <h1 class="text-3xl block mb-4">Statistieken</h1>
        <section class="flex flex-row items-center mb-4">
            <div class="inline-block relative w-64 mr-8">
                <CustomSelect
                    v-model="selectedPeriod"
                    :options="periods"
                    @input="changeFilter()"
                />
            </div>
            <div class="inline-block relative w-64">
                <CustomSelect
                    v-model="selectedOrderOption"
                    :options="orderOptions"
                    @input="changeFilter()"
                />
            </div>
        </section>
        <Card>
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
                        class="hover:bg-gray-300 border-b border-gray-300"
                        v-for="(statistic, index) of data"
                        v-bind:key="statistic.id"
                    >
                        <TableColumn>{{ index + 1 }}</TableColumn>
                        <TableColumn
                            v-for="field in fields"
                            v-bind:key="field.text"
                            >{{
                                `${statistic[field.property]}${field.addition ||
                                    ''}`
                            }}</TableColumn
                        >
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
        </Card>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';
import TableHeader from '@shared/Table/TableHeader.vue';
import TableColumn from '@shared/Table/TableColumn.vue';
import Card from '@shared/Card.vue';
import CustomSelect from '@shared/Form/CustomSelect.vue';

import statistics from '../../config/statistics';

import { Inertia } from '@inertiajs/inertia';

export default {
    name: 'Statistics',
    components: {
        Layout,
        TableHeader,
        TableColumn,
        Card,
        CustomSelect,
    },
    props: {
        data: Array,
    },
    data: function() {
        return {
            selectedOrderOption: null,
            selectedPeriod: null,
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
        this.selectedOrderOption = this.orderOptions.find((orderOption) => {
            return (
                orderOption.field === 'points' &&
                orderOption.direction === 'desc'
            );
        });
        this.selectedPeriod = this.periods.find(
            (period) => period.code === 'all-time',
        );

        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('orderBy') && urlParams.has('orderDirection')) {
            this.selectedOrderOption = this.orderOptions.find((orderOption) => {
                return (
                    orderOption.field === urlParams.get('orderBy') &&
                    orderOption.direction === urlParams.get('orderDirection')
                );
            });
        }

        if (urlParams.has('period')) {
            this.selectedPeriod = this.periods.find(
                (period) => period.code === urlParams.get('period'),
            );
        }
    },
};
</script>
