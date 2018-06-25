<?php
namespace Deployer;

require 'recipe/cakephp.php';

// Configuration
set('repository', 'https://github.com/Dallas-Makerspace/calendar.git');
set('git_tty', true); // [Optional] Allocate tty for git on first deployment
add('shared_files', []);
add('shared_dirs', [
    'protected'
]);
add('writable_dirs', [
    'protected'
]);
set('allow_anonymous_stats', false);

// Hosts

host('10.255.0.129')
    ->stage('prod')
    ->set('deploy_path', '/srv/http/calendar.dallasmakerspace.org');


// Tasks
task('deploy:executable', 'chmod +x bin/cake');
task('deploy:run_migrations', function() {
	return false;
});

before('deploy:init', 'deploy:executable');


// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
