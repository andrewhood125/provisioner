<?php

namespace Andrewhood125\Provisioner\Commands;


use Andrewhood125\Provisioner\SshProcess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('install')
            ->addArgument('project', InputArgument::REQUIRED,
                        'The project you would like to install on GitHub. username/repository')
            ->addArgument('host', InputArgument::REQUIRED,
                        'Host to install your project at. user@domain')
            ->setDescription('Install your project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $input->getArgument('project');
        $dir = explode("/", $project)[1];
        $host = $input->getArgument('host');
        $user = explode("@", $host)[0];

        if($user == 'root') {
            $output->write('Are you sure you want to run as root?');
            return;
        }

        $outputFunction = function($type, $line) use ($output) {
            $output->write($line);
        };

        (new SshProcess($host, 'git clone https://github.com/' . $project))->run($outputFunction);
        (new SshProcess($host, 'cd '.$dir.';composer install'))->run($outputFunction);
        (new SshProcess($host, 'sudo serve-laravel.sh '.$user.' '.$dir))->run($outputFunction);

    }
}
