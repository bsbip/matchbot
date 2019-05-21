import { environment } from 'src/environments/environment';
import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router, NavigationEnd, ActivatedRoute } from '@angular/router';
import { Title } from '@angular/platform-browser';
import { filter, map, first } from 'rxjs/operators';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss'],
})
export class AppComponent implements OnInit, OnDestroy {
    public title = 'matchbot';
    public readonly baseUrl = environment.baseUrl;
    private routerEventsSubscriber: any;

    constructor(
        private titleService: Title,
        private router: Router,
        private activatedRoute: ActivatedRoute
    ) {}

    public ngOnInit(): void {
        this.routerEventsSubscriber = this.router.events
            .pipe(
                filter(event => event instanceof NavigationEnd),
                map(() => this.activatedRoute.firstChild.data)
            )
            .subscribe(data => {
                data.pipe(first()).subscribe(value => {
                    if (value.title !== undefined) {
                        this.titleService.setTitle(value.title);
                    }
                });
            });
    }

    public ngOnDestroy(): void {
        this.routerEventsSubscriber.unsubscribe();
    }
}
