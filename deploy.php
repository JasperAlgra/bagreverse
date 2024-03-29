<?php
namespace Deployer;
require 'recipe/common.php';

// Configuration

set('ssh_type', 'native');
// set('ssh_multiplexing', true);


// Default stage
set('default_stage', 'production');

// set('repository', 'git@gitlab.eztrack.nl:other/bagreverse.git');
set('repository', 'git@gitlab.eztrack.nl:other/bagreverse.git');

set('shared_files', ['.env']);
// set('shared_dirs', ['logs']);
// set('writable_dirs', ['logs']);

// Servers
host('vmgeo01.servers.kantoor.eztrack.nl')
    // ->become('deploy')
    ->user('deploy')
    ->identityFile('~/.ssh/id_rsa')
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no')
    // ->forwardAgent()
    ->set('deploy_path', '/var/apps/bagreverse')
    ->stage('staging');

host('geo1.eztrack.nl')
    ->user('deploy')
    ->identityFile('~/.ssh/id_rsa')
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->set('deploy_path', '/var/apps/bagreverse')
    ->stage('production');

// Tasks

desc('Restart PHP-FPM service');
task('php-fpm:restart', function() {
    // The user must have rights for restart service
    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
    run('sudo /etc/init.d/php7.0-fpm restart');
});
after('deploy:symlink', 'php-fpm:restart');

desc('Copy .env file');
task('copy_env', function() {
    upload(".env", '{{deploy_path}}/shared/.env');
});

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'copy_env',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');