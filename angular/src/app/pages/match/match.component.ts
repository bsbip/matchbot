import { Component, OnInit } from '@angular/core';
import { HttpService } from 'src/app/services/http.service';

@Component({
    selector: 'app-match',
    templateUrl: './match.component.html',
    styleUrls: ['./match.component.scss'],
})
export class MatchComponent implements OnInit {
    public loading = false;
    public selectedMatchType = 'random';
    public resResult = {};
    public users: any;
    public matchTypes: { name: string; code_name: string }[];
    public matchPlayers: {}[];
    public selectedPlayers = [];
    protected createMatchAllowed = true;

    constructor(private httpService: HttpService) {
        this.matchTypes = [
            {
                name: 'Willekeurig',
                code_name: 'random',
            },
            {
                name: 'Handmatig',
                code_name: 'manually',
            },
        ];
        this.matchPlayers = [{}, {}, {}, {}];
    }

    ngOnInit() {
        this.getUsers();
    }

    getUsers() {
        this.httpService.get('users/slack').subscribe(
            data => {
                this.users = data;

                // Add default users to list of selected players
                for (let user of this.users) {
                    if (user.default) {
                        this.selectedPlayers.push(user.id);
                    }
                }
            },
            error => console.error(error),
            () => {}
        );
    }

    createMatch() {
        this.loading = true;
        this.httpService.post('match', this.matchPlayers).subscribe(
            data => {
                this.resResult = data;
                this.resResult['success'] = true;
                this.matchPlayers = [{}, {}, {}, {}];
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

    createRandomMatch() {
        this.createMatchAllowed = false;
        this.loading = true;
        this.httpService
            .post('slack/match', { users: this.selectedPlayers })
            .subscribe(
                data => {
                    this.resResult = data;
                    this.resResult['success'] = true;
                },
                error => {
                    this.resResult['success'] = false;
                    this.resResult['error'] = true;
                    this.resResult['msg'] = error.data.msg;
                    this.loading = false;
                    this.createMatchAllowed = true;
                },
                () => {
                    this.loading = false;
                }
            );
    }

    selectPlayer($event: any, i: string | number) {
        this.matchPlayers[i] = $event;
        this.resResult = {};
    }

    selectMatchType($event: string) {
        this.selectedMatchType = $event;
        this.resResult = {};
    }

    changeSelectedPlayers(playerId: any) {
        this.createMatchAllowed = true;
        this.resResult = {};

        const idx = this.selectedPlayers.indexOf(playerId);

        if (idx === -1) {
            // Add player
            this.selectedPlayers.push(playerId);
        } else {
            // Remove player
            this.selectedPlayers.splice(idx, 1);
        }
    }
}
