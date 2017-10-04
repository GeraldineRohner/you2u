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

        # Affichage d'une catégorie (services qui y sont liés)
        $controllers
            ->get('/categorie/{nomCategorieService}',
                'App\Controller\IndexController::categorie_serviceAction')
            # Je spécifie le type de paramètre attendu avec une Regex
            ->assert('nomCategorieService', '[^/]+')
            # Je peux attribuer une valeur par défaut.
            ->value('nomCategorieService', 'Garde d\'enfants')
            # Nom de ma Route
            ->bind('index_categorie');


        # Page Annonce
        $controllers
            ->get('/{nomCategorieService}/{slugService}_{idService}.html',
                'App\Controller\IndexController::serviceAction')
            ->assert('idService', '\d+')
            ->bind('index_annonce');


        # Page d'inscription
        $controllers
            ->match("/inscription","App\Controller\IndexController::inscriptionAction")
            ->method('GET|POST')
            ->bind('index_inscription');




        # Page de connexion
            $controllers
                ->get("/connexion","App\Controller\IndexController::connexionAction")
                ->bind('index_connexion');




            
        # On retourne la liste des controllers (ControllerCollection)
        return $controllers;

    }

}
