export default {
    fields: [
        {
            text: 'Team',
            property: 'name',
        },
        {
            text: 'Matches',
            property: 'totalgames',
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
            text: 'Win/verlies ratio',
            property: 'winlose',
        },
        {
            text: 'Teamscore',
            property: 'totalscore',
        },
        {
            text: 'Gemiddelde teamscore',
            property: 'avgscore',
            addition: '/match',
        },
        {
            text: 'Kruipscore',
            property: 'crawlscore',
        },
        {
            text: 'Gemiddelde kruipscore',
            property: 'avgcrawlscore',
            addition: '/match',
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
            name: 'Huidige maand',
            code: 'current-month',
        },
        {
            name: 'Geheel',
            code: 'all-time',
        },
    ],
    orderOptions: [
        {
            name: 'Matches',
            code: 'totalgames',
        },
        {
            name: 'Gewonnen',
            code: 'won',
        },
        {
            name: 'Verloren',
            code: 'lost',
        },
        {
            name: 'Win/verlies ratio',
            code: 'winlose',
        },
        {
            name: 'Gemiddelde teamscore',
            code: 'avgscore',
        },
        {
            name: 'Teamscore',
            code: 'totalscore',
        },
        {
            name: 'Gemiddelde kruipscore',
            code: 'avgcrawlscore',
        },
        {
            name: 'Kruipscore',
            code: 'crawlscore',
        },
    ],
};
