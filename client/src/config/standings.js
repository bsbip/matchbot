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
            text: 'Huidige maand',
            code: 'current-month',
        },
        {
            text: 'Geheel',
            code: 'all-time',
        },
    ],
    orderOptions: [
        {
            text: 'Matches',
            code: 'totalgames',
        },
        {
            text: 'Gewonnen',
            code: 'won',
        },
        {
            text: 'Verloren',
            code: 'lost',
        },
        {
            text: 'Win/verlies ratio',
            code: 'winlose',
        },
        {
            text: 'Gemiddelde teamscore',
            code: 'avgscore',
        },
        {
            text: 'Teamscore',
            code: 'totalscore',
        },
        {
            text: 'Gemiddelde kruipscore',
            code: 'avgcrawlscore',
        },
        {
            text: 'Kruipscore',
            code: 'crawlscore',
        },
    ],
};
