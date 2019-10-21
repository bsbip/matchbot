export default {
    orderOptions: [
        {
            text: 'ID (oplopend)',
            field: 'id',
            direction: 'asc',
        },
        {
            text: 'ID (aflopend)',
            field: 'id',
            direction: 'desc',
        },
        {
            text: 'Speler (oplopend)',
            field: 'player',
            direction: 'asc',
        },
        {
            text: 'Speler (aflopend)',
            field: 'player',
            direction: 'desc',
        },
        {
            text: 'Matches (oplopend)',
            field: 'matches',
            direction: 'asc',
        },
        {
            text: 'Matches (aflopend)',
            field: 'matches',
            direction: 'desc',
        },
        {
            text: 'Gewonnen (oplopend)',
            field: 'won',
            direction: 'asc',
        },
        {
            text: 'Gewonnen (aflopend)',
            field: 'won',
            direction: 'desc',
        },
        {
            text: 'Verloren (oplopend)',
            field: 'lost',
            direction: 'asc',
        },
        {
            text: 'Verloren (aflopend)',
            field: 'lost',
            direction: 'desc',
        },
        {
            text: 'Teamscore (oplopend)',
            field: 'score',
            direction: 'asc',
        },
        {
            text: 'Teamscore (aflopend)',
            field: 'score',
            direction: 'desc',
        },
        {
            text: 'Gemiddelde teamscore (oplopend)',
            field: 'score_avg',
            direction: 'asc',
        },
        {
            text: 'Gemiddelde teamscore (aflopend)',
            field: 'score_avg',
            direction: 'desc',
        },
        {
            text: 'Kruipscore (oplopend)',
            field: 'crawl_score',
            direction: 'asc',
        },
        {
            text: 'Kruipscore (aflopend)',
            field: 'crawl_score',
            direction: 'desc',
        },
        {
            text: 'Gemiddelde kruipscore (oplopend)',
            field: 'crawl_score_avg',
            direction: 'asc',
        },
        {
            text: 'Gemiddelde kruipscore (aflopend)',
            field: 'crawl_score_avg',
            direction: 'desc',
        },
        {
            text: 'Punten (oplopend)',
            field: 'points',
            direction: 'asc',
        },
        {
            text: 'Punten (aflopend)',
            field: 'points',
            direction: 'desc',
        },
    ],
    periods: [
        {
            text: 'Vandaag',
            code: 'today',
        },
        {
            text: 'Gisteren',
            code: 'yesterday',
        },
        {
            text: 'Huidige week',
            code: 'current-week',
        },
        {
            text: '7 dagen',
            code: 'last-seven-days',
        },
        {
            text: '30 dagen',
            code: 'last-thirty-days',
        },
        {
            text: 'Huidige maand',
            code: 'current-month',
        },
        {
            text: 'Geheel',
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
