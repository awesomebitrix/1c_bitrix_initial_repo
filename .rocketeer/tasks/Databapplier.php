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
        $local_db_user =            $this->rocketeer->getOption('config.local.db.user');
        $local_db_password =        $this->rocketeer->getOption('config.local.db.password');
        $local_db_name =            $this->rocketeer->getOption('config.local.db.name');
        $local_db_backups_path =    $this->rocketeer->getOption('config.local.db.backups_path');
        $local_mysql_path =         $this->rocketeer->getOption('config.local.db.mysql_path');

        $remote_db_host =           $this->rocketeer->getOption('remote.db.' . $connection . '.host');
        $remote_db_user =           $this->rocketeer->getOption('remote.db.' . $connection . '.user');
        $remote_db_password =       $this->rocketeer->getOption('remote.db.' . $connection . '.password');
        $remote_db_name =           $this->rocketeer->getOption('remote.db.' . $connection . '.name');
        $remote_db_backups_path =   $this->rocketeer->getOption('remote.db.' . $connection . '.backups_path');
        $remote_login_user =        $this->rocketeer->getOption('config.connections.' . $connection . '.username');
        $remote_login_host =        $this->rocketeer->getOption('config.connections.' . $connection . '.hostonly');
        $remote_login_port =        $this->rocketeer->getOption('config.connections.' . $connection . '.ssh_port');

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

        $this->command->info("Purging existing local DB...");
        //$command = "{$local_mysql_path} -u {$local_db_user} -p{$local_db_password} -e \"DROP DATABASE IF EXISTS `{$local_db_name}`; CREATE DATABASE `{$local_db_name}` CHARACTER SET utf8 COLLATE utf8_general_ci;\"";
        $command = "
            {$local_mysql_path} -u {$local_db_user} -p{$local_db_password} -e \"
            MUSER=\"$1\"
            MPASS=\"$2\"
            MDB=\"$3\"
             
            # Detect paths
            MYSQL=$(which mysql)
            AWK=$(which awk)
            GREP=$(which grep)
             
            if [ $# -ne 3 ]
            then
                echo \"Usage: $0 {MySQL-User-Name} {MySQL-User-Password} {MySQL-Database-Name}\"
                echo \"Drops all tables from a MySQL\"
                exit 1
            fi
             
            TABLES=$({$local_mysql_path} -u {$local_db_user} -p{$local_db_password} {$local_db_name} -e 'show tables' | \$AWK '{ print $1}' | \$GREP -v '^Tables' )
             
            for t in \$TABLES
            do
                echo \"Deleting \$t table from \$MDB database...\"
                \$MYSQL -u \$MUSER -p\$MPASS \$MDB -e \"drop table \$t\"
            done
            \"
        ";
        //$this->command->info($command);
        exec($command);
        $this->command->info("Pushing DB to local MySQL DBMS...");
        $command = "{$local_mysql_path} -u {$local_db_user} -p{$local_db_password} {$local_db_name} < \"{$local_db_backups_path}/{$backup_name}\"";
        $this->command->info($command);
        exec($command);
    }
}