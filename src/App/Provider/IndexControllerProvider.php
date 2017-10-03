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

           # Page de résultat recherche
            /*$controllers
                ->get("/recherche", "App\Controller\IndexController::resultatRechercheAction")
                ->assert('localisation','[^a-zA-Z\- $]+')
                ->assert('categorie', '[\d]+')
                ->bind('index_resultat_recherche');*/
            
        # On retourne la liste des controllers (ControllerCollection)
        return $controllers;

    }

}
