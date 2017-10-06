<?php
namespace App\Provider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use App\Controller\AdminController;

class AdminControllerProvider implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        # : Récupérer l'instance de Silex\ControllerCollection
        # : https://silex.symfony.com/api/master/Silex/ControllerCollection.html
        $controllers = $app['controllers_factory'];

        # Page des signalements d'utilisateurs
        $controllers
            ->get('/signalements/services', 'App\Controller\AdminController::signalementServiceGestionAction')
            ->bind('admin_signalements_services');


        # Page des signalements de services
        $controllers
            ->get('/signalements/utilisateurs', 'App\Controller\AdminController::signalementUtilisateurGestionAction')
            ->bind('admin_signalements_utilisateurs');


        $controllers
            ->get('/gestion/utilisateurs', 'App\Controller\AdminController::gestionUtilisateursAction')
            ->bind('admin_gestion_utilisateurs');


        $controllers
            ->get('/gestion/services', 'App\Controller\AdminController::gestionServicesAction')
            ->bind('admin_gestion_services');



        return $controllers;
    }
}










