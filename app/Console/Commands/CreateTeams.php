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
    protected $signature = 'create:teams {n} {m}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command will create teams for the league and generate a plan for the week plays the teams. n is the number of teams.';

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
        $numberOfReferee = $this->argument('m') ?? 4;

        $teams = new TeamHelper($numberOfTeam, $numberOfReferee);
        $data = $teams->generatePlanForWeeklyGames();
        if (is_string($data)) {
            $this->error($data);
            return;
        }
        $this->table(['Week Number', 'Team 1 (home)', 'Team 2 (Away)', 'Referee', 'Date','round'], $data);

    }
}
