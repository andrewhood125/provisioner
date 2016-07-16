<?php

namespace Andrewhood125\Provisioner\Commands;

use Symfony\Component\Process\Process;
use Andrewhood125\Provisioner\SshProcess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProvisionDebianCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('remote:provision:debian')
            ->addArgument('host', InputArgument::REQUIRED,
                        'Host to provision. (example.org | 127.0.0.1)')
            ->setDescription('Provision your remote debian server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host');

        $outputFunction = function($type, $line) use ($output) {
            $output->write($line);
        };

        $stubs = __DIR__.'/../Stubs/';

        (new SshProcess("root@$host", 'wget -O - https://packagecloud.io/gpg.key | apt-key add -'))->run($outputFunction);
        (new SshProcess("root@$host", 'echo "deb http://http.debian.net/debian wheezy-backports main" | tee /etc/apt/sources.list.d/nodejs.list'))->run($outputFunction);
        (new SshProcess("root@$host", 'echo "deb http://packages.blackfire.io/debian any main" | tee /etc/apt/sources.list.d/blackfire.list'))->run($outputFunction);

        (new SshProcess("root@$host", 'apt-get update'))->run($outputFunction);
        (new SshProcess("root@$host", 'apt-get --yes --target-release wheezy-backports install nodejs'))->run($outputFunction);
        (new SshProcess("root@$host", 'ln -s /usr/bin/nodejs /usr/bin/node'))->run($outputFunction);
        (new SshProcess("root@$host", 'echo "LC_ALL=en_US.UTF-8" >> /etc/default/locale'))->run($outputFunction);
        (new SshProcess("root@$host", 'echo mysql-server mysql-server/root_password password secret | debconf-set-selections'))->run($outputFunction);
        (new SshProcess("root@$host", 'echo mysql-server mysql-server/root_password_again password secret | debconf-set-selections'))->run($outputFunction);
        (new SshProcess("root@$host", 'locale-gen en_US.UTF-8'))->run($outputFunction);
        (new SshProcess("root@$host", 'apt-get install --yes blackfire-agent blackfire-php bmon sudo mysql-server software-properties-common htop curl nginx redis-server php5-cli php5-fpm php5-mysql build-essential dos2unix gcc git libmcrypt4 libpcre3-dev make python2.7-dev python-pip re2c supervisor unattended-upgrades whois vim libnotify-bin memcached beanstalkd'))->run($outputFunction);
        (new SshProcess("root@$host", 'apt-get upgrade --yes'))->run($outputFunction);
        (new SshProcess("root@$host", 'curl -sS https://getcomposer.org/installer | php'))->run($outputFunction);
        (new SshProcess("root@$host", 'mv composer.phar /usr/bin/composer'))->run($outputFunction);
        (new SshProcess("root@$host", 'curl -SsLo- https://npmjs.org/install.sh | sh'))->run($outputFunction);
        (new SshProcess("root@$host", 'ln -sf /usr/share/zoneinfo/UTC /etc/localtime'))->run($outputFunction);
        (new SshProcess("root@$host", '/bin/dd if=/dev/zero of=/var/swap.1 bs=1M count=1024'))->run($outputFunction);
        (new SshProcess("root@$host", 'chmod 0600 /var/swap.1'))->run($outputFunction);
        (new SshProcess("root@$host", '/sbin/mkswap /var/swap.1'))->run($outputFunction);
        (new SshProcess("root@$host", '/sbin/swapon /var/swap.1'))->run($outputFunction);
        (new Process("scp $stubs/serve-laravel.sh root@$host:"))->run($outputFunction);
        (new SshProcess("root@$host", 'mv serve-laravel.sh /usr/bin/'))->run($outputFunction);
        (new SshProcess("root@$host", 'chmod +x /usr/bin/serve-laravel.sh'))->run($outputFunction);
        (new SshProcess("root@$host", 'sed -i "s/user www-data;/user deployer;/" /etc/nginx/nginx.conf'))->run($outputFunction);
        (new SshProcess("root@$host", 'sed -i "s/# server_names_hash_bucket_size.*/server_names_hash_bucket_size 64;/" /etc/nginx/nginx.conf'))->run($outputFunction);
        (new SshProcess("root@$host", 'sed -i "s/user = www-data/user = deployer/" /etc/php5/fpm/pool.d/www.conf'))->run($outputFunction);
        (new SshProcess("root@$host", 'sed -i "s/group = www-data/group = deployer/" /etc/php5/fpm/pool.d/www.conf'))->run($outputFunction);
        (new SshProcess("root@$host", 'sed -i "s/listen\.owner.*/listen.owner = deployer/" /etc/php5/fpm/pool.d/www.conf'))->run($outputFunction);
        (new SshProcess("root@$host", 'sed -i "s/listen\.group.*/listen.group = deployer/" /etc/php5/fpm/pool.d/www.conf'))->run($outputFunction);
        (new SshProcess("root@$host", 'sed -i "s/;listen\.mode.*/listen.mode = 0666/" /etc/php5/fpm/pool.d/www.conf'))->run($outputFunction);
        (new SshProcess("root@$host", 'rm /etc/nginx/sites-enabled/default*'))->run($outputFunction);
        (new SshProcess("root@$host", 'rm /etc/nginx/sites-available/default*'))->run($outputFunction);
        (new SshProcess("root@$host", 'adduser --disabled-password --gecos "" deployer'))->run($outputFunction);
        (new SshProcess("root@$host", 'mkdir ~deployer/.ssh'))->run($outputFunction);
        (new SshProcess("root@$host", 'cp ~/.ssh/authorized_keys ~deployer/.ssh/'))->run($outputFunction);
        (new SshProcess("root@$host", 'chown -R deployer:deployer ~deployer/.ssh/'))->run($outputFunction);
        (new Process("scp $stubs/sudo root@$host:"))->run($outputFunction);
        (new SshProcess("root@$host", 'mv sudo /etc/sudoers.d/ondamanda'))->run($outputFunction);
    }
}
