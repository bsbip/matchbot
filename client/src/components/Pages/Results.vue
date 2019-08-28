<template>
    <layout>
        <h1 class="text-3xl block">Resultaten toevoegen</h1>
        <Card>
            <form class="px-8 pt-6 pb-8 mb-4">
                <div class="mb-4">
                    <label
                        class="block text-gray-700 text-sm font-bold mb-2"
                        for="checkbox"
                    >
                        Resultaten wijzigen?
                    </label>
                    Switch werkt niet 😑
                    <Switch v-model="update" />
                </div>
                <div class="mb-4">
                    <label
                        class="block text-gray-700 text-sm font-bold mb-2"
                        for="match"
                    >
                        Wedstrijd
                    </label>
                    <select
                        v-model="form.match"
                        id="match"
                        class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline mb-4"
                    >
                        <option
                            v-for="event in events"
                            v-bind:key="event.id"
                            :value="event"
                            >{{ event.name }}</option
                        >
                    </select>
                </div>
                <div
                    class="flex items-center bg-blue-300 text-white text-sm font-bold px-4 py-3 mb-4"
                    role="alert"
                >
                    <svg
                        class="fill-current w-4 h-4 mr-2"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"
                        />
                    </svg>
                    <p>
                        Resultaten kunnen alleen toegevoegd/gewijzigd worden
                        voor matches die maximaal 7 dagen geleden zijn
                        aangemaakt.
                    </p>
                </div>
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
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="team_one_score"
                                type="number"
                                placeholder="0"
                                v-model="form.team_one.score"
                            />
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-gray-700 text-sm font-bold mb-2"
                                for="team_one_crawl_score"
                            >
                                Kruipscore
                            </label>
                            <input
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="team_one_crawl_score"
                                type="number"
                                placeholder="0"
                                v-model="form.team_one.crawl_score"
                            />
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
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="team_two_score"
                                type="number"
                                placeholder="0"
                                v-model="form.team_two.score"
                            />
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-gray-700 text-sm font-bold mb-2"
                                for="team_two_crawl_score"
                            >
                                Kruipscore
                            </label>
                            <input
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="team_two_crawl_score"
                                type="number"
                                placeholder="0"
                                v-model="form.team_two.crawl_score"
                            />
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
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        v-model="form.note"
                        id="note"
                    ></textarea>
                </div>
                <button
                    class="block text-center bg-blue-500 text-white text-sm font-bold px-4 py-3 rounded shadow-md hover:no-shadow hover:bg-blue-600"
                >
                    <span>Opslaan</span>
                </button>
            </form>
        </Card>
    </layout>
</template>

<script>
import Layout from '@shared/Layout.vue';
import Card from '@shared/Card.vue';
import Switch from '@shared/Form/Switch.vue';

export default {
    name: 'Results',
    components: {
        Layout,
        Card,
        Switch,
    },
    props: {
        events: {},
    },
    data: function() {
        return {
            update: false,
            form: {
                match: {},
                team_one: {
                    score: Number,
                    crawl_score: Number,
                },
                team_two: {
                    score: Number,
                    crawl_score: Number,
                },
                note: '',
            },
        };
    },
};
</script>
