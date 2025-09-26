<?php

namespace App\JustOrange\Cli;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class JOServe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'justorange:dev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run php artisan serve and bun run dev concurrently';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       // Start php artisan serve
       $this->info('Starting php artisan serve...');
       $serveProcess = new Process(['php', 'artisan', 'serve']);
       $serveProcess->setTimeout(null);
       $serveProcess->start();

       // Start bun run dev
       $this->info('Starting bun run dev...');
       $bunProcess = new Process(['bun', 'run', 'dev']);
       $bunProcess->setTimeout(null);
       $bunProcess->start();

       // Handle serve process output asynchronously
       $serveProcess->wait(function ($type, $buffer) {
           if (Process::ERR === $type) {
               $this->error($buffer);
           } else {
               $this->info($buffer);
           }
       });

       // Handle bun process output asynchronously
       $bunProcess->wait(function ($type, $buffer) {
           if (Process::ERR === $type) {
               $this->error($buffer);
           } else {
               $this->info($buffer);
           }
       });

       if (!$serveProcess->isSuccessful()) {
           $this->error('php artisan serve failed.');
           return 1;
       }

       if (!$bunProcess->isSuccessful()) {
           $this->error('bun run dev failed.');
           return 1;
       }

       $this->info('Both processes have finished.');
       return 0;
    }

    
}
