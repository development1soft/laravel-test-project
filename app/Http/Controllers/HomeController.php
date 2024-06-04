<?php
namespace App\Http\Controllers;
use App\Helper\TeamHelper;
class HomeController
{
    public function index($number = null)
    {

        $numberOfTeam = $number ?? 6;
        $teams = TeamHelper::createTeamForLeg($numberOfTeam);

        $data = TeamHelper::generatePlanForWeeklyGames($teams);

        return view('welcome', compact('data'));

    }
}
