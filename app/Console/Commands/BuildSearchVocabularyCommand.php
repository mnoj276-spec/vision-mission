<?php

namespace App\Console\Commands;

use App\Domains\Jobs\Services\Contracts\SearchServiceInterface;
use Illuminate\Console\Command;

class BuildSearchVocabularyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:build-vocabulary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild and cache the vocabulary dictionary index for spell check corrections';

    /**
     * Execute the console command.
     */
    public function handle(SearchServiceInterface $searchService): int
    {
        $this->info('Starting vocabulary dictionary rebuild...');
        
        $searchService->rebuildVocabulary();
        
        $this->info('Vocabulary dictionary rebuild completed successfully and cached!');
        
        return Command::SUCCESS;
    }
}
