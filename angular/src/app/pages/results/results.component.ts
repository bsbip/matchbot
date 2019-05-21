import { Component, OnInit } from '@angular/core';
import { HttpService } from 'src/app/services/http.service';
import { ActivatedRoute } from '@angular/router';

@Component({
    selector: 'app-results',
    templateUrl: './results.component.html',
    styleUrls: ['./results.component.scss'],
})
export class ResultsComponent implements OnInit {
    public id;
    public events;
    public index;
    public loading = false;
    public editResults = false;
    public statusType = 'without-results';
    public autoSelectedEvent = false;
    public team1Name = '';
    public team2Name = '';
    public results = {};
    public resResult = {};

    constructor(
        private httpService: HttpService,
        private _routeParams: ActivatedRoute
    ) {}

    ngOnInit() {
        this._routeParams.params.subscribe(params => {
            this.id = +params['id'];
        });
        this.getEvents(this.editResults);
    }

    getEvents(editResults) {
        if (editResults) {
            this.statusType = 'with-results';
            this.editResults = true;
        } else {
            this.statusType = 'without-results';
            this.editResults = false;
        }

        this.httpService.get(`events/${this.statusType}`).subscribe(
            data => {
                this.events = data;
                this.index = 0;

                // Select event based on route param (if available)
                if (!isNaN(this.id)) {
                    for (let event in this.events) {
                        if (this.events[event].id == this.id) {
                            this.index = event;
                        }
                    }
                }

                if (this.events[this.index] !== undefined) {
                    this.selectEvent(this.index);
                }
            },
            error => console.error(error)
        );
    }

    selectEvent(index) {
        this.index = index;
        this.results = {
            id: this.events[index].id,
        };
        this.id = this.events[index].id;

        if (this.editResults) {
            if (this.events[index].event_teams[0] !== undefined) {
                if (
                    this.events[index].event_teams[0].result.score !== undefined
                ) {
                    this.results['scoreTeam1'] = this.events[
                        index
                    ].event_teams[0].result.score;
                }
                if (
                    this.events[index].event_teams[0].result.crawl_score !==
                    undefined
                ) {
                    this.results['crawlsTeam1'] = this.events[
                        index
                    ].event_teams[0].result.crawl_score;
                }
                if (
                    this.events[index].event_teams[1].result.score !== undefined
                ) {
                    this.results['scoreTeam2'] = this.events[
                        index
                    ].event_teams[1].result.score;
                }
                if (
                    this.events[index].event_teams[1].result.crawl_score !==
                    undefined
                ) {
                    this.results['crawlsTeam2'] = this.events[
                        index
                    ].event_teams[1].result.crawl_score;
                }
                if (
                    this.events[index].event_teams[0].result.note !== undefined
                ) {
                    this.results['note'] = this.events[
                        index
                    ].event_teams[0].result.note;
                }
            }
        }

        this.setTeamNames(index);

        if (!this.autoSelectedEvent) {
            // Reset response result alert
            this.resResult = {};
        }

        this.autoSelectedEvent = false;
    }

    setTeamNames(index) {
        this.team1Name = '';
        this.team2Name = '';

        if (this.events[index] === undefined) {
            return;
        }
        if (this.events[index].event_teams[0].team.name !== undefined) {
            this.team1Name = this.events[index].event_teams[0].team.name;
        }
        if (this.events[index].event_teams[1].team.name !== undefined) {
            this.team2Name = this.events[index].event_teams[1].team.name;
        }
    }

    saveResults() {
        if (this.loading) {
            return;
        }

        if (this.results['crawlsTeam1'] === undefined) {
            this.results['crawlsTeam1'] = 0;
        }

        if (this.results['crawlsTeam2'] === undefined) {
            this.results['crawlsTeam2'] = 0;
        }

        this.loading = true;
        this.httpService
            .post(`match/result/${this.editResults}`, this.results)
            .subscribe(
                data => {
                    this.resResult = data;
                    this.resResult['success'] = true;
                    // Reset
                    this.team1Name = '';
                    this.team2Name = '';
                    this.results = {};
                    this.events.splice(this.index, 1);
                    this.autoSelectedEvent = true;
                    this.index = 0;

                    if (this.events[this.index] !== undefined) {
                        this.selectEvent(this.index);
                    }
                },
                error => {
                    this.resResult['success'] = false;
                    this.resResult['error'] = true;
                    this.resResult['errors'] = Object.values(error.data.errors);
                    this.resResult['msg'] = error.data.msg;
                    this.loading = false;
                },
                () => {
                    this.loading = false;
                }
            );
    }

    deleteResults() {
        if (
            !confirm(
                'Weet je zeker dat je het resultaat voor deze match wilt verwijderen?'
            )
        ) {
            return;
        }

        if (this.loading) {
            return;
        }

        this.loading = true;
        this.httpService.delete(`match/result/${this.results['id']}`).subscribe(
            data => {
                this.resResult = data;
                this.resResult['success'] = true;
                // Reset
                this.team1Name = '';
                this.team2Name = '';
                this.results = {};
                this.events.splice(this.index, 1);
                this.autoSelectedEvent = true;
                this.index = 0;

                if (this.events[this.index] !== undefined) {
                    this.selectEvent(this.index);
                }
            },
            error => {
                this.resResult['success'] = false;
                this.resResult['error'] = true;
                this.resResult['errors'] = Object.values(error.data.errors);
                this.resResult['msg'] = error.data.msg;
                this.loading = false;
            },
            () => {
                this.loading = false;
            }
        );
    }
}
