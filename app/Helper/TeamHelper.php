<?php
namespace App\Helper;
use Carbon\Carbon;
class TeamHelper
{
    public $totalWeeks;
    public $teams;
    public $totalMatches;
    public $numberOfRefree;
    public function __construct($teams,$numberOfRefree)
    {
        $this->teams = $this->createTeamForLeg($teams);
        $this->totalMatches = $this->calculateTotalMatches();
        $this->totalWeeks = $this->calculateTotalWeeks();
        $this->numberOfRefree = $numberOfRefree;


    }
    public function createTeamForLeg($numberOfTeam)
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
    public function generatePlanForWeeklyGames()
    {
        $dataCollection = collect();
        $totalTeams = count($this->teams);
        $totalMatches = self::calculateTotalMatches();
        // Add a "OddNumber" team if the number of teams is odd
        if ($totalTeams % 2 !== 0) {
            // Add a team with the name OddNumber
            $team = new Team();
            $team->teamName = 'OddNumber';
            $this->teams[] = $team;
            $totalTeams++;
        }
        // ciel will round a number up to the nearest whole number
        // like 2.2 will be 3
        $totalReferees = ceil($totalMatches / 4);
        // i use laravel collection to store the referees
        $collectReferees = collect();
        for ($i = 0; $i < $totalReferees; $i++) {
            $collectReferees->push(['referee' => 'R-' . ($i + 1) , 'value' => 4]);
        }
        if (count($collectReferees) > $this->numberOfRefree) {
           return 'Invalid number of referees';
        }
        $midWeekCount = count($this->totalWeeks) / 2;
        $HomeWeeks = $this->totalWeeks->slice(0 , $midWeekCount);
        $AwayWeeks = $this->totalWeeks->slice($midWeekCount , $midWeekCount);
        $this->loopThroughTeam($dataCollection , $collectReferees , $HomeWeeks , 'home');
        $this->loopThroughTeam($dataCollection , $collectReferees , $AwayWeeks , 'away');
        $dataCollection = $dataCollection->sortBy('date');
        return $dataCollection;
    }
    private function calculateTotalMatches()
    {
        // check if team title   ='OddNumber' and remove it
        foreach ($this->teams as $key => $team) {
            if ($team->teamName == 'OddNumber') {
                unset($this->teams[$key]);
            }
        }
        $totalTeams = count($this->teams);
        $totalMatches = 0;
        // Each team plays with every other team twice (home and away)
        for ($i = 0; $i < $totalTeams - 1; $i++) {
            for ($j = $i + 1; $j < $totalTeams; $j++) {
                $totalMatches += 2; // One match home and one away
            }
        }
        return $totalMatches;
    }
    private function calculateTotalWeeks()
    {
        $mid = count($this->teams) / 2;
        $totalWeeks = $this->totalMatches / $mid;
        // Start from today
        $weeks = collect();
        $startDate = Carbon::today();
        for ($week = 0; $week < $totalWeeks; $week++) {
            $weekDates = [];
            for ($day = 0; $day < 7; $day++) {
                $weekDates[] = $startDate->copy()->addWeeks($week)->startOfWeek()->addDays($day)->toDateString();
            }
            $weeks->put("Week" . ($week + 1) , $weekDates);
        }
        return $weeks;
    }
    private function loopThroughTeam($dataCollection , $collectReferees , $weekRound , $round): void
    {

        $totalTeams = count($this->teams);
        $mid = $totalTeams / 2;
        for ($teamIndex = 0; $teamIndex < $totalTeams - 1; $teamIndex++) {
            // select first week dates
            for ($secondTeamIndex = 0; $secondTeamIndex < $mid; $secondTeamIndex++) {
                $refereeCollection = $collectReferees->first();
                $refereeName = $refereeCollection['referee'];
                $refereeValue = $refereeCollection['value'];
                $refereeValue = $refereeValue - 1;
                $collectReferees->shift();
                $collectReferees->push(['referee' => $refereeName , 'value' => $refereeValue]);
                $team1 = $this->teams[$secondTeamIndex];
                $team2 = $this->teams[$totalTeams - 1 - $secondTeamIndex];
                if ($team1->teamName !== 'OddNumber' && $team2->teamName !== 'OddNumber') {

                    // select random week
                    do {

                        $weekSelected = $weekRound->keys()->random();
                        $date = $weekRound[$weekSelected][rand(0 , 6)];
                        $checkTeamPlayInTheWeek = $this->checkTeamPlayedInTheWeek($dataCollection , $weekSelected , $team1->teamName , $team2->teamName);
                    } while (!$checkTeamPlayInTheWeek);
                    $dataCollection->push([
                        'week' => $weekSelected ,
                        'teamName' => ($round == 'home') ? $team1->teamName : $team2->teamName ,
                        'teamName2' => ($round == 'home') ? $team2->teamName : $team1->teamName ,
                        'refereeName' => $refereeName ,
                        'date' => $date ,
                    ]);

                }
            }
            // Rotate the array
            $this->teams = array_merge([$this->teams[0]] , array_slice($this->teams , -1) , array_slice($this->teams , 1 , -1));


        }
    }
    public function checkTeamPlayedInTheWeek($dataCollection , $week , $team1 , $team2)
    {
        $checkFirstTeamName = $dataCollection->where('teamName' , $team1)
            ->where('week' , $week)
            ->count();
        $check2TeamName = $dataCollection->where('teamName2' , $team1)
            ->where('week' , $week)
            ->count();
        $check3TeamName = $dataCollection->where('teamName' , $team2)
            ->where('week' , $week)
            ->count();
        $check4TeamName = $dataCollection->where('teamName2' , $team2)
            ->where('week' , $week)
            ->count();
        $countAll = $checkFirstTeamName + $check2TeamName + $check3TeamName + $check4TeamName;
        if ($countAll >= 2) {
            return false;
        }
        else {
            return true;
        }
    }
}



