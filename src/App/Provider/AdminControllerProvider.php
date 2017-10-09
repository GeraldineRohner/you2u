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
        
       #Page d'accueil de l'admin
        $controllers
        ->get('/', 'App\Controller\AdminController::indexAdminAction')
        ->bind('index_admin');

        # Page des signalements de services
        $controllers
            ->get('/signalements/utilisateurs', 'App\Controller\AdminController::signalementUtilisateurGestionAction')
            ->bind('admin_signalements_utilisateurs');
        
        # Page des signalements d'utilisateurs.
        $controllers
        ->get('/signalements/utilisateurs/gestion/', 'App\Controller\AdminController::userTraitementAction')
        ->bind('admin_traitementSignalementUser');
        
        # Page des signalements des services.
        $controllers
        ->get('/signalements/services/gestion/', 'App\Controller\AdminController::serviceTraitementAction')
        ->bind('admin_traitementSignalementService');
        
        # Page de fermeture des services.
        $controllers
        ->get('/services/gestion/fermeture/{idService}', 'App\Controller\AdminController::fermetureAnnonceAction')
        ->assert('idService', '\d+')
        ->bind('admin_fermetureService');
        
        # Page des traitement des annonces
        $controllers
        ->get('/services/gestion/traiter/{idService}', 'App\Controller\AdminController::traiterAnnonceAction')
        ->assert('idService', '\d+')
        ->bind('admin_traiterService');
        
        # Page de traitement des utilisateurs (sans action)
        $controllers
        ->get('/utilisateurs/gestion/traiter/{idUser}', 'App\Controller\AdminController::traiterUserAction')
        ->assert('idUser', '\d+')
        ->bind('admin_traiterUser');
        
        #Page de bannisement d'un utilisateur. 
        $controllers
        ->get('/utilisateurs/gestion/bannir/{idUser}', 'App\Controller\AdminController::bannirUtilisateurAction')
        ->assert('idUser', '\d+')
        ->bind('admin_bannirUser');
        
        #Page de débannisement d'un utilisateur.
        $controllers
        ->get('/utilisateurs/gestion/debannir/{idUser}', 'App\Controller\AdminController::debannirUtilisateurAction')
        ->assert('idUser', '\d+')
        ->bind('admin_debannirUser');
        
        #Page de promotion utilisateur
        $controllers
        ->get('/utilisateurs/gestion/promotion/{idUser}', 'App\Controller\AdminController::AdminUtilisateurAction')
        ->assert('idUser', '\d+')
        ->bind('admin_new_admin');
        
        #Page d'affichage des annonces à valider
        $controllers
        ->get('/service/validation', 'App\Controller\AdminController::serviceValidationAction')
        ->bind('admin_serviceavalider');
        
        #Page de validation d'annonce
        $controllers
        ->get('/service/validation/{idService}', 'App\Controller\AdminController::ValiderAnnonceAction')
        ->bind('admin_validation_service');
        
        #Page de validation d'annonce
        $controllers
        ->get('/utilisateurs/gestion', 'App\Controller\AdminController::gestionUtilisateurAction')
        ->bind('admin_gestion_utilisateurs');
        




        return $controllers;
    }
}










