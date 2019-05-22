import { Component, OnInit } from '@angular/core';
import { HttpService } from 'src/app/services/http.service';

@Component({
    selector: 'app-statistics',
    templateUrl: './statistics.component.html',
    styleUrls: ['./statistics.component.scss'],
})
export class StatisticsComponent implements OnInit {
    public totals = [];
    public playerStats = [];
    public data: any;
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
    public statsLimit = 40;
    public resResult: ApiResponse = {};

    constructor(private httpService: HttpService) {}

    /**
     * Handle the on init lifecycle.
     *
     * @author Ramon Bakker
     */
    public ngOnInit(): void {
        this.getTotals('all-time');
        this.getPlayerStats();
    }

    /**
     * Get the totals
     *
     * @param newPeriod the new period
     *
     * @author Ramon Bakker
     */
    public getTotals(newPeriod: any): void {
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
                    for (const stat of this.data) {
                        this.totals.push(stat);
                    }
                },
                error => console.error(error),
                () => {
                    if (this.totals.length === 0) {
                        this.resResult.error = true;
                        this.resResult.msg = 'Geen statistieken beschikbaar.';
                    } else {
                        this.resResult = {};
                    }
                }
            );
    }

    /**
     * Change the order and get totals.
     *
     * @param newOrder the new order
     *
     * @author Ramon Bakker
     */
    public changeOrder(newOrder: string): void {
        this.orderBy = this.orderOptions[newOrder];
        this.getTotals(this.periodFilter);
    }

    /**
     * Get the player statistics.
     *
     * @author Ramon Bakker
     */
    private getPlayerStats(): void {
        this.httpService.get(`stats/players/${this.statsLimit}`).subscribe(
            data => {
                this.playerStats = [];
                this.data = data;
                for (const stat of this.data['results']) {
                    this.playerStats.push(stat);
                }

                for (const playerStat of this.playerStats) {
                    const playerScores = [];
                    const playerCrawlScores = [];

                    for (const result of playerStat.data) {
                        playerScores.push(result.score);
                        playerCrawlScores.push(result.crawl_score);
                    }
                }
            },
            error => console.error(error),
            () => {
                if (this.totals.length === 0) {
                    this.resResult.error = true;
                    this.resResult.msg = 'Geen statistieken beschikbaar.';
                } else {
                    this.resResult = {};
                }
            }
        );
    }
}
