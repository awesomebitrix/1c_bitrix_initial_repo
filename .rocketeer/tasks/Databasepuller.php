<?php

namespace Rocketeer\Tasks;

use Rocketeer\Abstracts\AbstractTask;

class Databasepuller extends AbstractTask
{
    protected $description = 'Pulls database from remote to local';

    public function execute()
    {
        $connection = $this->connections->getConnection();
        $stage = $this->connections->getStage();

        // get options
        $local_db_user =                    $this->rocketeer->getOption('config.local.db.user');
        $local_db_password =                $this->rocketeer->getOption('config.local.db.password');
        $local_db_name =                    $this->rocketeer->getOption('config.local.db.name');
        $local_db_backups_path =            $this->rocketeer->getOption('config.local.db.backups_path');
        $local_keep_backups =               $this->rocketeer->getOption('config.local.db.keep_backups');
        $local_windows_dir_to_rsync_ssh =   $this->rocketeer->getOption('config.local.windows_dir_to_rsync_ssh');

        $remote_db_host =           $this->rocketeer->getOption('remote.db.' . $connection . '.host');
        $remote_db_user =           $this->rocketeer->getOption('remote.db.' . $connection . '.user');
        $remote_db_password =       $this->rocketeer->getOption('remote.db.' . $connection . '.password');
        $remote_db_name =           $this->rocketeer->getOption('remote.db.' . $connection . '.name');
        $remote_db_backups_path =   $this->rocketeer->getOption('remote.db.' . $connection . '.backups_path');
        $remote_login_user =        $this->rocketeer->getOption('config.connections.' . $connection . '.username');
        $remote_login_host =        $this->rocketeer->getOption('config.connections.' . $connection . '.hostonly');
        $remote_login_port =        $this->rocketeer->getOption('config.connections.' . $connection . '.ssh_port');

        $root_directory = $this->paths->getHomeFolder() . $this->connections->getStage();

        $backup_prefix = $connection . '_';
        $backup_file_name = $backup_prefix . date("Y_m_d_H_i_s");

        $local_db_backup_file_path = $local_db_backups_path . '/' . $backup_file_name . '.sql';
        $local_db_compressed_backup_file_path = $local_db_backup_file_path . '.tar.gz';

        $remote_db_backup_directory = $root_directory . '/' . $remote_db_backups_path;
        $remote_db_backup_file_path = $remote_db_backup_directory . '/' . $backup_file_name . '.sql';
        $remote_db_compressed_backup_file_path = $remote_db_backup_file_path . '.tar.gz';

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

        $this->command->info('Running remote command on connection: ' . $connection);
        if (!$this->fileExists($remote_db_backup_directory)) {
            $this->command->info('Initializing backup folder');
            $this->remote->run('mkdir -p ' . $remote_db_backup_directory);
        }
        $this->command->info('Doing backup of remote DB: ' . "{$remote_db_backup_directory}/{$backup_file_name}.sql");
        $this->remote->run(array(
            "touch {$remote_db_backup_directory}/{$backup_file_name}.sql",
            'mysqldump --single-transaction --user=' . $remote_db_user . ' --password=' . $remote_db_password . ' --host=' . $remote_db_host . ' ' . $remote_db_name . ' > ' . $remote_db_backup_directory . '/' . $backup_file_name . '.sql',
            // compressing result
            "tar -czvf {$remote_db_compressed_backup_file_path} {$remote_db_backup_file_path}",
            // remove raw backup
            "rm {$remote_db_backup_file_path}"
        ));


        $this->command->info("Creating backups folder: " . $local_db_backups_path);
        $options = '';
        if ($env == 'linux') $options = '-p';
        $command = "mkdir {$options} $local_db_backups_path";
        if ($env == 'windows') $command = str_replace('/', '\\', $command);
        $this->command->info($command);
        exec($command);
        $this->command->info("Getting backup from remote host to: " . $local_db_compressed_backup_file_path);
        $command = "rsync -avz --rsh='{$local_windows_dir_to_rsync_ssh}ssh -p {$remote_login_port} -i {$private_key_path}' {$remote_login_user}@{$remote_login_host}:{$remote_db_compressed_backup_file_path} {$local_db_compressed_backup_file_path}";
        exec($command);

        // remove old local backups
        $filesNames = scandir($local_db_backups_path, SCANDIR_SORT_DESCENDING);
        $localBackupNames = [];
        foreach ($filesNames as $fileName) {
            if (strpos($fileName, $backup_prefix) === 0) {
                $localBackupNames[] = $fileName;
            }
        }
        if (($filesToRemove = (count($localBackupNames) - $local_keep_backups)) > 0) {
            while ($filesToRemove > 0) {
                $fileToRemove = array_pop($localBackupNames);
                unlink($local_db_backups_path . '/' . $fileToRemove);
                $this->command->info('Old local backup removed: ' . $fileToRemove);
                $filesToRemove--;
            }
        }

        // ToDo: remove old remote backups
    }
}