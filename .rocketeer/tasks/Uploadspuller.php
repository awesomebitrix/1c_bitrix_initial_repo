<?php

class Uploadspuller extends \Rocketeer\Abstracts\AbstractTask
{
    protected $description = 'Pull uploads from remote server';

    public function execute()
    {
        $stage = $this->connections->getStage();
        $connection = $this->connections->getConnection();

        $local_upload_path = $this->rocketeer->getOption('config.local.files.upload_path');

        $remote_login_user = $this->rocketeer->getOption('config.connections.' . $connection . '.username');
        $remote_login_host = $this->rocketeer->getOption('config.connections.' . $connection . '.hostonly');
        $remote_login_port = $this->rocketeer->getOption('config.connections.' . $connection . '.port');
        $remote_upload_path = $this->rocketeer->getOption('remote.db.' . $connection . '.upload_path');

        $remote_upload_path = $this->paths->getHomeFolder() . '/' . $remote_upload_path;

        $this->command->info('Pulling uploads');

        $env = !empty($_SERVER['HOMEDRIVE']) ? 'windows' : 'linux';
        $homePath = '';
        switch ($env) {
            case 'windows':
                $homePath = $_SERVER['HOMEDRIVE'] . '/' . $_SERVER['HOMEPATH'];
                break;
            case 'linux':
                $homePath = $_SERVER['HOME'];
                break;
        }

        $this->command->info("Getting backup from remote host ...");
        exec(
            "rsync -avzO --rsh='ssh -p {$remote_login_port} -i {$homePath}/.ssh/id_rsa' {$remote_login_user}@{$remote_login_host}:{$remote_upload_path} {$local_upload_path}"
        );
    }
}