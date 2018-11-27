<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
define('IMAGE_PATH',BASE_PATH.'/public/images/');
define('IMAGE_PATH_TRUNCATED', "images/");
/*
 * Статусы процесса выполнения задания
 */
define('STATUS_ACCEPTING', 0);
define('STATUS_SELECTION_EXECUTOR',1);
define('STATUS_CANCELED',2);
define('STATUS_WAITING_CONFIRM',3);
define('STATUS_EXECUTING',4);
define('STATUS_EXECUTED_EXECUTOR',5);
define('STATUS_EXECUTED_CLIENT',6);
define('STATUS_NOT_EXECUTED',7);
define('STATUS_REJECTED_BY_SYSTEM',8);
define('STATUS_PAID_CLIENT',9);
define('STATUS_PAID_PART',10);
define('STATUS_PAID_EXECUTOR',11);
define('STATUS_PAID_BY_SECURE_TRANSACTION',12);
define('STATUS_NOT_CONFIRMED',13);

/*
 * Статусы выполнения запросов
 */
define('STATUS_OK', 'OK');
define('STATUS_WRONG','WRONG_DATA');
define('STATUS_ALREADY_EXISTS','ALREADY_EXISTS');
define('STATUS_UNRESOLVED_ERROR','UNRESOLVED_ERROR');

/*
 * Роли
 */
define('ROLE_GUEST', 'Guests');
define('ROLE_USER', 'User');
define('ROLE_MODERATOR', 'Moderator');


define('API_URL', 'http://bro4you.ru/');

return new \Phalcon\Config([
    'database' => [
        'adapter'     => 'postgresql',
        'username'    => 'bro4you_parser',
        'password'    => '123456',
        'port'        => '5432',
        'host'        => 'localhost',
        'dbname'      => 'bro4you_parser',
    ],
    'application' => [
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'APIDir' => APP_PATH . '/controllers/api/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'formsDir'     => APP_PATH . '/forms/',
        'cacheDir'       => BASE_PATH . '/cache/',
        'modelsResponsesDir' => APP_PATH . '/models/responses/',

        // This allows the baseUri to be understand project paths that are not in the root directory
        // of the webpspace.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        'baseUri'        => preg_replace('/public([\/\\\\])index.php$/', '', $_SERVER["PHP_SELF"]),
    ],
    /*
    'mail' => [
        'driver' => 'smtp', // mail, sendmail, smtp
        'port'   => 587,
        'from'   => [
            'address' => 'no-reply@my-domain.com',
            'name'    => 'My Cool Company'
        ],
        'encryption' => 'tls',
        'username'   => 'no-reply@my-domain.com',
        'password'   => 'some-strong-password',
        'sendmail'   => '/usr/sbin/sendmail -bs',
    ],*/

    'mail' =>[
        'driver'     => 'smtp',
        'host'       => 'bro4you.post',
        'port'       => 110,
        'encryption' => 'ssl',
        'username'   => 'bro1.4you@bro4you.post',
        'password'   => 'A7t0P7a1',
        'from' => [
            'email' => 'bro1.4you@bro4you.post',
            'name' => 'Тестовая почта Раст'
        ],
        'viewsDir' =>  APP_PATH . '/views/',
    ],
]);

