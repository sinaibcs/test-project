<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use Str;
use Symfony\Component\Console\Input\InputArgument;
class all extends Command
{

/**
     * argumentName
     *
     * @var string
     */
    public $argumentName = 'all';

        /**
     * Name and signiture of Command.
     * name
     * @var string
     */
    protected $name = 'make:test';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

        /**
     * Get command agrumants - EX : UserService
     * getArguments
     *
     * @return void
     */
    protected function getArguments()
    {
        return [
            ['all', InputArgument::REQUIRED, 'The name of the all class.'],
        ];
    }

        /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function getServiceName()
    {
        $service = Str::studly($this->argument('all'));

        if (Str::contains(strtolower($service), 'service') === false) {
            $service .= 'Service';
        }

        return $service;
    }
    public function ReplaceNames($name){
        $originalString = $this->getServiceName();
$stringToReplace = "Service";
$replacementString = $name;

return  str_replace($stringToReplace, $replacementString, $originalString);
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Artisan::call('make:service', ['name' =>  $this->ReplaceNames('Service')]);
        Artisan::call('make:resource', ['name' =>  $this->ReplaceNames('Resource')]);
        Artisan::call('make:request', ['name' =>  $this->ReplaceNames('Request')]);
        Artisan::call('make:request', ['name' =>  $this->ReplaceNames('UpdateRequest')]);
        $this->info("all generated successfully!");
    }
}
