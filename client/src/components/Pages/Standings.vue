<template>
    <layout>
        <h1 class="text-3xl block mb-4">Standen</h1>
        <section class="flex flex-row items-center mb-4">
            <div class="inline-block relative w-64 mr-8">
                <CustomSelect
                    v-model="selectedPeriod"
                    :options="periods"
                    @change="changeFilter()"
                />
            </div>
            <div class="inline-block relative w-64">
                <CustomSelect
                    v-model="selectedOrderOption"
                    :options="orderOptions"
                    @change="changeFilter()"
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
                            :field="field.property"
                            >{{
                                `${statistic[field.property]}${field.addition ||
                                    ''}`
                            }}
                        </TableColumn>
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

import standings from '../../config/standings';

import { Inertia } from '@inertiajs/inertia';

export default {
    name: 'Standings',
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
            selectedOrderOption: {
                text: '',
                code: '',
            },
            selectedPeriod: {
                text: '',
                code: '',
            },
            orderOptions: standings.orderOptions,
            periods: standings.periods,
            fields: standings.fields,
        };
    },
    methods: {
        changeFilter() {
            const url = Inertia.page.url.split('?')[0];

            console.log({
                data: {
                    period: this.selectedPeriod.code,
                    orderBy: this.selectedOrderOption.code,
                },
            });

            Inertia.visit(url, {
                data: {
                    period: this.selectedPeriod.code,
                    orderBy: this.selectedOrderOption.code,
                },
            });
        },
    },
    mounted: function() {
        this.selectedOrderOption = this.orderOptions.find(
            (orderOption) => orderOption.code === 'winlose',
        );
        this.selectedPeriod = this.periods.find(
            (period) => period.code === 'all-time',
        );

        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('orderBy')) {
            this.selectedOrderOption = this.orderOptions.find(
                (orderOption) => orderOption.code === urlParams.get('orderBy'),
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
