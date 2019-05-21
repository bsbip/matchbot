import { Component, OnInit } from '@angular/core';
import { HttpService } from 'src/app/services/http.service';

@Component({
    selector: 'app-standings',
    templateUrl: './standings.component.html',
    styleUrls: ['./standings.component.scss'],
})
export class StandingsComponent implements OnInit {
    public totals = [];
    public playerStats = [];
    public data;

    public periodFilter = 'all-time';
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
            name: 'Huidige maand',
            code: 'current-month',
        },
        {
            id: 5,
            name: 'Geheel',
            code: 'all-time',
        },
    ];

    public sortFilter = 'winlose';
    public sorts = [
        {
            id: 0,
            name: 'Matches',
            code: 'totalgames',
        },
        {
            id: 1,
            name: 'Gewonnen',
            code: 'win',
        },
        {
            id: 2,
            name: 'Verloren',
            code: 'lose',
        },
        {
            id: 3,
            name: 'Win/verlies ratio',
            code: 'winlose',
        },
        {
            id: 4,
            name: 'Gemiddelde teamscore',
            code: 'avgscore',
        },
        {
            id: 5,
            name: 'Teamscore',
            code: 'score',
        },
        {
            id: 6,
            name: 'Gemiddelde kruipscore',
            code: 'avgcrawl',
        },
        {
            id: 7,
            name: 'Kruipscore',
            code: 'crawl',
        },
    ];

    public resResult = [];

    constructor(private httpService: HttpService) {}

    // on init, get stats
    ngOnInit() {
        this.getDuoStats(this.periodFilter, this.sortFilter);
    }

    // get the stats for duo's from API
    getDuoStats(newPeriod: string, newSort: string) {
        // reset
        this.totals = [];
        // make request
        this.periodFilter = newPeriod;
        this.sortFilter = newSort;
        this.httpService
            .get(`standings/duo/${this.periodFilter}/${this.sortFilter}`)
            .subscribe(
                data => {
                    if (data['msg'] === undefined) {
                        this.data = data;
                        for (let stat of this.data) {
                            this.totals.push(stat);
                        }
                    } else {
                        this.resResult['error'] = true;
                        this.resResult['msg'] = data['msg'];
                    }
                },
                error => console.error(error),
                () => {
                    if (this.totals.length > 0) {
                        this.resResult = [];
                    }
                }
            );
    }
}
