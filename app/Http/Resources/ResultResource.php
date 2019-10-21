<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'score' => $this->score,
            'crawlScore' => $this->crawl_score,
            'note' => $this->note,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'eventTeamId' => $this->event_team_id,
            'deleted' => (bool) $this->deleted,
            'team' => new TeamResource($this->whenLoaded('team')),
        ];
    }
}
