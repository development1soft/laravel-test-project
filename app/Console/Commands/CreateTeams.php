<?php

namespace App\Console\Commands;

use App\Helper\TeamHelper;
use Illuminate\Console\Command;

class CreateTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:teams {n}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command will create teams for the league and generate a plan for the week plays the teams. n is the number of teams. Default is 6.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!is_numeric($this->argument('n')) || $this->argument('n') < 1 || $this->argument('n') == 1) {
            $this->error('Invalid number of teams');
            return;
        }
        $numberOfTeam  = $this->argument('n') ?? 6;
        $teams = TeamHelper::createTeamForLeg($numberOfTeam);

        $data = TeamHelper::generatePlanForWeeklyGames($teams);

        $this->table(['Week Number', 'Team 1 (home)', 'Team 2 (Away)', 'Referee', 'Date', 'Round'], $data);

    }
}
