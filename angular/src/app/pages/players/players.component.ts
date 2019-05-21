import { Component, OnInit } from '@angular/core';
import { HttpService } from 'src/app/services/http.service';

@Component({
    selector: 'app-players',
    templateUrl: './players.component.html',
    styleUrls: ['./players.component.scss'],
})
export class PlayersComponent implements OnInit {
    public loading = false;
    public resResult = {};
    public users;
    public player;

    constructor(private httpService: HttpService) {
        this.player = {
            default: false,
        };
    }

    ngOnInit() {
        this.getUsers();
    }

    getUsers() {
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

    editList() {
        this.player.default = !this.player.default;
        this.loading = true;
        this.httpService.put('player', this.player).subscribe(
            data => {
                this.resResult = data;
                this.resResult['success'] = true;
            },
            error => {
                this.resResult['success'] = false;
                this.resResult['error'] = true;
                this.resResult['errors'] = Object.values(error.data.errors);
                this.resResult['msg'] = error.data.msg;
                this.loading = false;
                this.player.default = !this.player.default;
            },
            () => {
                this.loading = false;
            }
        );
    }

    selectPlayer($event, i) {
        this.player = $event;
        this.resResult = {};
    }
}
