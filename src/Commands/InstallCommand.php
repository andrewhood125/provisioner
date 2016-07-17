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
            ->setName('remote:install')
            ->addArgument('project', InputArgument::REQUIRED,
                        'The project you would like to install on GitHub. username/repository')
            ->addArgument('host', InputArgument::REQUIRED,
                        'Host to install your project at. (example.org | 127.0.0.1)')
            ->setDescription('Install your project on provisioned remote server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $input->getArgument('project');
        $dir = explode("/", $project)[1];
        $host = $input->getArgument('host');
        $dbuser = preg_replace("/-/","_", $dir);

        $outputFunction = function($type, $line) use ($output) {
            $output->write($line);
        };

        (new SshProcess("deployer@$host", "git clone git@github.com:$project.git"))->run($outputFunction);
        (new SshProcess("deployer@$host", "sudo serve-laravel.sh deployer $dir"))->run($outputFunction);
        (new SshProcess("deployer@$host", 'mysql -uroot -psecret --execute "CREATE USER \''. $dbuser .'\'@\'localhost\' IDENTIFIED BY \'secret\';"'))->run($outputFunction);
        (new SshProcess("deployer@$host", 'mysql -uroot -psecret --execute "CREATE DATABASE '. $dbuser .';"'))->run($outputFunction);
        (new SshProcess("deployer@$host", 'mysql -uroot -psecret --execute "GRANT ALL PRIVILEGES ON '.$dbuser.'. * TO \''.$dbuser.'\'@\'localhost\';"'))->run($outputFunction);
        (new SshProcess("deployer@$host", 'mysql -uroot -psecret --execute "FLUSH PRIVILEGES;"'))->run($outputFunction);
        (new SshProcess("root@$host", "cd ~deployer/$dir && bash after.sh"))->run($outputFunction);
    }
}
