<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

return
	[
		'paths' => [
			'migrations' => '%%PHINX_CONFIG_DIR%%/app/db/migrations',
			'seeds' => '%%PHINX_CONFIG_DIR%%/app/db/seeds',
		],
		'environments' => [
			'default_migration_table' => 'phinxlog',
			'default_environment' => 'development',
			'production' => [
				'migration_table' => 'phinxlog_live',
				'adapter' => 'mysql',
				'host' => '%%PHINX_DBHOST%%',
				'name' => '%%PHINX_DBNAME%%',
				'user' => '%%PHINX_DBUSER%%',
				'pass' => '%%PHINX_DBPASS%%',
				'port' => '3306',
				'charset' => 'utf8',
			],
			'development' => [
				'migration_table' => 'phinxlog',
				'adapter' => 'mysql',
				'host' => '%%PHINX_DBHOST%%',
				'name' => '%%PHINX_DBNAME%%',
				'user' => '%%PHINX_DBUSER%%',
				'pass' => '%%PHINX_DBPASS%%',
				'port' => '3306',
				'charset' => 'utf8',
			],
			'testing' => [
				'adapter' => 'mysql',
				'host' => 'localhost',
				'name' => 'testing_db',
				'user' => 'root',
				'pass' => '',
				'port' => '3306',
				'charset' => 'utf8',
			],
		],
		'version_order' => 'creation',
	];
