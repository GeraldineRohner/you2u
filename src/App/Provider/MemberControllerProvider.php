<?php
<<<<<<< HEAD
=======


>>>>>>> espaceConnexion
namespace App\Provider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

<<<<<<< HEAD
=======

>>>>>>> espaceConnexion
class MemberControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
<<<<<<< HEAD
        /**
         * Récupérer l'instance de Silex\ControllerCollection
         * https://silex.symfony.com/doc/2.0/organizing_controllers.html (DOC)
         * $app['controllers_factory'] is a factory that returns a new instance of ControllerCollection when used.
         */
        
        $controllers = $app['controllers_factory'];
        
        #page d'Accueil
        $controllers
        #on associe une route à un controller et une action
        ->get('/','App\Controller\MemberController::indexAction')
        #En option, je peux donner un nom à la route, qui servira plus tard pour la création de liens.
        ->bind('membre_index');
        
        
        #page d'Accueil
        $controllers
        #on associe une route à un controller et une action
        ->match('/modif','App\Controller\MemberController::modifAction')
        #En option, je peux donner un nom à la route, qui servira plus tard pour la création de liens.
        ->bind('membre_modif');
      
        #page d'Accueil
        $controllers
        #on associe une route à un controller et une action
        ->match('/modifMdp','App\Controller\MemberController::modifMdp')
        #En option, je peux donner un nom à la route, qui servira plus tard pour la création de liens.
        ->bind('membre_motdepasse');
        
        
        
        return $controllers;
    }
    
   
}

=======
        # Récupérer l'instance de Silex\ControllerCollection
        # https://silex.symfony.com/api/master/Silex/ControllerCollection.html
        # https://silex.symfony.com/doc/2.0/organizing_controllers.html
        $controllers = $app['controllers_factory'];

        # Page Ajout Annonce
        $controllers
            ->match("/ajouter", "App\Controller\MemberController::ajoutAnnonceAction")
            ->method('GET|POST')
            ->bind('member_ajout_annonce');

        # On retourne la liste des $controllers (ControllersCollection)
        return $controllers;

    }
}
>>>>>>> espaceConnexion
