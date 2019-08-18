import { Component, OnInit } from '@angular/core';
import { HttpService } from 'src/app/services/http.service';
import { ApiResponse } from 'src/app/types/api-response';

@Component({
    selector: 'app-dashboard',
    templateUrl: './dashboard.component.html',
    styleUrls: ['./dashboard.component.scss'],
})
export class DashboardComponent implements OnInit {
    public page = 0;
    public limit = 25;
    public finished = false;
    public loading = false;
    public amountOfResults = 0;
    public events = [];
    public resResult: ApiResponse<void> = {};

    constructor(private httpService: HttpService) {}

    /**
     * Handle on init lifecycle.
     *
     * @author Ramon Bakker
     */
    public ngOnInit(): void {
        this.getEventResults();
    }

    /**
     * Get the next event results.
     *
     * @author Ramon Bakker
     */
    public getNextEventResults(): void {
        if (!this.loading && !this.finished) {
            this.page++;
            this.getEventResults();
        }
    }

    /**
     * Get the event results.
     *
     * @author Ramon Bakker
     */
    public getEventResults(): void {
        this.loading = true;
        this.httpService
            .get(`events/results/${this.page}/${this.limit}`)
            .subscribe(
                data => {
                    const eventData: any = data;
                    this.amountOfResults = 0;
                    for (const event of eventData) {
                        this.events.push(event);
                        this.amountOfResults++;
                    }
                },
                () => {
                    this.finished = true;
                    this.loading = false;
                },
                () => {
                    if (this.events.length === 0) {
                        this.resResult.error = true;
                        this.resResult.msg = 'Geen matches gevonden.';
                    } else {
                        this.resResult = {};
                    }

                    if (this.amountOfResults < this.limit) {
                        this.finished = true;
                    }

                    this.loading = false;
                }
            );
    }
}
