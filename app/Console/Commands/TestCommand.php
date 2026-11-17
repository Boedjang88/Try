<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test 
        {filter? : Filter which tests to run}
        {--testsuite= : Filter by testsuite}
        {--stop-on-failure : Stop execution upon first error or failure}
        {--colors=always : Use colors in output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the PHPUnit automated test suite';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseDir = base_path();
        $phpunitBin = $baseDir . '/vendor/bin/phpunit';

        if (!file_exists($phpunitBin)) {
            $this->error("PHPUnit binary not found at {$phpunitBin}");
            return Command::FAILURE;
        }

        $cmd = [$phpunitBin];

        if ($filter = $this->argument('filter')) {
            $cmd[] = '--filter';
            $cmd[] = $filter;
        }

        if ($testsuite = $this->option('testsuite')) {
            $cmd[] = '--testsuite';
            $cmd[] = $testsuite;
        }

        if ($this->option('stop-on-failure')) {
            $cmd[] = '--stop-on-failure';
        }

        $cmd[] = '--colors=always';

        $env = array_merge($_SERVER, $_ENV, [
            'APP_ENV' => 'testing',
            'CACHE_STORE' => 'array',
            'SESSION_DRIVER' => 'array',
        ]);

        $process = new Process($cmd, $baseDir, $env, null, null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode() ?? Command::SUCCESS;
    }
}
