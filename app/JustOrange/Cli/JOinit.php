<?php

namespace App\JustOrange\Cli;


use Illuminate\Console\Command;

class JOinit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'justorange:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run JustOrange initialization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("
 ┏┓╻ ╻┏━┓╺┳╸┏━┓┏━┓┏━┓┏┓╻┏━╸┏━╸
  ┃┃ ┃┗━┓ ┃ ┃ ┃┣┳┛┣━┫┃┗┫┃╺┓┣╸ 
┗━┛┗━┛┗━┛ ╹ ┗━┛╹┗╸╹ ╹╹ ╹┗━┛┗━╸
");
        $this->info("JustOrange Initalization");
        if (file_exists(dirname(app_path()) . '/.env')) {
            $this->info("[CHECK] .env Exists ............................................ [OK]");
        } else {
            $this->error("[CHECK[ .env Exists ............................................ [FAIL]");
            @copy(dirname(app_path()) . '/.env.example', dirname(app_path()) . '/.env');
            $this->info("[CHECK] .env Exists ............................................ [OK]");
            $this->call('key:generate');
        }
        $this->info('Running composer install...');
        exec('composer install', $composerOutput, $composerReturnVar);
        
        if ($composerReturnVar !== 0) {
            $this->error('Composer install failed');
            return;
        }
        
        $this->info('Composer install completed successfully.');

        $this->info('Running bun install...');
        exec('bun install', $bunOutput, $bunReturnVar);
        
        if ($bunReturnVar !== 0) {
            $this->error('Bun install failed');
            return;
        }

        $this->info('Bun install completed successfully.');
    }
}
