<?php

namespace Encore\Laredis\Console;

use Encore\Laredis\Server\Server;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'redis-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve the redis service';

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function fire()
    {
        //chdir($this->laravel->publicPath());

        $host = $this->input->getOption('host');
        $port = $this->input->getOption('port');

        $action = $this->argument('action');

        $server = new Server("tcp://$host:$port");
        try {
            $server->$action();
        } catch (Exception $e) {
            dump($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', '127.0.0.1'],
            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 6379],
            ['daemon', '-d', InputOption::VALUE_OPTIONAL, 'serve the application in DAEMON mode.', false],
        ];
    }

    protected function getArguments()
    {
        return [
            ['action', InputArgument::OPTIONAL, 'The name of the store you would like to clear.', 'usage'],
        ];
    }
}
