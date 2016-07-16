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
                        'Host to install your project at. user@domain')
            ->setDescription('Install your project on provisioned remote server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $input->getArgument('project');
        $dir = explode("/", $project)[1];
        $host = $input->getArgument('host');
        $user = explode("@", $host)[0];
        $dbuser = preg_replace("/-/","_",$dir);


        if($user == 'root') {
            $output->write('You\'re root. Try again as deployer.');
            return;
        }

        $outputFunction = function($type, $line) use ($output) {
            $output->write($line);
        };

        (new SshProcess($host, "git clone https://github.com/$project"))->run($outputFunction);
        (new SshProcess($host, "sudo serve-laravel.sh $user $dir"))->run($outputFunction);
        (new SshProcess($host, 'mysql -uroot -psecret --execute "CREATE USER \''. $dbuser .'\'@\'localhost\' IDENTIFIED BY \'secret\';"'))->run($outputFunction);
        (new SshProcess($host, 'mysql -uroot -psecret --execute "CREATE DATABASE '. $dbuser .';"'))->run($outputFunction);
        (new SshProcess($host, 'mysql -uroot -psecret --execute "GRANT ALL PRIVILEGES ON '.$dbuser.'. * TO \''.$dbuser.'\'@\'localhost\';"'))->run($outputFunction);
        (new SshProcess($host, 'mysql -uroot -psecret --execute "FLUSH PRIVILEGES;"'))->run($outputFunction);
        (new SshProcess($host, "cd $dir && ./after.sh"))->run($outputFunction);
    }
}
