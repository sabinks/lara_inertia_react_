<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'git@github.com:nepteka/xl-accounting-backend.git');
set('branch', 'main');
// by default release folder name is 1, 2 etc
set('release_name', function () {
    return date('YmdHi');
});
set('keep_releases', 1);
// Shared files/dirs between deploys
add('shared_files', ['.env', '.env.example']);
//add('shared_dirs', ['vendor', 'public/uploads', 'public/profile']);
add('shared_dirs', ['public/uploads']);

// Writable dirs by web server
add('writable_dirs', ['public/uploads', 'storage', 'storage/framework/cache', 'bootstrap/cache']);


// Hosts
host('xl_accounting_production')
    ->set('labels', ['stage' => 'production'])
    ->set('remote_user', 'root')
    ->set('deploy_path', '/var/www/xl_accounting/live/backend');
// Hooks

after('deploy:failed', 'deploy:unlock');

task('config-cache:clear', function () {
    run('cd {{release_path}} && php artisan config:clear && php artisan cache:clear');
});

task('reload:php-fpm', function () {
    run('sudo systemctl reload php8.1-fpm');
});

task('reload:nginx', function () {
    run('sudo systemctl reload nginx');
});
after('deploy', 'config-cache:clear');
after('deploy', 'reload:php-fpm');
after('deploy', 'reload:nginx');
