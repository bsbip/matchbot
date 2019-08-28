export default {
    orderOptions: [
        {
            name: 'ID (oplopend)',
            field: 'id',
            direction: 'asc',
        },
        {
            name: 'ID (aflopend)',
            field: 'id',
            direction: 'desc',
        },
        {
            name: 'Speler (oplopend)',
            field: 'player',
            direction: 'asc',
        },
        {
            name: 'Speler (aflopend)',
            field: 'player',
            direction: 'desc',
        },
        {
            name: 'Matches (oplopend)',
            field: 'matches',
            direction: 'asc',
        },
        {
            name: 'Matches (aflopend)',
            field: 'matches',
            direction: 'desc',
        },
        {
            name: 'Gewonnen (oplopend)',
            field: 'won',
            direction: 'asc',
        },
        {
            name: 'Gewonnen (aflopend)',
            field: 'won',
            direction: 'desc',
        },
        {
            name: 'Verloren (oplopend)',
            field: 'lost',
            direction: 'asc',
        },
        {
            name: 'Verloren (aflopend)',
            field: 'lost',
            direction: 'desc',
        },
        {
            name: 'Teamscore (oplopend)',
            field: 'score',
            direction: 'asc',
        },
        {
            name: 'Teamscore (aflopend)',
            field: 'score',
            direction: 'desc',
        },
        {
            name: 'Gemiddelde teamscore (oplopend)',
            field: 'score_avg',
            direction: 'asc',
        },
        {
            name: 'Gemiddelde teamscore (aflopend)',
            field: 'score_avg',
            direction: 'desc',
        },
        {
            name: 'Kruipscore (oplopend)',
            field: 'crawl_score',
            direction: 'asc',
        },
        {
            name: 'Kruipscore (aflopend)',
            field: 'crawl_score',
            direction: 'desc',
        },
        {
            name: 'Gemiddelde kruipscore (oplopend)',
            field: 'crawl_score_avg',
            direction: 'asc',
        },
        {
            name: 'Gemiddelde kruipscore (aflopend)',
            field: 'crawl_score_avg',
            direction: 'desc',
        },
        {
            name: 'Punten (oplopend)',
            field: 'points',
            direction: 'asc',
        },
        {
            name: 'Punten (aflopend)',
            field: 'points',
            direction: 'desc',
        },
    ],
    periods: [
        {
            name: 'Vandaag',
            code: 'today',
        },
        {
            name: 'Gisteren',
            code: 'yesterday',
        },
        {
            name: 'Huidige week',
            code: 'current-week',
        },
        {
            name: '7 dagen',
            code: 'last-seven-days',
        },
        {
            name: '30 dagen',
            code: 'last-thirty-days',
        },
        {
            name: 'Huidige maand',
            code: 'current-month',
        },
        {
            name: 'Geheel',
            code: 'all-time',
        },
    ],
    fields: [
        {
            text: 'Speler',
            property: 'name',
        },
        {
            text: 'Matches',
            property: 'matches',
        },
        {
            text: 'Gewonnen',
            property: 'won',
        },
        {
            text: 'Verloren',
            property: 'lost',
        },
        {
            text: 'Teamscore',
            property: 'score',
        },
        {
            text: 'Gemiddelde teamscore',
            property: 'score_avg',
            addition: '/match',
        },
        {
            text: 'Kruipscore',
            property: 'crawl_score',
        },
        {
            text: 'Gemiddelde kruipscore',
            property: 'crawl_score_avg',
            addition: '/match',
        },
        {
            text: 'Punten',
            property: 'points',
        },
    ],
};
