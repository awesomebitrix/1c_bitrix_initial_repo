<?php

use Rocketeer\Services\Connections\ConnectionsHandler;

return [

    // The name of the application to deploy
    // This will create a folder of the same name in the root directory
    // configured above, so be careful about the characters used
    'application_name' => 'totaldict',

    // Plugins
    ////////////////////////////////////////////////////////////////////

    // The plugins to load
    'plugins'          => [// 'Rocketeer\Plugins\Slack\RocketeerSlack',
    ],

    // Logging
    ////////////////////////////////////////////////////////////////////

    // The schema to use to name log files
    'logs'             => function (ConnectionsHandler $connections) {
        return sprintf('%s-%s-%s.log', $connections->getConnection(), $connections->getStage(), date('Ymd'));
    },

    // Remote access
    //
    // You can either use a single connection or an array of connections
    ////////////////////////////////////////////////////////////////////

    // The default remote connection(s) to execute tasks on
    'default'          => ['preprod'],

    // The various connections you defined
    // You can leave all of this empty or remove it entirely if you don't want
    // to track files with credentials : Rocketeer will prompt you for your credentials
    // and store them locally
    'connections'      => [
        'preprod' => [
            // this field may contain port so it's for ssh purposes
            'host'      => 'www.domain.xxx[:port]',
            // this field should not contain port. ip address or hostname
            'hostonly'  => 'www.domain.xxx',
            'ssh_port'      => 22,
            'username'  => '',
            'password'  => '',
            'key'       => '',
            'keyphrase' => '',
            'agent'     => '',
            'db_role'   => true,
        ],
    ],

    /*
     * In most multiserver scenarios, migrations must be run in an exclusive server.
     * In the event of not having a separate database server (in which case it can
     * be handled through connections), you can assign a 'db_role' => true to the
     * server's configuration and it will only run the migrations in that specific
     * server at the time of deployment.
     */
    'use_roles'        => false,

    // Contextual options
    //
    // In this section you can fine-tune the above configuration according
    // to the stage or connection currently in use.
    // Per example :
    // 'stages' => array(
    // 	'staging' => array(
    // 		'scm' => array('branch' => 'staging'),
    // 	),
    //  'production' => array(
    //    'scm' => array('branch' => 'master'),
    //  ),
    // ),
    ////////////////////////////////////////////////////////////////////

    'on'               => [

        // Stages configurations
        'stages'      => [],
        // Connections configuration
        'connections' => [],

    ],

    // options for database manipulations tasks
    'local' => [
        // for windows users only to fix ssh bug. so if u linux user leave this field empty. this string should end with slash
        'windows_dir_to_rsync_ssh' => 'C:/Users/bfday/Cloud@Mail.Ru/Progs/Portable/cwRsync_5.5.0_x86_Free/bin/',
        'db' => [
            'host' => 'localhost',
            'user' => '',
            'password' => '',
            'name' => '',
            'charset' => '',
            'domain_name' => 'local-intraceuticals.ru',
            'mysql_path' => 'mysql',
            'mysqldump_path' => 'mysqldump',
            // from that folder where rocketeer.phar is located
            'backups_path' => 'web/shared/backups/db',
            'keep_backups' => 1,
        ],
        'files' => [
            // upload path relative to rockeeter. directory itself doesn't needed, because it takes name from remote
            'upload_dest_path' => 'web'
        ]
    ],
];
