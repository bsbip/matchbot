<template>
    <layout>
        <h1 class="text-3xl block">
            {{ collection.total }} wedstrijden gevonden
        </h1>
        <Card>
            <table
                class="text-left w-full border-collapse"
                v-if="matches.length > 0"
            >
                <thead>
                    <tr>
                        <TableHeader field="ID" />
                        <TableHeader field="Naam" />
                        <TableHeader field="Datum/tijd" />
                        <TableHeader field="Team 1" />
                        <TableHeader field="Team 2" />
                        <TableHeader field="Score" />
                        <TableHeader field="Kruipscore" />
                        <TableHeader field="Commentaar" />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="match in matches" v-bind:key="match.id">
                        <TableColumn :value="match.id" />
                        <TableColumn :value="match.name" />
                        <TableColumn :value="match.start | date" />
                        <TableColumn :value="match.results[0].team.name" />
                        <TableColumn :value="match.results[1].team.name" />
                        <TableColumn
                            :value="
                                `${match.results[0].score}-${match.results[1].score}`
                            "
                        />
                        <TableColumn
                            :value="
                                `${match.results[0].crawl_score}-${match.results[1].crawl_score}`
                            "
                        />
                        <TableColumn :value="match.results[0].note" />
                    </tr>
                </tbody>
            </table>
            <div
                v-if="matches.length === 0"
                class="flex items-center bg-blue-300 text-white text-sm font-bold px-4 py-3"
                role="alert"
            >
                <p>Geen statistieken gevonden</p>
            </div>
        </Card>
        <button
            v-if="next_page_url"
            v-on:click="loadMore()"
            class="block text-center bg-blue-500 text-white text-sm font-bold px-4 py-3 rounded shadow-md hover:no-shadow hover:bg-blue-600"
        >
            <span v-if="!loading">Meer resultaten laden</span>
            <img v-else src="../../assets/loader.svg" width="25" height="25" />
        </button>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';
import TableHeader from '@shared/Table/TableHeader.vue';
import TableColumn from '@shared/Table/TableColumn.vue';
import Card from '@shared/Card.vue';

export default {
    components: {
        Layout,
        TableHeader,
        TableColumn,
        Card,
    },
    data: function() {
        return {
            matches: this.collection.data,
            next_page_url: this.collection.next_page_url,
            loading: false,
        };
    },
    props: {
        collection: {
            current_page: Number,
            data: Array,
            first_page_url: String,
            from: Number,
            last_page: Number,
            last_page_url: String,
            next_page_url: String | null,
            path: String,
            per_page: Number,
            prev_page_url: String | null,
            to: Number,
            total: Number,
        },
    },
    methods: {
        loadMore: function() {
            this.loading = true;

            this.$axios
                .get(this.next_page_url)
                .then((response) => {
                    this.matches = [
                        ...this.matches,
                        ...response.data.collection.data,
                    ];
                    this.next_page_url = response.data.collection.next_page_url;
                })
                .then(() => {
                    this.loading = false;
                });
        },
    },
    filters: {
        date: function(value) {
            return new Date(value).toLocaleString('nl-NL');
        },
    },
};
</script>
