<template>
    <layout>
        <h1 class="text-3xl block mb-4">Resultaten toevoegen/wijzigen</h1>
        <Card>
            <form class="px-4 md:px-8 pt-3 md:pt-6 pb-4 md:pb-8">
                <div class="mb-4">
                    <label
                        class="block text-gray-700 text-sm font-bold mb-2"
                        for="checkbox"
                    >
                        Resultaten wijzigen?
                    </label>
                    <Toggle v-model="update" @change="updateChanged()" />
                </div>
                <div class="mb-4">
                    <label
                        class="block text-gray-700 text-sm font-bold mb-2"
                        for="match"
                    >
                        Wedstrijd
                    </label>
                    <CustomSelect
                        id="match"
                        v-model="event"
                        @input="eventChanged()"
                        :options="matches"
                        v-if="!loading.events && matches.length > 0"
                        :customText="customText"
                    />
                    <Alert
                        v-if="!loading.events && matches.length === 0"
                        class="mb-4"
                        message="Er zijn geen wedstrijden gevonden"
                    />
                    <img
                        v-if="loading.events"
                        width="25"
                        height="25"
                        src="/assets/img/loader.svg"
                    />
                </div>
                <Alert
                    class="mb-4"
                    message="Resultaten kunnen alleen toegevoegd/gewijzigd worden voor matches
            die maximaal 7 dagen geleden zijn aangemaakt."
                />
                <div class="flex flex-wrap flex-row">
                    <div class="w-full lg:w-1/2 pr-0 lg:pr-4">
                        <p class="mb-4">Team 1:</p>
                        <div class="mb-4">
                            <label
                                class="block text-gray-700 text-sm font-bold mb-2"
                                for="team_one_score"
                            >
                                Teamscore
                            </label>
                            <input
                                :class="{
                                    'has-errors':
                                        errors['teams.0.score'] !== undefined,
                                }"
                                class="input focus:outline-none focus:shadow-outline"
                                id="team_one_score"
                                type="number"
                                placeholder="0"
                                v-model="form.teams[0].score"
                            />
                            <p
                                v-if="errors['teams.0.score']"
                                class="text-red-500 text-xs italic"
                            >
                                {{ errors['teams.0.score'][0] }}
                            </p>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-gray-700 text-sm font-bold mb-2"
                                for="team_one_crawl_score"
                            >
                                Kruipscore
                            </label>
                            <input
                                :class="{
                                    'has-errors':
                                        errors['teams.0.crawl_score'] !==
                                        undefined,
                                }"
                                class="input focus:outline-none focus:shadow-outline"
                                id="team_one_crawl_score"
                                type="number"
                                placeholder="0"
                                v-model="form.teams[0].crawl_score"
                            />
                            <p
                                v-if="errors['teams.0.crawl_score']"
                                class="text-red-500 text-xs italic"
                            >
                                {{ errors['teams.0.crawl_score'][0] }}
                            </p>
                        </div>
                    </div>
                    <div class="w-full lg:w-1/2 pl-0 lg:pl-4">
                        <p class="mb-4">Team 2:</p>
                        <div class="mb-4">
                            <label
                                class="block text-gray-700 text-sm font-bold mb-2"
                                for="team_two_score"
                            >
                                Teamscore
                            </label>
                            <input
                                :class="{
                                    'has-errors':
                                        errors['teams.1.score'] !== undefined,
                                }"
                                class="input focus:outline-none focus:shadow-outline"
                                id="team_two_score"
                                type="number"
                                placeholder="0"
                                v-model="form.teams[1].score"
                            />
                            <p
                                v-if="errors['teams.1.score']"
                                class="text-red-500 text-xs italic"
                            >
                                {{ errors['teams.1.score'][0] }}
                            </p>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-gray-700 text-sm font-bold mb-2"
                                for="team_two_crawl_score"
                            >
                                Kruipscore
                            </label>
                            <input
                                :class="{
                                    'has-errors':
                                        errors['teams.1.crawl_score'] !==
                                        undefined,
                                }"
                                class="input focus:outline-none focus:shadow-outline"
                                id="team_two_crawl_score"
                                type="number"
                                placeholder="0"
                                v-model="form.teams[1].crawl_score"
                            />
                            <p
                                v-if="errors['teams.1.crawl_score']"
                                class="text-red-500 text-xs italic"
                            >
                                {{ errors['teams.1.crawl_score'][0] }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label
                        class="block text-gray-700 text-sm font-bold mb-2"
                        for="note"
                    >
                        Note
                    </label>
                    <textarea
                        class="input focus:outline-none focus:shadow-outline"
                        v-model="form.note"
                        id="note"
                    ></textarea>
                </div>
                <div class="flex flex-row">
                    <button
                        v-on:click.prevent="saveResults()"
                        v-bind:disabled="loading.results"
                        class="block mr-4 mb-4 text-center bg-blue-500 text-white text-sm font-bold px-4 py-3 rounded shadow-md hover:no-shadow hover:bg-blue-600"
                    >
                        <span v-if="!loading.results">Opslaan</span>
                        <span v-else><Loader /></span>
                    </button>
                    <button
                        v-if="update && form.event_id !== null"
                        v-on:click.prevent="deleteResult()"
                        class="block mb-4
                    text-center bg-red-500 text-white text-sm font-bold px-4
                    py-3 rounded shadow-md hover:no-shadow hover:bg-red-600"
                    >
                        <span v-if="!loading.deleteResult"
                            >Verwijder resultaat</span
                        >
                        <span v-else><Loader /></span>
                    </button>
                </div>
                <div
                    v-if="succeeded || failure"
                    :class="{
                        'alert--failure': failure,
                        'alert--success': succeeded,
                    }"
                    class="flex items-center text-white text-sm font-bold px-4 py-3"
                    role="alert"
                >
                    <p>{{ responseMessage }}</p>
                </div>
            </form>
        </Card>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';
