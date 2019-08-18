<?php

namespace App\Http\Controllers;

use App\Player;
use App\Result;
use App\Team;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Actions for statistics
 */
class StatsController extends Controller
{
    /**
     * Get a result.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function getResult(Request $request): JsonResponse
    {
        $text = $request->input('text');
        $resultText = '';

        if (strpos($text, 'overall') !== false) {
            // Get overall results
            $results = Team::join('results', 'teams.id', '=', 'results.team_id')
                ->where('results.deleted', false)
                ->get([
                    'teams.id AS team_id',
                    'teams.name AS team_name',
                    'results.score AS score',
                    'results.crawl_score AS crawl_score',
                ]);

            $teams = [];

            foreach ($results as $result) {
                if (isset($teams[$result->team_id])) {
                    $teams[$result->team_id]->score += $result->score;
                    $teams[$result->team_id]->crawl_score += $result->crawl_score;
                } else {
                    $teams[$result->team_id] = $result;
                }
            }

            foreach ($teams as $team) {
                $resultText .= $team->team_name . ' - punten: ' . $team->score . ' - aantal keren gekropen: ' . $team->crawl_score . "\n";
            }
        } else {
            $resultText = 'Statistiektype niet herkend.';
        }

        $data = [
            'response_type' => 'in_channel',
            'username' => 'Matchbot',
            'icon_url' => asset('assets/img/matchbot-icon.jpg'),
            'text' => $resultText,
        ];

        return new JsonResponse($data);
    }

    /**
     * Get total stats.
     *
     * @param string $period
     * @param string $orderBy order results by the specified field
     * @param string $orderDirection the order direction
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function getTotalStats(string $period = '', string $orderBy = 'id', string $orderDirection = 'asc'): JsonResponse
    {
        $data = [];
        $periodSet = $period !== '' && $period !== 'all-time';

        if ($orderBy == 'player') {
            $orderBy = 'name';
        }

        $players = Player::when($orderBy == 'id' || $orderBy == 'name', function ($query) use ($orderBy, $orderDirection) {
            $query->orderBy($orderBy, $orderDirection);
        })
            ->with(['results' => function ($query) use ($periodSet, $period) {
                $query
                    ->where('results.deleted', false)
                    ->with(['event.results'])
                    ->when($periodSet, function ($query) use ($period) {
                        switch ($period) {
                            case 'today':
                                $period = date('Y-m-d 00:00:00');
                                $periodEnd = date('Y-m-d H:i:s');
                                break;
                            case 'yesterday':
                                $period = new \DateTime();
                                $period->sub(new \DateInterval('P1D'));
                                $period = $period->format('Y-m-d 00:00:00');
                                $periodEnd = date('Y-m-d 00:00:00');
                                break;
                            case 'current-week':
                                $period = date('Y-m-d 00:00:00', strtotime('-' . date('w') . ' days'));
                                $periodEnd = date('Y-m-d H:i:s');
                                break;
                            case '7-days':
                                $period = new \DateTime();
                                $period = $period->sub(new \DateInterval('P7D'))->format('Y-m-d 00:00:00');
                                $periodEnd = date('Y-m-d H:i:s');
                                break;
                            case '30-days':
                                $period = new \DateTime();
                                $period = $period->sub(new \DateInterval('P30D'))->format('Y-m-d 00:00:00');
                                $periodEnd = date('Y-m-d H:i:s');
                                break;
                            case 'current-month':
                                $period = date('Y-m-01 00:00:00');
                                $periodEnd = date('Y-m-d H:i:s');
                                break;
                            default:
                                return;
                        }

                        $query->whereBetween('results.created_at', [
                            $period,
                            $periodEnd,
                        ]);
                    });
            }])
            ->get();

        foreach ($players as $player) {

            $playerStats = new \stdClass();

            $playerStats->score = $player->results->sum('score');
            $playerStats->matches = $player->results->count() ?: 1;
            $playerStats->crawl_score = $player->results->sum('crawl_score');
            $playerStats->user_id = $player->user_id;
            $playerStats->name = $player->name;
            $playerStats->id = $player->id;
            $playerStats->score_avg = round($player->results->average('score'), 2);
            $playerStats->crawl_score_avg = round($player->results->average('crawl_score'), 2);
            $playerStats->won = 0;
            $playerStats->lost = 0;
            $playerStats->draw = 0;

            foreach ($player->results as $stat) {

                $team2Score = $stat->event->results->firstWhere('team_id', '!=', $stat->team_id)->score;

                if ($stat->score > $team2Score) {
                    $playerStats->won++;
                } elseif ($stat->score < $team2Score) {
                    $playerStats->lost++;
                } else {
                    $playerStats->draw++;
                }
            }

            if (sizeof(get_object_vars($playerStats)) > 0) {
                $playerStats->points = $player->points;
                $data[] = $playerStats;
            }
        }

        if ($orderBy === 'id' || $orderBy === 'name') {
            return new JsonResponse($data);
        }

        usort($data, function ($a, $b) use ($orderBy, $orderDirection) {
            if ($a->$orderBy === $b->$orderBy) {
                return 0;
            }

            if ($orderDirection === 'asc') {
                return ($a->$orderBy < $b->$orderBy) ? -1 : 1;
            } else {
                return ($a->$orderBy < $b->$orderBy) ? 1 : -1;
            }
        });

        return new JsonResponse($data);
    }

    /**
     * Get player statistics.
     *
     * @param  int $limit the maximum amount of results to get
     *
     * @return JsonResponse
     *
     * @author Ramon Bakker <ramonbakker@rambit.nl>
     */
    public function getPlayerStats(int $limit = 40): JsonResponse
    {
        $stats = [
            'results' => [],
            'total_results' => 0,
            'max_results' => 0,
        ];
        // Get players
        $players = Player::get();

        // Get results for each player
        foreach ($players as $player) {
            $playerData = [
                'player' => $player,
                'data' => [],
            ];

            $playerData['data'] = Result::join('event_teams AS et', function ($j) use ($player) {
                $j->on('et.id', '=', 'results.event_team_id');
            })
                ->join('team_players AS tp', 'tp.team_id', '=', 'et.team_id')
                ->where('results.deleted', false)
                ->where('tp.player_id', $player->id)
                ->orderBy('results.event_id', 'desc')
                ->take($limit)
                ->get([
                    'results.id',
                    'results.event_id',
                    'results.score',
                    'results.crawl_score',
                    'results.created_at',
                    'results.updated_at',
                ])
                ->toArray();

            $playerData['data'] = array_reverse($playerData['data']);

            $stats['results'][] = $playerData;
            $stats['total_results'] += sizeof($playerData['data']);

            if ($stats['max_results'] < sizeof($playerData['data'])) {
                $stats['max_results'] = sizeof($playerData['data']);
            }
        }

        return new JsonResponse($stats);
    }

