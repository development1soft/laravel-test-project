<?php
namespace App\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class TeamHelper
{
    public static function createTeamForLeg($numberOfTeam): array|string
    {
        if ($numberOfTeam < 1 || $numberOfTeam == 1) {
            return 'Invalid number of teams';
        }
        $teams = [];
        for ($i = 0; $i < $numberOfTeam; $i++) {
            $team = new Team();
            $team->teamName = 'T-' . $i + 1;
            $teams[] = $team;
        }
        return $teams;
    }
    public static function generatePlanForWeeklyGames(array|string $teams): \Illuminate\Support\Collection
    {
        $dataCollection = collect();
        $totalTeams = count($teams);

        // Add a "OddNumber" team if the number of teams is odd
        if ($totalTeams % 2 !== 0) {
            $teams[] = (object) ['teamName' => 'OddNumber'];
            $totalTeams++;
        }

        $mid = $totalTeams / 2;
        $totalMatches = self::calculateTotalMatches($teams);

        // ciel will round a number up to the nearest whole number
        // like 2.2 will be 3
        $totalReferees = ceil($totalMatches / 4);

        // i use laravel collection to store the referees
        $collectReferees = collect();
        for ($i = 0; $i < $totalReferees; $i++) {
            $collectReferees->push(['referee' => 'R-' . ($i + 1) , 'value' => 4]);
        }

        $date = Carbon::now();
        $week = 0;

        self::loopThroughTeam($dataCollection,$collectReferees,$teams,$totalTeams,$mid,$date,$week,'home');
        // week of a way will start after mid week
        $week = $mid;
        self::loopThroughTeam($dataCollection,$collectReferees,$teams,$totalTeams,$mid,$date,$week,'away');

        return $dataCollection;
    }
    private static function calculateTotalMatches($teams)
    {
        // check if team title   ='OddNumber' and remove it
        foreach ($teams as $key => $team) {
            if ($team->teamName == 'OddNumber') {
                unset($teams[$key]);
            }
        }
        $totalTeams = count($teams);
        $totalMatches = 0;
        // Each team plays with every other team twice (home and away)
        for ($i = 0; $i < $totalTeams - 1; $i++) {
            for ($j = $i + 1; $j < $totalTeams; $j++) {
                $totalMatches += 2; // One match home and one away
            }
        }
        return $totalMatches;
    }

    private static function loopThroughTeam($dataCollection, $collectReferees, $teams, $totalTeams, $mid, $date, $week, $round) : void
    {


        for ($teamIndex = 0; $teamIndex < $totalTeams - 1; $teamIndex++) {
            // increment week if the loop is even
            if ($teamIndex % 2 == 0) {
                $date = $date->addDays(7);

                $week++;
            }
            for ($secondTeamIndex = 0; $secondTeamIndex < $mid; $secondTeamIndex++) {
                $refereeCollection = $collectReferees->first();
                $refereeName = $refereeCollection['referee'];
                $refereeValue = $refereeCollection['value'];
                $refereeValue = $refereeValue - 1;
                $collectReferees->shift();
                $collectReferees->push(['referee' => $refereeName , 'value' => $refereeValue]);


                $team1 = $teams[$secondTeamIndex];
                $team2 = $teams[$totalTeams - 1 - $secondTeamIndex];

                if ($team1->teamName !== 'OddNumber' && $team2->teamName !== 'OddNumber') {
                    $dataCollection->push([
                        'week'=>$week,
                        'teamName' => $round == 'home'? $team1->teamName : $team2->teamName,
                        'teamName2' => $round == 'home' ? $team2->teamName : $team1->teamName,
                        'refereeName' => $refereeName,
                        'date' =>  $date->format('Y-m-d'),
                        'round' => $round
                    ]);

                }
            }
            // Rotate the array
            $teams = array_merge([$teams[0]], array_slice($teams, -1), array_slice($teams, 1, -1));
        }
    }
}



