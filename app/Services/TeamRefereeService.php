<?php

namespace App\Services;

/**
 * TeamRefereeService
 *
 * This service is responsible for generating a list of teams and referees
 * based on the provided number of teams and referees. The class is designed
 * to be used in scenarios where a schedule or match management system is 
 * required to initialize participants and match officials.
 */
class TeamRefereeService
{
    /**
     * Generates an array of team identifiers based on the number of teams.
     *
     * @param int $n The number of teams to generate.
     * @return array The generated list of teams, where each team is identified 
     *               by a string in the format 'T1', 'T2', etc.
     */
    public function generateTeams(int $n): array
    {
        $teams = [];
        for ($i = 1; $i <= $n; $i++) {
            $teams[] = 'T' . $i;
        }
        return $teams;
    }

    /**
     * Generates an array of referee identifiers based on the number of referees.
     *
     * @param int $m The number of referees to generate.
     * @return array The generated list of referees, where each referee is 
     *               identified by a string in the format 'R1', 'R2', etc.
     */
    public function generateReferees(int $m): array
    {
        $referees = [];
        for ($i = 1; $i <= $m; $i++) {
            $referees[] = 'R' . $i;
        }
        return $referees;
    }
}
