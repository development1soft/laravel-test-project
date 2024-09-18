<?php

namespace App\Services;

class TeamRefereeService
{
    public function generateTeams(int $n): array
    {
        $teams = [];
        for ($i = 1; $i <= $n; $i++) {
            $teams[] = 'T' . $i;
        }
        return $teams;
    }

    public function generateReferees(int $m): array
    {
        $referees = [];
        for ($i = 1; $i <= $m; $i++) {
            $referees[] = 'R' . $i;
        }
        return $referees;
    }
}