    /**
     * Get duo-player statistics.
     *
     * @param  string $period the period to filter on
     * @param  string $sort value to sort by
     *
     * @return JsonResponse
     *
     * @author Sander van Ooijen
     */
    public function getDuoStats(string $period = '', string $sort = 'winlose'): JsonResponse
    {
        $endDate = null;
        $startDate = null;
        $now = Carbon::now();
        $data = new Collection([]);

        $sortOptions = [
            'won',
            'lost',
            'crawlscore',
            'winlose',
            'avgscore',
            'totalgames',
            'totalscore',
            'avgcrawlscore',
        ];

        switch ($period) {
            case 'current-month':
                $periodSet = true;
                $startDate = $now->startOfMonth();
                $endDate = $now->endOfMonth();
                break;
            case 'current-week':
                $periodSet = true;
                $startDate = $now->startOfWeek();
                $endDate = $now->endOfWeek();
                break;
            case 'today':
                $periodSet = true;
                $startDate = $now->startOfDay();
                $endDate = $now->endOfDay();
                break;
            case 'yesterday':
                $periodSet = true;
                $startDate = $now->yesterday()->startOfDay();
                $endDate = $now->yesterday()->endOfDay();
                break;
            case '7-days':
                $periodSet = true;
                $startDate = $now->subDays(Carbon::DAYS_PER_WEEK);
                $endDate = $now;
                break;
            default:
                $periodSet = false;
                break;
        }

        $teams = Team::has('results', '>=', 5)
            ->whereHas('results.event', function ($w) use ($startDate, $endDate, $periodSet) {
                $w->where('status', 1);
                $w->when($periodSet, function ($w2) use ($startDate, $endDate) {
                    $w2->whereBetween('created_at', [$startDate, $endDate]);
                });
                $w->has('results');
            })
            ->with(['results.event' => function ($w) use ($startDate, $endDate, $periodSet) {
                $w->where('status', 1);
                $w->when($periodSet, function ($w2) use ($startDate, $endDate) {
                    $w2->whereBetween('created_at', [$startDate, $endDate]);
                });
                $w->with(['results']);
            }])
            ->get();

        if (sizeof($teams) === 0) {
            return new JsonResponse([
                'msg' => 'Geen teams gevonden',
            ]);
        }

        foreach ($teams as $team) {
            $stats = (object) [
                'won' => 0,
                'lost' => 0,
                'winlose' => 0,
                'name' => $team->name,
                'totalgames' => $team->results->count(),
                'totalscore' => $team->results->sum('score'),
                'crawlscore' => $team->results->sum('crawl_score'),
                'avgscore' => round($team->results->average('score'), 2),
                'avgcrawlscore' => round($team->results->average('crawl_score'), 2),
            ];

            if (!$periodSet && $stats->totalgames < 6) {
                continue;
            }

            foreach ($team->results as $result) {

                if (!isset($result->event)) {
                    continue;
                }

                $otherResult = $result->event->results->firstWhere('team_id', '!=', $result->team_id);

                if ($result->score > $otherResult->score) {
                    $stats->won++;
                    $stats->winlose++;
                } else {
                    $stats->lost++;
                }

            }

            if ($stats->won !== 0 && $stats->lost !== 0) {
                $stats->winlose = round($stats->won / $stats->lost, 2);
            }

            $data->push($stats);
        }

        if (in_array($sort, $sortOptions)) {
            $data = $data->sortByDesc($sort);
        }

        $data = $data->values();

        return new JsonResponse([
            'data' => $data->toArray(),
        ]);
    }
}
