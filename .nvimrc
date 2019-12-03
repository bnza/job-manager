let g:ale_php_phpmd_ruleset = 'phpmd.xml'
let test#php#phpunit#executable = 'XDEBUG_CONFIG="idekey=xdebug" php -dxdebug.remote_enable=1 -dxdebug.remote_mode=req -dxdebug.remote_port=9000 -dxdebug.remote_host=127.0.0.1 ./vendor/bin/simple-phpunit'
