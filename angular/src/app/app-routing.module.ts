import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

const routes: Routes = [
    {
        path: '',
        loadChildren: () => import('./pages/dashboard/dashboard.module').then(m => m.DashboardModule),
        data: {
            title: 'Matchbot',
        },
    },
    {
        path: 'match',
        loadChildren: () => import('./pages/match/match.module').then(m => m.MatchModule),
        data: {
            title: 'Match aanmaken',
        },
    },
    {
        path: 'players',
        loadChildren: () => import('./pages/players/players.module').then(m => m.PlayersModule),
        data: {
            title: 'Spelers',
        },
    },
    {
        path: 'results',
        loadChildren: () => import('./pages/results/results.module').then(m => m.ResultsModule),
        data: {
            title: 'Resultaten toevoegen',
        },
    },
    {
        path: 'standings',
        loadChildren: () => import('./pages/standings/standings.module').then(m => m.StandingsModule),
        data: {
            title: 'Standen',
        },
    },
    {
        path: 'stats',
        loadChildren: () => import('./pages/statistics/statistics.module').then(m => m.StatisticsModule),
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
