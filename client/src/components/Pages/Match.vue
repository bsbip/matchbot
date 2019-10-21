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
            <CustomSelect
                v-model="matchType"
                @input="matchTypeChanged()"
                :options="types"
            />
        </div>
        <Card class="px-4 md:px-8 pt-3 md:pt-6 pb-4 md:pb-8">
            <div v-if="matchType.code === 'random'">
                <h2 class="text-x1 block mb-4">Potentiële spelers kiezen</h2>
                <table class="text-left w-full border-collapse mb-4">
                    <thead>
                        <TableHeader field="Speler" />
                        <TableHeader field="Standaardspeler" />
                    </thead>
                    <tbody>
                        <tr
                            class="cursor-pointer border-b border-gray-300"
                            :class="{
                                selected: selectedPlayers.includes(player),
                            }"
                            v-for="player in players"
                            v-bind:key="player.id"
                            @click="toggleSelected(player)"
                        >
                            <TableColumn>
                                <div
                                    class="flex flex-row content-start items-center"
                                >
                                    <img
                                        :src="player.profile.image_32"
                                        class="mr-4"
                                        alt="speler"
                                    />
                                    {{ player.real_name }} @{{ player.name }}
                                </div>
                            </TableColumn>
                            <TableColumn>
                                {{ player.default ? 'Ja' : 'nee' }}
                            </TableColumn>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="flex flex-row flex-wrap">
                <div
                    class="mb-4 w-full md:w-1/2 p-4"
                    v-bind:key="index"
                    v-for="(selectedPlayer, index) in selectedPlayers"
                >
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        <span v-if="index === 0">
                            {{ `Team 1` }}
                        </span>
                        <span v-if="index === 2">
                            {{ `Team 2` }}
                        </span>
                        <span v-else></span>
                    </label>
                    <CustomSelect
                        v-model="selectedPlayers[index]"
                        :options="players"
                        defaultOption="Kies een speler"
                        :customText="customText"
                    />
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
import CustomSelect from '@shared/Form/CustomSelect.vue';

export default {
    name: 'Match',
    components: {
        Layout,
        Card,
        TableHeader,
        TableColumn,
        Loader,
        CustomSelect,
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
            matchType: {
                code: 'random',
            },
            types: [
                {
                    code: 'random',
                    text: 'random',
                },
                {
                    code: 'custom',
                    text: 'custom',
                },
            ],
            apiUrl: process.env.VUE_APP_API_URL,
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
        customText(player) {
            return `${player.real_name} (@ ${player.name})`;
        },
        createMatch() {
            this.loading = true;
            this.matchCreated = false;
            this.failure = false;

            let url = `${this.apiUrl}match`;

            this.$axios
                .post(url, {
                    users: this.selectedPlayers,
                    random: this.matchType.code === 'random',
                })
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
            if (this.matchType.code === 'random') {
                this.selectedPlayers = this.players.filter(
                    (player) => player.default,
                );
            }

            if (this.matchType.code === 'custom') {
                this.selectedPlayers = [{}, {}, {}, {}];
            }
        },
    },
    mounted: function() {
        this.selectedPlayers = this.players.filter((player) => player.default);
    },
};
</script>
