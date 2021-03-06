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


        $controllers
            # On associe une Route à un Controller et une Action
            ->get('/about', 'App\Controller\IndexController::presentationAction')
            # En option je peux donner un nom à la route, qui servira plus tard
            # pour la créations de lien : "controller_action"
            ->bind('index_about');

        $controllers
            # On associe une Route à un Controller et une Action
            ->get('/categories', 'App\Controller\IndexController::liste_categorieAction')

            # En option je peux donner un nom à la route, qui servira plus tard
            # pour la créations de lien : "controller_action"
            ->bind('index_categories');


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
            ->match('/{nomCategorieService}/{slugService}_{idService}.html',
                'App\Controller\IndexController::serviceAction')
            ->method('GET|POST')
            ->assert('idService', '\d+')
            ->bind('index_annonce');


# Page d'inscription
        $controllers
            ->match("/inscription","App\Controller\IndexController::inscriptionAction")
            ->method('GET|POST')
            ->bind('index_inscription');


        # Page d'inscription
        $controllers
            ->match("/inscription","App\Controller\IndexController::inscriptionAction")
            ->method('GET|POST')
            ->bind('index_inscription');



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

            # Page d'affichage du profil public
            $controllers
            ->match("/profil/{idUser}", "App\Controller\IndexController::affichageProfilAction")
            ->assert('idUser', '\d+')
            ->method('GET|POST')
            ->bind('index_profil');

            # Page CGU
            $controllers
            ->get("/cgu", "App\Controller\IndexController::cguAction")
            ->bind("index_cgu");

            # Page Qui sommes nous
            $controllers
                ->get("/qui-sommes-nous", "App\Controller\IndexController::quiSommesNousAction")
                ->bind("index_quiSommesNous");


        # On retourne la liste des controllers (ControllerCollection)
        return $controllers;

    }

}
