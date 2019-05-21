import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { StandingsRoutingModule } from './standings-routing.module';
import { StandingsComponent } from './standings.component';
import { SharedModule } from 'src/app/shared/shared.module';

@NgModule({
    declarations: [StandingsComponent],
    imports: [CommonModule, StandingsRoutingModule, SharedModule],
})
export class StandingsModule {}
