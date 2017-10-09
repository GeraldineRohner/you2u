<?php

use Idiorm\Silex\Provider\IdiormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;

#1 : Connexion BDD
define('DBHOST',     'localhost');
define('DBNAME',     'you2ubeta');
define('DBUSERNAME', 'root');
define('DBPASSWORD', '');

#2 : Doctrine DBAL
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => DBHOST,
        'dbname'    => DBNAME,
        'user'      => DBUSERNAME,
        'password'  => DBPASSWORD
    ),
));

#3 : Idiorm ORM
$app->register(new IdiormServiceProvider(), array(
    'idiorm.db.options' => array(

        'connection_string'   => 'mysql:host='.DBHOST.';dbname='.DBNAME,
        'username'            => DBUSERNAME,
        'password'            => DBPASSWORD,
        'id_column_overrides' => array('users'             => 'idUser',
                                       'categorie_service' => 'idCategorieService',
                                       'services'          => 'idService',
                                       'vue_services'      => 'idService',
                                       'signalements_services' =>'idSignalement',
                                       'signalements_users' => 'idSignalementUser',
                                       'villes_rhone'      =>  'codeINSEE')
    )
));


$app->register(new HttpFragmentServiceProvider());