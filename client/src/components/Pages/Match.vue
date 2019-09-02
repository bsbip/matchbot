<template>
    <layout>
        <h1 class="text-3xl block mb-4">Match aanmaken</h1>
        <label
            class="block text-gray-700 text-sm font-bold mb-2"
            for="checkbox"
        >
            Type match
        </label>
        <div class="inline-block relative w-full mr-8">
            <select
                class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline"
                v-model="matchType"
                @change="matchTypeChanged()"
            >
                <option
                    v-for="(type, index) in types"
                    v-bind:value="type"
                    v-bind:key="index"
                    >{{ type }}</option
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
        <Card class="px-4 md:px-8 pt-3 md:pt-6 pb-4 md:pb-8">
            <div v-if="matchType === 'random'">
                <h2 class="text-x1 block mb-4">Potentiële spelers kiezen</h2>
                <table class="text-left w-full border-collapse mb-4">
                    <thead>
                        <TableHeader field="Speler" />
                        <TableHeader field="Standaardspeler" />
                    </thead>
                    <tbody>
                        <tr
                            class="cursor-pointer"
                            :class="{
                                selected: selectedPlayers.includes(player),
                            }"
                            v-for="player in players"
                            v-bind:key="player.id"
                            @click="toggleSelected(player)"
                        >
                            <td
                                class="flex flex-row content-start items-center py-2 px-3 border-b border-gray-300 text-sm"
                            >
                                <img
                                    :src="player.profile.image_32"
                                    class="mr-4"
                                    alt="speler"
                                />
                                {{ player.real_name }} @{{ player.name }}
                            </td>
                            <TableColumn
                                :value="player.default ? 'Ja' : 'nee'"
                            />
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="flex flex-col">
                <div
                    class="mb-4"
                    v-bind:key="index"
                    v-for="(selectedPlayer, index) in selectedPlayers"
                >
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <span v-if="index <= 1">
                            {{ `Team 1: speler ${index + 1}` }}
                        </span>
                        <span v-else>
                            {{ `Team 2: speler ${index + 1}` }}
                        </span>
                    </label>
                    <select
                        v-model="selectedPlayers[index]"
                        class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline"
                    >
                        <option :value="{}">Kies speler</option>
                        <option
                            v-for="player in players"
                            v-bind:key="player.id"
                            :value="player"
                            >{{ player.real_name }} (@ {{ player.name }})
                        </option></select
                    >
                </div>
            </div>
            <button
                v-on:click.prevent="createMatch()"
                v-bind:disabled="loading"
                class="block mb-4 text-center bg-blue-500 text-white text-sm font-bold px-4 py-3 rounded shadow-md hover:no-shadow hover:bg-blue-600"
            >
                <span v-if="!loading">Aanmaken</span>
                <span v-else><Loader /></span>
            </button>
            <div
                v-if="matchCreated || failure"
                :class="{
                    'alert--failure': failure,
                    'alert--success': matchCreated,
                }"
                class="flex items-center text-white text-sm font-bold px-4 py-3"
                role="alert"
            >
                <p>{{ responseMessage }}</p>
            </div>
        </Card>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';
import Card from '@shared/Card.vue';
import TableHeader from '@shared/Table/TableHeader.vue';
import TableColumn from '@shared/Table/TableColumn.vue';
import Loader from '@shared/Loader.vue';

export default {
    name: 'Match',
    components: {
        Layout,
        Card,
        TableHeader,
        TableColumn,
        Loader,
    },
    props: {
        data: Array,
    },
    data: function() {
        return {
            players: this.data,
            loading: false,
            matchCreated: false,
            failure: false,
            responseMessage: '',
            selectedPlayers: [],
            matchType: 'random',
            types: ['random', 'custom'],
        };
    },
    methods: {
        toggleSelected(player) {
            if (this.selectedPlayers.includes(player)) {
                this.selectedPlayers = this.selectedPlayers.filter(
                    (selectedPlayer) => selectedPlayer !== player,
                );
            } else {
                this.selectedPlayers = [...this.selectedPlayers, player];
            }
        },
        createMatch() {
            this.loading = true;
            this.matchCreated = false;
            this.failure = false;

            let url = '/api/match';

            this.$axios
                .post(url, { users: this.selectedPlayers })
                .then((response) => {
                    this.matchCreated = true;
                    this.responseMessage = response.data.message;
                })
                .catch((failure) => {
                    this.loading = false;
                    this.failure = true;
                    this.responseMessage = failure.data.message;
                })
                .then(() => {
                    this.loading = false;
                });
        },
        matchTypeChanged() {
            if (this.matchType === 'random') {
                this.selectedPlayers = this.players.filter(
                    (player) => player.default,
                );
            }

            if (this.matchType === 'custom') {
                this.selectedPlayers = [{}, {}, {}, {}];
            }
        },
    },
    mounted: function() {
        this.selectedPlayers = this.players.filter((player) => player.default);
    },
};
</script>
