import { ResultsComponent } from './results.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { ResultsRoutingModule } from './results-routing.module';
import { SharedModule } from 'src/app/shared/shared.module';

@NgModule({
    declarations: [ResultsComponent],
    imports: [CommonModule, ResultsRoutingModule, SharedModule],
})
export class ResultsModule {}
