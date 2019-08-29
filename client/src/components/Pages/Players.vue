<template>
    <layout>
        <h1 class="text-3xl block mb-4">Spelers</h1>
        <p class="mb-4">
            De spelerslijst bevat de spelers die standaard gekozen kunnen worden
            voor matches. Indien een speler niet in de spelerslijst staat, moet
            hij of zij iedere keer handmatig bij een matchaanvraag worden
            toegevoegd.
        </p>
        <Card class="px-8 pt-6 pb-8">
            <table class="text-left w-full border-collapse mb-4">
                <thead>
                    <TableHeader field="Speler" />
                    <TableHeader field="Standaardspeler" />
                </thead>
                <tbody>
                    <tr
                        v-for="player in players"
                        v-bind:key="player.id"
                        class="hover:bg-gray-300"
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
                        <td class="py-2 px-3 border-b border-gray-300 text-sm">
                            <Toggle
                                :value="player.default"
                                @change="changeDefault(player)"
                            />
                        </td>
                        <TableColumn :value="player.default ? 'Ja' : 'nee'" />
                    </tr>
                </tbody>
            </table>
        </Card>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';
import Card from '@shared/Card.vue';
import Toggle from '@shared/Form/Toggle.vue';
import TableHeader from '@shared/Table/TableHeader.vue';

export default {
    name: 'Players',
    components: {
        Layout,
        Card,
        Toggle,
        TableHeader,
    },
    props: {
        data: Array,
    },
    data: function() {
        return {
            players: this.data,
        };
    },
    methods: {
        changeDefault(player) {
            player.default = !player.default;

            let url = `api/players/${player.id}`;

            this.$axios
                .put(url, { default: player.default })
                .then((response) => {
                    this.$toastService.add('success', response.data.message);
                })
                .catch((failure) => {
                    this.$toastService.add('failure', failure.data.message);
                    player.default = !player.default;
                });
        },
    },
};
</script>
