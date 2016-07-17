<?php

namespace Andrewhood125\Provisioner;

use Symfony\Component\Process\Process;

class SshProcess extends Process
{
    public function __construct($host, $command) {
        parent::__construct("ssh -A $host 'bash -se' << HEREFILE".PHP_EOL
            .'export DEBIAN_FRONTEND=noninteractive'.PHP_EOL
            .$command.PHP_EOL
            .'HEREFILE');
        $this->setTimeout(600);
    }
}