import Card from '@shared/Card.vue';
import Loader from '@shared/Loader.vue';
import Alert from '@shared/Alert.vue';
import Toggle from '@shared/Form/Toggle.vue';
import CustomSelect from '@shared/Form/CustomSelect.vue';

const defaultForm = {
    teams: [
        {
            score: null,
            crawl_score: null,
        },
        {
            score: null,
            crawl_score: null,
        },
    ],
    note: '',
};

export default {
    name: 'Results',
    components: {
        Layout,
        Card,
        Loader,
        Alert,
        Toggle,
        CustomSelect,
    },
    props: {
        events: {},
    },
    data: function() {
        return {
            update: false,
            loading: {
                events: false,
                results: false,
                deleteResult: false,
            },
            succeeded: false,
            failure: false,
            matches: this.events,
            responseMessage: '',
            event: {},
            errors: [],
            form: {
                event_id: null,
                ...defaultForm,
            },
            apiUrl: process.env.VUE_APP_API_URL,
        };
    },
    mounted() {
        if (this.events) {
            this.event = this.events[0];
        }
    },
    methods: {
        customText(event) {
            return `${event.id}. ${event.name} (${new Date(
                event.start,
            ).toLocaleString('nl-NL')}): ${event.event_teams[0].team.name} - ${
                event.event_teams[1].team.name
            }`;
        },
        eventChanged() {
            this.form = {
                ...this.form,
                ...{ event_id: this.event.id },
            };

            if (this.update) {
                this.form = {
                    ...this.form,
                    ...{
                        teams: [
                            {
                                score:
                                    this.event.event_teams[0].result.score || 0,
                                crawl_score:
                                    this.event.event_teams[0].result
                                        .crawl_score || 0,
                            },
                            {
                                score:
                                    this.event.event_teams[1].result.score || 0,
                                crawl_score:
                                    this.event.event_teams[1].result
                                        .crawl_score || 0,
                            },
                        ],
                        note: this.event.event_teams[0].result.note,
                    },
                };
            } else {
                this.form = {
                    ...this.form,
                    ...defaultForm,
                };
            }
        },
        updateChanged() {
            this.form.event_id = null;
            this.loading.events = true;

            let url = window.location;

            const queryParams = new URLSearchParams(window.location.search);

            url += queryParams.has('limited') ? '&' : '?';
            url += `statusType=${
                this.update ? 'with-results' : 'without-results'
            }`;

            this.$axios
                .get(url)
                .then((response) => {
                    this.matches = response.data.events;
                    this.event = this.matches[0];
                    this.eventChanged();
                })
                .then(() => {
                    this.loading.events = false;
                });
        },
        saveResults() {
            this.form = {
                ...this.form,
                ...{ event_id: this.event.id },
            };

            this.loading.results = true;
            this.succeeded = false;
            this.failure = false;

            let url = `${this.apiUrl}match/result`;

            let request = this.update
                ? this.$axios.put(url, this.form)
                : this.$axios.post(url, this.form);

            request
                .then((response) => {
                    this.succeeded = true;
                    this.responseMessage = response.data.message;
                })
                .catch((failure) => {
                    this.failure = true;
                    this.errors = failure.data.errors || [];
                    this.responseMessage = failure.data.message;
                    this.loading.results = false;
                })
                .then(() => {
                    this.loading.results = false;
                });
        },
        deleteResult() {
            this.loading.deleteResult = true;
            this.succeeded = false;
            this.failure = false;

            let url = `${this.apiUrl}match/results/${this.form.event_id}`;

            this.$axios
                .delete(url)
                .then((response) => {
                    this.succeeded = true;
                    this.responseMessage = response.data.message;

                    this.matches = this.matches.filter((match) => {
                        return match.id !== this.form.event_id;
                    });

                    this.form = { event_id: null, ...defaultForm };
                })
                .catch((failure) => {
                    this.failure = true;
                    this.loading.deleteResult = false;
                    this.responseMessage = failure.data.message;
                })
                .then(() => {
                    this.loading.deleteResult = false;
                });
        },
    },
};
</script>
