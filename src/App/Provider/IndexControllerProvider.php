<?php

namespace App\Provider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class IndexControllerProvider implements ControllerProviderInterface {
    
    /**
     * {@inheritDoc}
     * @see \Silex\Api\ControllerProviderInterface::connect()
     */
    public function connect(Application $app)
    {
        
        # : Créer une instance de Silex\ControllerCollection
        # : https://silex.symfony.com/api/master/Silex/ControllerCollection.html
        $controllers = $app['controllers_factory'];
        
            # Page d'Accueil
            $controllers
                # On associe une Route à un Controller et une Action
                ->get('/', 'App\Controller\IndexController::indexAction')
                # En option je peux donner un nom à la route, qui servira plus tard
                # pour la créations de lien : "controller_action"
                ->bind('index_index');

            # Page de connexion
            $controllers
                ->get("/connexion","App\Controller\IndexController::connexionAction")
                ->bind('index_connexion');

            # Page de recherche
            $controllers
                ->get("/recherche", "App\Controller\IndexController::rechercheAction")
                ->bind('index_recherche');

            # Page de recherche
            $controllers
                ->post("/recherche", "App\Controller\IndexController::rechercheActionPost")
                ->bind('index_recherche_POST');

            $controllers->get('/api/recherche',  "App\Controller\IndexController::api");

        # On retourne la liste des controllers (ControllerCollection)
        return $controllers;

    }

}
