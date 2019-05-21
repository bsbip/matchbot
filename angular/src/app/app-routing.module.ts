import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

const routes: Routes = [
    {
        path: '',
        loadChildren: './pages/dashboard/dashboard.module#DashboardModule',
        data: {
            title: 'Matchbot',
        },
    },
    {
        path: 'match',
        loadChildren: './pages/match/match.module#MatchModule',
        data: {
            title: 'Match aanmaken',
        },
    },
    {
        path: 'players',
        loadChildren: './pages/players/players.module#PlayersModule',
        data: {
            title: 'Spelers',
        },
    },
    {
        path: 'results',
        loadChildren: './pages/results/results.module#ResultsModule',
        data: {
            title: 'Resultaten toevoegen',
        },
    },
    {
        path: 'standings',
        loadChildren: './pages/standings/standings.module#StandingsModule',
        data: {
            title: 'Standen',
        },
    },
    {
        path: 'stats',
        loadChildren: './pages/statistics/statistics.module#StatisticsModule',
        data: {
            title: 'Statistieken',
        },
    },
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule],
})
export class AppRoutingModule {}
