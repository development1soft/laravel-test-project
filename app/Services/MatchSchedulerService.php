<?php

namespace App\Services;

use DateTime;

class MatchSchedulerService
{
    protected array $matches = [];
    protected array $refereeAssignments = [];

    public function generateMatches(array $teams): array
    {
        $numTeams = count($teams);

        // Generate first round (Home)
        for ($i = 0; $i < $numTeams - 1; $i++) {
            for ($j = $i + 1; $j < $numTeams; $j++) {
                $this->matches[] = ['home' => $teams[$i], 'away' => $teams[$j], 'round' => 'Home'];
            }
        }

        // Generate second round (Away)
        for ($i = 0; $i < $numTeams - 1; $i++) {
            for ($j = $i + 1; $j < $numTeams; $j++) {
                $this->matches[] = ['home' => $teams[$j], 'away' => $teams[$i], 'round' => 'Away'];
            }
        }

        return $this->matches;
    }

    public function assignReferees(array $matches, array $referees): array
    {
        $refereeCount = count($referees);
        $refereeAssignments = array_fill(0, $refereeCount, 0); // Track matches per referee

        foreach ($matches as $key => &$match) {
            // Assign referees to the matches ensuring no referee handles more than 4 matches
            for ($i = 0; $i < $refereeCount; $i++) {
                if ($refereeAssignments[$i] < 4) {
                    $match['referee'] = $referees[$i];
                    $refereeAssignments[$i]++;
                    break;
                }
            }
        }

        return $matches;
    }

    public function scheduleMatches(array $matches): array
    {
        $weeks = [];
        $week = 1;
        $teamMatchesCount = [];
        $date = new DateTime('2022-06-01'); // Starting date

        foreach ($matches as $match) {
            $home = $match['home'];
            $away = $match['away'];

            // Initialize counters for the teams
            $teamMatchesCount[$home] = $teamMatchesCount[$home] ?? 0;
            $teamMatchesCount[$away] = $teamMatchesCount[$away] ?? 0;

            // Ensure no more than 2 matches per team per week
            if ($teamMatchesCount[$home] < 2 && $teamMatchesCount[$away] < 2) {
                $weeks[$week][] = [
                    'home' => $home,
                    'away' => $away,
                    'referee' => $match['referee'],
                    'date' => $date->format('Y-m-d'),
                    'round' => $match['round'],
                ];

                // Increment matches played by each team
                $teamMatchesCount[$home]++;
                $teamMatchesCount[$away]++;

                // Schedule one match per day
                $date->modify('+1 day');
            }

            // Move to the next week if both teams reached the max of 2 matches
            if ($teamMatchesCount[$home] == 2 && $teamMatchesCount[$away] == 2) {
                $week++;
                $date->modify('+1 day'); // Give a break day before starting the next week
                $teamMatchesCount[$home] = 0;
                $teamMatchesCount[$away] = 0;
            }
        }

        return $weeks;
    }

    public function calculateMinReferees(array $matches): int
    {
        return (int) ceil(count($matches) / 4); // Max 4 matches per referee
    }
}