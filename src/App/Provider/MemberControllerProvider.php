<?php
namespace App\Provider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;


class MemberControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {

        /**
         * Récupérer l'instance de Silex\ControllerCollection
         * https://silex.symfony.com/doc/2.0/organizing_controllers.html (DOC)
         * $app['controllers_factory'] is a factory that returns a new instance of ControllerCollection when used.
         */
        
        $controllers = $app['controllers_factory'];
        
        #page d'Accueil (profil)
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
        ->match('/modifMdp','App\Controller\MemberController::modifMdpAction')
        #En option, je peux donner un nom à la route, qui servira plus tard pour la création de liens.
        ->bind('membre_motdepasse');
        
        #Ajout d'annonce
        $controllers
        ->match("/ajouter", "App\Controller\MemberController::ajoutAnnonceAction")
        ->method('GET|POST')
        ->bind('member_ajout_annonce');

        #Contact
        $controllers
            ->match('/contact','App\Controller\MemberController::contactAction')
            #En option, je peux donner un nom à la route, qui servira plus tard pour la création de liens.
                ->method('GET|POST')
            ->bind('membre_contact');


        # Pages de signalement (service et utilisateur)
        $controllers
            ->match("/signalement/annonce_{idService}","App\Controller\MemberController::signalementServiceAction")
            ->method('GET|POST')
            ->assert('idService', '\d+')
            ->bind('membre_signalement_annonce');

        $controllers
            ->match("/signalement/utilisateur_{idUser}","App\Controller\MemberController::signalementUserAction")
            ->method('GET|POST')
            ->assert('idUser', '\d+')
            ->bind('membre_signalement_utilisateur');
        
        
        
        return $controllers;
    }
    
  
}

