import { MatchComponent } from './match.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { MatchRoutingModule } from './match-routing.module';
import { SharedModule } from 'src/app/shared/shared.module';

@NgModule({
    declarations: [MatchComponent],
    imports: [CommonModule, MatchRoutingModule, SharedModule],
})
export class MatchModule {}
