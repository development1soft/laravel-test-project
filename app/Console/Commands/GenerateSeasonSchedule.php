<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TeamRefereeService;
use App\Services\MatchSchedulerService;

/**
 * GenerateSeasonSchedule
 *
 * Artisan command for generating a football season schedule. This command utilizes
 * the `TeamRefereeService` to generate teams and referees, and the `MatchSchedulerService`
 * to create matches, assign referees, schedule matches, and calculate the minimum number
 * of referees required. It outputs the schedule in a weekly format along with the minimum
 * referees needed.
 */
class GenerateSeasonSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'season:generate {n} {m}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a football season schedule';

    /**
     * The service responsible for generating teams and referees.
     *
     * @var TeamRefereeService
     */
    private TeamRefereeService $teamRefereeService;

    /**
     * The service responsible for scheduling matches and assigning referees.
     *
     * @var MatchSchedulerService
     */
    private MatchSchedulerService $matchSchedulerService;

    /**
     * Create a new command instance.
     *
     * @param TeamRefereeService $teamRefereeService Service for generating teams and referees.
     * @param MatchSchedulerService $matchSchedulerService Service for scheduling matches and assigning referees.
     * @return void
     */
    public function __construct(TeamRefereeService $teamRefereeService, MatchSchedulerService $matchSchedulerService)
    {
        parent::__construct();
        $this->teamRefereeService = $teamRefereeService;
        $this->matchSchedulerService = $matchSchedulerService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $n = (int) $this->argument('n');
        $m = (int) $this->argument('m');

        // Validate that the number of teams is even
        if ($n % 2 !== 0) {
            $this->error('The number of teams (n) must be even.');
            return;
        }

        // Generate teams and referees
        $teams = $this->teamRefereeService->generateTeams($n);
        $referees = $this->teamRefereeService->generateReferees($m);

        // Generate matches and assign referees
        $matches = $this->matchSchedulerService->generateMatches($teams);
        $matchesWithReferees = $this->matchSchedulerService->assignReferees($matches, $referees);

        // Schedule matches across weeks
        $weeks = $this->matchSchedulerService->scheduleMatches($matchesWithReferees);

        // Output the weekly schedule
        foreach ($weeks as $week => $weekMatches) {
            $this->info("Week $week");
            foreach ($weekMatches as $match) {
                $this->info("{$match['home']} vs {$match['away']} | Referee: {$match['referee']} | Date: {$match['date']} | Round: {$match['round']}");
            }
        }

        // Output the minimum number of referees needed
        $minReferees = $this->matchSchedulerService->calculateMinReferees($matchesWithReferees);
        $this->info("Minimum referees needed: $minReferees");
    }
}