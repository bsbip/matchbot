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
    public resResult: ApiResponse = {};
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

    /**
     * Handle on init lifecycle.
     *
     * @author Ramon Bakker
     */
    public ngOnInit(): void {
        this.getUsers();
    }

    /**
     * Get the users.
     *
     * @author Ramon Bakker
     */
    public getUsers(): void {
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

    /**
     * Create a match.
     *
     * @author Ramon Bakker
     */
    public createMatch(): void {
        this.loading = true;
        this.httpService.post('match', this.matchPlayers).subscribe(
            data => {
                this.resResult = data;
                this.resResult.success = true;
                this.matchPlayers = [{}, {}, {}, {}];
            },
            error => {
                this.resResult.success = false;
                this.resResult.error = true;
                this.resResult.errors = Object.values(error.data.errors);
                this.resResult.msg = error.data.msg;
                this.loading = false;
            },
            () => {
                this.loading = false;
            }
        );
    }

    /**
     * Create a random match.
     *
     * @author Ramon Bakker
     */
    public createRandomMatch(): void {
        this.createMatchAllowed = false;
        this.loading = true;
        this.httpService
            .post('slack/match', { users: this.selectedPlayers })
            .subscribe(
                data => {
                    this.resResult = data;
                    this.resResult.success = true;
                },
                error => {
                    this.resResult.success = false;
                    this.resResult.error = true;
                    this.resResult.msg = error.data.msg;
                    this.loading = false;
                    this.createMatchAllowed = true;
                },
                () => {
                    this.loading = false;
                }
            );
    }

    /**
     * Select a player.
     *
     * @param $event the event data
     * @param i the position in the list with match players
     *
     * @author Ramon Bakker
     */
    public selectPlayer($event: any, i: string | number): void {
        this.matchPlayers[i] = $event;
        this.resResult = {};
    }

    /**
     * Select the match type.
     *
     * @param $event the event data
     *
     * @author Ramon Bakker
     */
    public selectMatchType($event: string): void {
        this.selectedMatchType = $event;
        this.resResult = {};
    }

    /**
     * Change the selected players.
     *
     * @param playerId the id of the player
     *
     * @author Ramon Bakker
     */
    public changeSelectedPlayers(playerId: any): void {
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
