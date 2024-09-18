<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * MatchSchedulerService
 *
 * This service is responsible for generating football matches between teams,
 * assigning referees to the matches, and scheduling the matches across weeks.
 * It also provides functionality to calculate the minimum number of referees
 * required for the season.
 */
class MatchSchedulerService
{
    /**
     * @var array $matches Holds the generated matches between teams.
     * @var array $refereeAssignments Tracks the number of matches assigned to each referee.
     */
    protected array $matches = [];
    protected array $refereeAssignments = [];

    /**
     * Generates all possible matches between teams, both home and away games.
     *
     * @param array $teams Array of team identifiers.
     * @return array The generated list of matches. Each match is represented as an array
     *               with 'home', 'away', and 'round' keys, indicating the home team, away
     *               team, and whether it’s a home or away round.
     */
    public function generateMatches(array $teams): array
    {
        $numTeams = count($teams);

        // Generate the first round (Home games)
        for ($i = 0; $i < $numTeams - 1; $i++) {
            for ($j = $i + 1; $j < $numTeams; $j++) {
                $this->matches[] = ['home' => $teams[$i], 'away' => $teams[$j], 'round' => 'Home'];
            }
        }

        // Generate the second round (Away games)
        for ($i = 0; $i < $numTeams - 1; $i++) {
            for ($j = $i + 1; $j < $numTeams; $j++) {
                $this->matches[] = ['home' => $teams[$j], 'away' => $teams[$i], 'round' => 'Away'];
            }
        }

        return $this->matches;
    }

    /**
     * Assigns referees to the generated matches, ensuring that no referee handles more than 4 matches.
     *
     * @param array $matches   Array of matches to assign referees to.
     * @param array $referees  Array of referees available for assignment.
     * @return array The updated list of matches with assigned referees.
     */
    public function assignReferees(array $matches, array $referees): array
    {
        $refereeCount = count($referees);
        $refereeAssignments = array_fill(0, $refereeCount, 0); // Initialize the match count per referee

        foreach ($matches as $key => &$match) {
            $refereeAssigned = false;

            // Try to assign a referee with fewer than 4 matches
            for ($i = 0; $i < $refereeCount; $i++) {
                if ($refereeAssignments[$i] < 4) {
                    $match['referee'] = $referees[$i];
                    $refereeAssignments[$i]++;
                    $refereeAssigned = true;
                    break;
                }
            }

            // If no available referee has less than 4 matches, assign one based on key modulus
            if (!$refereeAssigned) {
                $match['referee'] = $referees[$key % $refereeCount];
            }
        }

        return $matches;
    }

    /**
     * Schedules the matches across multiple weeks, ensuring that no team plays more than 2 matches per week.
     *
     * @param array $matches Array of matches with assigned referees.
     * @return array The weekly schedule of matches. Each week contains a set of matches
     *               with their corresponding details (home, away, referee, date, and round).
     */
    public function scheduleMatches(array $matches): array
    {
        $weeks = [];
        $week = 1;
        $teamMatchesCount = [];
        $date = Carbon::today();

        foreach ($matches as $match) {
            $home = $match['home'];
            $away = $match['away'];

            // Initialize match count per team if not already set
            $teamMatchesCount[$home] = $teamMatchesCount[$home] ?? 0;
            $teamMatchesCount[$away] = $teamMatchesCount[$away] ?? 0;

            // Schedule the match if both teams have played less than 2 matches in the current week
            if ($teamMatchesCount[$home] < 2 && $teamMatchesCount[$away] < 2) {
                $weeks[$week][] = [
                    'home' => $home,
                    'away' => $away,
                    'referee' => $match['referee'],
                    'date' => $date->toDateString(),
                    'round' => $match['round'],
                ];

                $teamMatchesCount[$home]++;
                $teamMatchesCount[$away]++;
                $date->addDay();
            }

            // Move to the next week and reset counts if a team has reached the match limit for the week
            if ($teamMatchesCount[$home] == 2 || $teamMatchesCount[$away] == 2) {
                $week++;
                $date->addDay();
                $teamMatchesCount = []; // Reset the match count for the new week
            }
        }

        return $weeks;
    }

    /**
     * Calculates the minimum number of referees required for the season.
     * Each referee can handle a maximum of 4 matches.
     *
     * @param array $matches The array of matches.
     * @return int The minimum number of referees needed to handle all matches.
     */
    public function calculateMinReferees(array $matches): int
    {
        return (int) ceil(count($matches) / 4); // Calculate based on 4 matches per referee
    }
}
