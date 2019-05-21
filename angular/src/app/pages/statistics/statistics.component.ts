import { Component, OnInit } from '@angular/core';
import { HttpService } from 'src/app/services/http.service';

@Component({
    selector: 'app-statistics',
    templateUrl: './statistics.component.html',
    styleUrls: ['./statistics.component.scss'],
})
export class StatisticsComponent implements OnInit {
    public chartXlimit = 40;
    public fillChartLines = true;
    public fillChartLinesOptions = [
        {
            id: 0,
            name: 'Ja',
            value: true,
        },
        {
            id: 1,
            name: 'Nee',
            value: false,
        },
    ];
    public scoreChartData: Array<any> = [{ data: [], label: 'Score' }];
    public scoreChartLabels: Array<any> = [];
    public scoreChartOptions: any = {
        animation: false,
        responsive: true,
        scales: {
            xAxes: [
                {
                    scaleLabel: {
                        display: true,
                        labelString: 'Match',
                    },
                },
            ],
            yAxes: [
                {
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1,
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Teamscore',
                    },
                },
            ],
        },
        title: {
            display: true,
            text:
                'Teamscores spelers (laatste ' +
                this.chartXlimit +
                ' matches per speler)',
            fontSize: 16,
        },
        elements: {
            line: {
                fill: this.fillChartLines,
                tension: 0.2,
            },
        },
    };
    public scoreChartLegend = true;
    public scoreChartType = 'line';

    public crawlScoreChartData: Array<any> = [
        { data: [], label: 'Kruipscore' },
    ];
    public crawlScoreChartLabels: Array<any> = [];
    public crawlScoreChartOptions: any = {
        animation: false,
        responsive: true,
        scales: {
            xAxes: [
                {
                    scaleLabel: {
                        display: true,
                        labelString: 'Match',
                    },
                },
            ],
            yAxes: [
                {
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1,
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Kruipscore',
                    },
                },
            ],
        },
        title: {
            display: true,
            text:
                'Kruipscores spelers (laatste ' +
                this.chartXlimit +
                ' matches per speler)',
            fontSize: 16,
        },
        elements: {
            line: {
                fill: this.fillChartLines,
                tension: 0.2,
            },
        },
    };
    public crawlScoreChartLegend = true;
    public crawlScoreChartType = 'line';

    public totals = [];
    public playerStats = [];
    public data;
    public periodFilter = 2;
    public periods = [
        {
            id: 0,
            name: 'Vandaag',
            code: 'today',
        },
        {
            id: 1,
            name: 'Gisteren',
            code: 'yesterday',
        },
        {
            id: 2,
            name: 'Huidige week',
            code: 'current-week',
        },
        {
            id: 3,
            name: '7 dagen',
            code: '7-days',
        },
        {
            id: 4,
            name: '30 dagen',
            code: '30-days',
        },
        {
            id: 5,
            name: 'Huidige maand',
            code: 'current-month',
        },
        {
            id: 6,
            name: 'Geheel',
            code: 'all-time',
        },
    ];
    public orderOptions = [
        {
            id: 0,
            name: 'ID (oplopend)',
            field: 'id',
            direction: 'asc',
        },
        {
            id: 1,
            name: 'ID (aflopend)',
            field: 'id',
            direction: 'desc',
        },
        {
            id: 2,
            name: 'Speler (oplopend)',
            field: 'player',
            direction: 'asc',
        },
        {
            id: 3,
            name: 'Speler (aflopend)',
            field: 'player',
            direction: 'desc',
        },
        {
            id: 4,
            name: 'Matches (oplopend)',
            field: 'matches',
            direction: 'asc',
        },
        {
            id: 5,
            name: 'Matches (aflopend)',
            field: 'matches',
            direction: 'desc',
        },
        {
            id: 6,
            name: 'Gewonnen (oplopend)',
            field: 'won',
            direction: 'asc',
        },
        {
            id: 7,
            name: 'Gewonnen (aflopend)',
            field: 'won',
            direction: 'desc',
        },
        {
            id: 8,
            name: 'Verloren (oplopend)',
            field: 'lost',
            direction: 'asc',
        },
        {
            id: 9,
            name: 'Verloren (aflopend)',
            field: 'lost',
            direction: 'desc',
        },
        {
            id: 10,
            name: 'Teamscore (oplopend)',
            field: 'score',
            direction: 'asc',
        },
        {
            id: 11,
            name: 'Teamscore (aflopend)',
            field: 'score',
            direction: 'desc',
        },
        {
            id: 12,
            name: 'Gemiddelde teamscore (oplopend)',
            field: 'score_avg',
            direction: 'asc',
        },
        {
            id: 13,
            name: 'Gemiddelde teamscore (aflopend)',
            field: 'score_avg',
            direction: 'desc',
        },
        {
            id: 14,
            name: 'Kruipscore (oplopend)',
            field: 'crawl_score',
            direction: 'asc',
        },
        {
            id: 15,
            name: 'Kruipscore (aflopend)',
            field: 'crawl_score',
            direction: 'desc',
        },
        {
            id: 16,
            name: 'Gemiddelde kruipscore (oplopend)',
            field: 'crawl_score_avg',
            direction: 'asc',
        },
        {
            id: 17,
            name: 'Gemiddelde kruipscore (aflopend)',
            field: 'crawl_score_avg',
            direction: 'desc',
        },
        {
            id: 18,
            name: 'Punten (oplopend)',
            field: 'points',
            direction: 'asc',
        },
        {
            id: 19,
            name: 'Punten (aflopend)',
            field: 'points',
            direction: 'desc',
        },
    ];
    public orderByDefault = 19;
    public orderBy = this.orderOptions[19];
    public resResult = [];

    constructor(private httpService: HttpService) {}

    ngOnInit() {
        this.getTotals('all-time');
        this.getPlayerStats();
    }

    getTotals(newPeriod: any) {
        this.periodFilter = newPeriod;

        this.httpService
            .get(
                `stats/total/${this.periodFilter}/${this.orderBy.field}/${
                    this.orderBy.direction
                }`
            )
            .subscribe(
                data => {
                    this.totals = [];
                    this.data = data;
                    for (let stat of this.data) {
                        this.totals.push(stat);
                    }
                },
                error => console.error(error),
                () => {
                    if (this.totals.length === 0) {
                        this.resResult['error'] = true;
                        this.resResult['msg'] =
                            'Geen statistieken beschikbaar.';
                    } else {
                        this.resResult = [];
                    }
                }
            );
    }

    changeOrder(newOrder: string) {
        this.orderBy = this.orderOptions[newOrder];
        this.getTotals(this.periodFilter);
    }

    changeFillChartLinesOption(newOption: string) {
        this.fillChartLines = newOption === 'true' ? true : false;
        this.scoreChartOptions.elements.line.fill = this.fillChartLines;
        this.crawlScoreChartOptions.elements.line.fill = this.fillChartLines;
    }

    getPlayerStats() {
        this.httpService.get(`stats/players/${this.chartXlimit}`).subscribe(
            data => {
                this.playerStats = [];
                this.data = data;
                for (const stat of this.data['results']) {
                    this.playerStats.push(stat);
                }
                const newScoreChartData = [];
                const newCrawlScoreChartData = [];
                const maxMatches = this.data['max_results'];

                for (const playerStat of this.playerStats) {
                    const playerScores = [];
                    const playerCrawlScores = [];

                    for (const result of playerStat.data) {
                        playerScores.push(result.score);
                        playerCrawlScores.push(result.crawl_score);
                    }
                    newScoreChartData.push({
                        data: playerScores,
                        label: playerStat.player.name,
                    });
                    newCrawlScoreChartData.push({
                        data: playerCrawlScores,
                        label: playerStat.player.name,
                    });
                }
                this.scoreChartData = newScoreChartData;
                this.scoreChartLabels = [];

                for (let i = 1; i <= maxMatches; i++) {
                    this.scoreChartLabels.push(i);
                }

                this.crawlScoreChartData = newCrawlScoreChartData;
                this.crawlScoreChartLabels = [];

                for (let i = 1; i <= maxMatches; i++) {
                    this.crawlScoreChartLabels.push(i);
                }
            },
            error => console.error(error),
            () => {
                if (this.totals.length === 0) {
                    this.resResult['error'] = true;
                    this.resResult['msg'] = 'Geen statistieken beschikbaar.';
                } else {
                    this.resResult = [];
                }
            }
        );
    }
}
