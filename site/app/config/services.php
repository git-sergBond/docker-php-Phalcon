<?php

use Phalcon\Mvc\View;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use ULogin\Auth;
//use SlowProg\Mailer\MailerService;
use Phalcon\Mailer;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/*$di->setShared('api', function () {
    $config = $this->getConfig();

    $controller = new Controller();
    $controller->setDI($this);
    $controller->setViewsDir($config->application->APIDir);


    return $controller;
});*/

/**
 * Setting up the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.volt' => function ($view) {
            $config = $this->getConfig();

            $volt = new VoltEngine($view, $this);

            $volt->setOptions([
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ]);

            return $volt;
        },
        '.phtml' => PhpEngine::class

    ]);
    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});


/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set('flash', function () {
    return new Flash([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ]);
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    $session = new SessionAdapter();

    $session->start();

    return $session;
});

$di->setShared(
    "cookies",
    function () {
        $cookies = new Cookies();
        return $cookies;
    }
);

$di->setShared(
    "dispatcher",
    function () {
        // Создаем менеджер событий
        $eventsManager = new EventsManager();

        // Отлавливаем исключения и not-found исключения, используя NotFoundPlugin
        $eventsManager->attach(
            "dispatch:beforeException",
            new NotFoundPlugin()
        );

        // Плагин безопасности слушает события, инициированные диспетчером
        $eventsManager->attach(
            "dispatch:beforeExecuteRoute",
            new SecurityPlugin()
        );

        //Это костыльный плагин для преобраования из body в параметры метода
       /* $eventsManager->attach(
            "dispatch:beforeExecuteRoute",
            new BodyMethodConverter()
        );*/

        $dispatcher = new /*Dispatcher2*/Dispatcher();

        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    }
);

$di->set(
    "elements",
    function(){
        return new Elements();
    }
);

/*$di->set(
    "modelsManager",
    function() {
        return new ModelsManager();
    }
);*/

/*$di['mailer'] = function() {
    $service = new MailerService([
        'driver' => 'mail', // mail, sendmail, smtp
        'host'   => 'localhost',
        'port'   => 587,
        'from'   => [
            'email' => 'no-reply@my-domain.com',
            'name'    => 'My Cool Company'
        ],
        'encryption' => 'tls',
        'username'   => 'no-reply@my-domain.com',
        'password'   => 'some-strong-password',
        'sendmail'   => '/usr/sbin/sendmail -bs',
        // Путь используемый для поиска шаблонов писем
        'viewsDir'   => BASE_PATH . '/app/views/', // optional
    ]);

    return $service->mailer();
};*/
$di['mailer'] = function() {
    $config = $this->getConfig()['mail'];
    $mailer = new \Phalcon\Mailer\Manager($config);
    return $mailer;
};

//API


$di->set(
    "PhonesAPI",
    function () {
        $phonesAPI = new PhonesAPIController();

        return $phonesAPI;
    }
);

$di->set(
    "SessionAPI",
    function () {
        $sessionAPI = new SessionAPIController();

        return $sessionAPI;
    }
);

$di->set(
    "TradePointsAPI",
    function () {
        $tradePointsAPI = new TradePointsAPIController();

        return $tradePointsAPI;
    }
);

$di->set(
    "CompaniesAPI",
    function () {
        $companiesAPI = new CompaniesAPIController();

        return $companiesAPI;
    }
);



$di->set(
    "ContactDetailsCompanyCompanyAPI",
    function () {
        $contactDetailsAPI = new ContactDetailsCompanyAPIController();

        return $contactDetailsAPI;
    }
);


