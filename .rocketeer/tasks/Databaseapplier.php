<?php

namespace Rocketeer\Tasks;

use Rocketeer\Abstracts\AbstractTask;

class Databaseapplier extends AbstractTask
{
    /**
     * @var string
     */
    protected $description = 'Applies the newest (alphabetically) database taken from this machine to MySQL DBMS';

    public function execute()
    {
        $connection = $this->connections->getConnection();
        $stage = $this->connections->getStage();

        // Set DB & Remote data
        $local_db_user =                    $this->rocketeer->getOption('config.local.db.user');
        $local_db_password =                $this->rocketeer->getOption('config.local.db.password');
        $local_db_name =                    $this->rocketeer->getOption('config.local.db.name');
        $local_db_charset =                 $this->rocketeer->getOption('config.local.db.charset');
        $local_db_backups_path =            $this->rocketeer->getOption('config.local.db.backups_path');
        $local_mysql_path =                 $this->rocketeer->getOption('config.local.db.mysql_path');

        $remote_db_host =                   $this->rocketeer->getOption('remote.db.' . $connection . '.host');
        $remote_db_user =                   $this->rocketeer->getOption('remote.db.' . $connection . '.user');
        $remote_db_password =               $this->rocketeer->getOption('remote.db.' . $connection . '.password');
        $remote_db_name =                   $this->rocketeer->getOption('remote.db.' . $connection . '.name');
        $remote_db_backups_path =           $this->rocketeer->getOption('remote.db.' . $connection . '.backups_path');
        $remote_login_user =                $this->rocketeer->getOption('config.connections.' . $connection . '.username');
        $remote_login_connection_point =    $this->rocketeer->getOption('config.connections.' . $connection . '.host');
        $divided_connection_point =         explode(":", $remote_login_connection_point);
        $remote_login_host =                count($divided_connection_point) == 1 ? $remote_login_connection_point : $divided_connection_point[0];
        $remote_login_port =                count($divided_connection_point) == 1 ? 22 : $divided_connection_point[1];

        $env = !empty($_SERVER['HOMEDRIVE']) ? 'windows' : 'linux';
        $homePath = '';
        switch ($env) {
            case 'windows':
                $homePath = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
                break;
            case 'linux':
                $homePath = $_SERVER['HOME'];
                break;
        }

        $backup_name = '';
        $backup_prefix = $connection . '_';
        $filesNames = scandir($local_db_backups_path, SCANDIR_SORT_DESCENDING);
        foreach ($filesNames as $fileName) {
            if (strpos($fileName, $backup_prefix) === 0) {
                $backup_name = $fileName;
                break;
            }
        }
        if (!empty($backup_name)) {
            $this->command->info('Newest backup found: ' . $backup_name);
        } else {
            $this->command->info("No appropriate backups for connection: " . $connection);
            return false;
        }

        $this->command->info("Purging existing local DB with php...");
        $db = new \PDO("mysql:host=localhost;dbname={$local_db_name};charset={$local_db_charset}", $local_db_user, $local_db_password);
        $result = $db->query("show tables");
        while ($row = $result->fetch(\PDO::FETCH_NUM)) {
            $db->query("DROP TABLE $row[0]");
        }

        $this->command->info("Pushing DB to local MySQL DBMS...");
        $archiveSuffix = '.tar.gz';
        if (strpos($backup_name, $archiveSuffix) == strlen($backup_name) - strlen($archiveSuffix)) {
            $command = "tar -xzOf \"{$local_db_backups_path}/{$backup_name}\" | {$local_mysql_path} -u {$local_db_user} -p{$local_db_password} {$local_db_name}";
        } else {
            $command = "{$local_mysql_path} -u {$local_db_user} -p{$local_db_password} {$local_db_name} < \"{$local_db_backups_path}/{$backup_name}\"";
        }

        $this->command->info($command);
        exec($command);
    }
}