<?php

namespace Rocketeer\Tasks;

use Rocketeer\Abstracts\AbstractTask;

class Databasepusher extends AbstractTask
{
    protected $description = 'Pushes locally saved database backup to remote database according to selected connection';

    public function execute()
    {
        $this->command->info($redeclare);
        $connection = $this->connections->getConnection();
        $stage = $this->connections->getStage();

        // Set DB & Remote data
        $local_db_user = $this->rocketeer->getOption('config.local.db.user');
        $local_db_password = $this->rocketeer->getOption('config.local.db.password');
        $local_db_name = $this->rocketeer->getOption('config.local.db.name');
        $local_db_backups_path = $this->rocketeer->getOption('config.local.db.backups_path');

        $remote_db_host =           $this->rocketeer->getOption('remote.db.' . $connection . '.host');
        $remote_db_user =           $this->rocketeer->getOption('remote.db.' . $connection . '.user');
        $remote_db_password =       $this->rocketeer->getOption('remote.db.' . $connection . '.password');
        $remote_db_name =           $this->rocketeer->getOption('remote.db.' . $connection . '.name');
        $remote_db_backups_path =   $this->rocketeer->getOption('remote.db.' . $connection . '.backups_path');
        $remote_login_user =        $this->rocketeer->getOption('config.connections.' . $connection . '.username');
        $remote_login_host =        $this->rocketeer->getOption('config.connections.' . $connection . '.hostonly');
        $remote_login_port =        $this->rocketeer->getOption('config.connections.' . $connection . '.ssh_port');

        $backup_name = date("Y_m_d_H_i_s");

        $root_directory = $this->paths->getHomeFolder() . '/' . $this->connections->getStage();
        $remote_backup_directory = $root_directory . $remote_db_backups_path;

        $detectEnvironment = !empty($_SERVER['HOMEDRIVE']) ? 'windows' : 'linux';
        $homePath = '';
        switch ($detectEnvironment) {
            case 'windows':
                $homePath = $_SERVER['HOMEDRIVE'] . '/' . $_SERVER['HOMEPATH'];
            break;
            case 'linux':
                $homePath = $_SERVER['HOME'];
            break;
        }

        $backup_name = '';
        $backup_prefix = $connection . '_';
        $filesNames = scandir($local_db_backups_path, SCANDIR_SORT_DESCENDING);
        foreach ($filesNames as $fileName) {
            if (strpos($fileName, $connection . '_') === 0) {
                $backup_name = $fileName;
                break;
            }
        }
        if (!empty($backup_name)) {
            $this->command->info('Newest backup found locally: ' . $backup_name);
            $this->command->info('Using connection: ' . $connection);
        } else {
            $this->command->info("No appropriate backups for connection: " . $connection);
            return false;
        }

        // connect to remote host and get files list in backup folder
        // detect if there already such backup file with $backup_name
        $remote_backup_file_path = $remote_backup_directory . '/' . $backup_name;
        $this->command->info('running remote command...');
        $this->remote->run("
            if [ -f '{$remote_backup_file_path}' ]
            then
                echo 'true'
            else
                echo 'false'
            fi
            ");
        $this->command->info('status: ' . $this->command->checkStatus());

        /*if (strpos($result, 'true') !== false) {
            $this->command->info('Backup found on remote server: ' . $backup_name);
        } else {
            // if not - upload it using rsync and apply to remote DBMS
            $this->command->info('this feature is not implemented yet.');
            return false;
        }

        // if so - empty existing and apply founded to remote
        $this->command->info("Dropping existing database...");
        // empties tables
        $this->remote->run('
                MUSER="' . $remote_db_user . '"
                MPASS="' . $remote_db_password . '"
                MDB="' . $remote_db_name . '"

                # Detect paths
                MYSQL=$(which mysql)
                AWK=$(which awk)
                GREP=$(which grep)

                TABLES=$($MYSQL -u $MUSER -p$MPASS $MDB -e "show tables" | $AWK "{ print $1}" | $GREP -v "^Tables" )

                for t in $TABLES
                do
                    echo "Deleting $t table from $MDB database..."
                    $MYSQL -u $MUSER -p$MPASS $MDB -e "drop table $t"
                done
            ');
        $this->command->info('Applying backup to remote database...');
        $this->remote->run("mysql --single-transaction --user={$remote_db_user} --password={$remote_db_password} {$remote_db_name} < {$remote_backup_file_path}");*/
    }
}