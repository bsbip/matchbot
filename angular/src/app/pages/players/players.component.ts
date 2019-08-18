import { Component, OnInit } from '@angular/core';
import { HttpService } from 'src/app/services/http.service';
import { ApiResponse } from 'src/app/types/api-response';

@Component({
    selector: 'app-players',
    templateUrl: './players.component.html',
    styleUrls: ['./players.component.scss'],
})
export class PlayersComponent implements OnInit {
    public loading = false;
    public resResult: ApiResponse<void> = {};
    public users: any;
    public player: any;

    constructor(private httpService: HttpService) {
        this.player = {
            default: false,
        };
    }

    /**
     * Handle the on init lifecycle.
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

                if (this.users[0] !== undefined) {
                    this.player = this.users[0];
                }
            },
            error => console.error(error),
            () => {}
        );
    }

    /**
     * Edit the list with players.
     *
     * @author Ramon Bakker
     */
    public editList(): void {
        this.player.default = !this.player.default;
        this.loading = true;
        this.httpService.put('player', this.player).subscribe(
            data => {
                this.resResult = data;
                this.resResult.success = true;
            },
            error => {
                this.resResult.success = false;
                this.resResult.error = true;

                if (error.error !== undefined) {
                    this.resResult.errors = Object.values(error.error.errors);
                    this.resResult.msg = error.error.msg;
                }

                this.loading = false;
                this.player.default = !this.player.default;
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
     *
     * @author Ramon Bakker
     */
    public selectPlayer($event: any): void {
        this.player = $event;
        this.resResult = {};
    }
}
