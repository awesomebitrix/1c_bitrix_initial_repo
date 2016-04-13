<?php

namespace Rocketeer\Tasks;

class Uploadspuller extends \Rocketeer\Abstracts\AbstractTask
{
    protected $description = 'Pulls uploads from remote server';

    public function execute()
    {
        $stage = $this->connections->getStage();
        $connection = $this->connections->getConnection();

        $local_upload_path = $this->rocketeer->getOption('config.local.files.upload_dest_path');

        $remote_login_user =                $this->rocketeer->getOption('config.connections.' . $connection . '.username');
        $remote_login_connection_point =    $this->rocketeer->getOption('config.connections.' . $connection . '.host');
        $divided_connection_point =         explode(":", $remote_login_connection_point);
        $remote_login_host =                count($divided_connection_point) == 1 ? $remote_login_connection_point : $divided_connection_point[0];
        $remote_login_port =                count($divided_connection_point) == 1 ? 22 : $divided_connection_point[1];
        $remote_upload_path =               $this->rocketeer->getOption('remote.db.' . $connection . '.upload_path');
        $remote_upload_path =               $this->paths->getHomeFolder() . '/' . $remote_upload_path;

        $local_windows_dir_to_rsync =       $this->rocketeer->getOption('config.local.windows_dir_to_rsync');

        $this->command->info('Pulling uploads');

        $env = !empty($_SERVER['HOMEDRIVE']) ? 'windows' : 'linux';
        $home_path = '';
        switch ($env) {
            case 'windows':
                $home_path = $_SERVER['HOMEDRIVE'] . '/' . $_SERVER['HOMEPATH'];
                break;
            case 'linux':
                $home_path = $_SERVER['HOME'];
                break;
        }
        
        $private_key_path = $home_path . "/.ssh/id_rsa";

        $this->command->info("Getting backup from remote host ...");
        $command = "rsync -avzO --rsh='{$local_windows_dir_to_rsync}ssh -p {$remote_login_port} -i {$private_key_path}' {$remote_login_user}@{$remote_login_host}:{$remote_upload_path} {$local_upload_path}";
        $this->command->info($command);
        exec(
            $command
        );
    }
}