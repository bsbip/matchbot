import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { StatisticsRoutingModule } from './statistics-routing.module';
import { StatisticsComponent } from './statistics.component';
import { SharedModule } from 'src/app/shared/shared.module';

@NgModule({
    declarations: [StatisticsComponent],
    imports: [CommonModule, StatisticsRoutingModule, SharedModule],
})
export class StatisticsModule {}
