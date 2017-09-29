<?php


namespace App\Provider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;


class MemberControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
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