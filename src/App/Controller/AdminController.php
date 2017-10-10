<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;



class AdminController
{


    function signalementUtilisateurGestionAction(Application $app)
    {
        $signalementUtilisateur = $app['idiorm.db']->for_table('signalements_users')->find_result_set();
        return $app['twig']->render('gestionSignalementUtilisateur.html.twig',
            ['signalements' => $signalementUtilisateur]);
    }


    function signalementServiceGestionAction(Application $app)
    {
        $signalementService = $app['idiorm.db']->for_table('signalements_services')->find_result_set();
        return $app['twig']->render('gestionSignalementService.html.twig',
            ['signalements' => $signalementService]);

    }
    
    public function indexAdminAction (Application $app)
    {
        $userSignaleATraiter = $app['idiorm.db']->for_table('signalements_users')->where('traitementAlerte',0)->count('idSignalementUser');
        $serviceSignaleATraiter = $app['idiorm.db']->for_table('vue_signalements_service')->where('traitementAlerte',0)->count('idServiceSignale');
        $serviceAValider = $app['idiorm.db']->for_table('services')->where('validation',0)->count('idService');
        
        
        
        return $app['twig']->render('indexAdmin.html.twig' , [
            'nombreUserSignaleATraiter' => $userSignaleATraiter,
            'serviceSignaleATraiter' => $serviceSignaleATraiter,
            'serviceAValider' => $serviceAValider
        ]);
    }
    
    public function userTraitementAction(Application $app)
    {
        $traitementUserSignale = $app['idiorm.db']->for_table('vue_signalements_user')->where('traitementAlerte',0)->find_result_set();
        return $app['twig']->render('userTraitement.html.twig' , [
            'traitementUserSignale' => $traitementUserSignale
            ]);
        
    }
    
    public function serviceTraitementAction(Application $app)
    {
        $traitementServiceSignale = $app['idiorm.db']->for_table('vue_signalements_service')->where('traitementAlerte',0)->find_result_set();
        return $app['twig']->render('serviceTraitement.html.twig' , [
            'traitementServiceSignale' => $traitementServiceSignale
        ]);
        
    }
    
    public function serviceValidationAction(Application $app)
    {
        $servicesAValider = $app['idiorm.db']->for_table('vue_services')->where('validationService',0)->find_result_set();
        return $app['twig']->render('serviceValidation.html.twig' , [
            'servicesAValider' => $servicesAValider
        ]);
        
    }
    
    public function fermetureAnnonceAction(Application $app, $idService, Request $request)
    {
        #Si l'utilisateur en cours à le role d'administrateur
        if(in_array('ROLE_ADMIN', $app['user']->getRoleUser()))
        {
            #On ferme l'annonce correspondante à $idService. 
            $annonceCloturee = $app['idiorm.db']->for_table('services')->find_one($idService);
            $annonceCloturee->set('ouvert', 0);
            $annonceCloturee->set('validation', 1);
            $annonceCloturee->save();
            
            #On ferme les requetes liées à l'annonce. 
            $fermetureAlerte = $app['idiorm.db']->for_table('signalements_services')->where('idServiceSignale', $idService)->find_result_set()->set('traitementAlerte',1)->save();
            
            
            
            #On redirige vers une page de succes. 
            
            $go = $request->get('go');
            if(!empty($go))
            {
                return $app->redirect($app['url_generator']->generate($go).'?cloture=ok');
            }
            else
            {
                return $app->redirect($request->headers->get('referer').'?cloture=ok');
            }
   
        }
        
        #Sinon on le renvoit vers l'espace de connexion.
        else
        {
            return $app->redirect($app['url_generator']->generate('index_connexion'));
        }
        
        
        
    }
    
    public function ValiderAnnonceAction(Application $app, $idService, Request $request)
    {
        #Si l'utilisateur en cours à le role d'administrateur
        if(in_array('ROLE_ADMIN', $app['user']->getRoleUser()))
        {
            #On valide l'annonce correspondante à $idService.
            $annonceValidee = $app['idiorm.db']->for_table('services')->find_one($idService);
            $annonceValidee->set('ouvert', 1);
            $annonceValidee->set('validation', 1);
            $annonceValidee->save();
            
           
            
            
          
            #On redirige vers une page de succes.
            $go = $request->get('go');
            if(!empty($go))
            {
                return $app->redirect($app['url_generator']->generate($go).'?validation=ok');
            }
            else
            {
                return $app->redirect($request->headers->get('referer').'?validation=ok');
            }
            
        }
        
        #Sinon on le renvoit vers l'espace de connexion.
        else
        {
            return $app->redirect($app['url_generator']->generate('index_connexion'));
        }
        
        
        
    }
    
    public function traiterAnnonceAction(Application $app, $idService, Request $request)
    {
        if(in_array('ROLE_ADMIN', $app['user']->getRoleUser()))
        {
            $app['idiorm.db']->for_table('signalements_services')->where('idServiceSignale', $idService)->find_result_set()->set('traitementAlerte',1)->save();
            
            $go = $request->get('go');
            if(!empty($go))
            {
                return $app->redirect($app['url_generator']->generate($go).'?traiter=ok');
            }
            else
            {
                return $app->redirect($request->headers->get('referer').'?traiter=ok');
            }
           
        }
        
        #Sinon on le renvoit vers l'espace de connexion.
        else
        {
            return $app->redirect($app['url_generator']->generate('index_connexion'));
        }
    }
    
    public function traiterUserAction(Application $app, $idUser, Request $request)
    {
        if(in_array('ROLE_ADMIN', $app['user']->getRoleUser()))
        {
            $app['idiorm.db']->for_table('signalements_users')->where('idUserSignale', $idUser)->find_result_set()->set('traitementAlerte',1)->save();
            
            $go = $request->get('go');
            if(!empty($go))
            {
                return $app->redirect($app['url_generator']->generate($go).'?traiter=ok');
            }
            else
            {
                return $app->redirect($request->headers->get('referer').'?traiter=ok');
            }
           
        }
        
        #Sinon on le renvoit vers l'espace de connexion.
        else
        {
            return $app->redirect($app['url_generator']->generate('index_connexion'));
        }
    }
    
    public function bannirUtilisateurAction(Application $app, $idUser,$go=NULL, Request $request)
    {
        if(in_array('ROLE_ADMIN', $app['user']->getRoleUser()))
        {
            #On recupérer les informations de l'utilisateur. 
            $infoUser = $app['idiorm.db']->for_table('users')->find_one($idUser);
            if($infoUser['roleUser'] == 'ROLE_ADMIN')
            {
                $go = $request->get('go');
                if(!empty($go))
                {
                    return $app->redirect($app['url_generator']->generate($go).'?bannir=echec');
                }
                else
                {
                    return $app->redirect($request->headers->get('referer').'?bannir=echec');
                }
            }
            else
            {
                #On cloture toutes les annonces de l'utilisateurs. 
                $app['idiorm.db']->for_table('services')->where('idUserProposantService', $idUser)->find_result_set()->set('ouvert',0)->save();
                #On cloture toutes les demandes liées à cette utilisateurs dans les deux signalements (USERS ET SERVICES)
                $app['idiorm.db']->for_table('signalements_services')->where('idUserSignale', $idUser)->find_result_set()->set('traitementAlerte',1)->save();
                $app['idiorm.db']->for_table('signalements_users')->where('idUserSignale', $idUser)->find_result_set()->set('traitementAlerte',1)->save();
                #On bannit l'utiliseur
                $app['idiorm.db']->for_table('users')->find_one($idUser)->set('roleUser','ROLE_BANNED')->save();
                $go = $request->get('go');
                if(!empty($go))
                {
                    return $app->redirect($app['url_generator']->generate($go).'?bannir=ok');
                }
                else
                {    
                    return $app->redirect($request->headers->get('referer').'?bannir=ok');
                }
               
                
                
            }
            
        }
        
        #Sinon on le renvoit vers l'espace de connexion.
        else
        {
            return $app->redirect($app['url_generator']->generate('index_connexion'));
        }
    }
    
    public function debannirUtilisateurAction(Application $app, $idUser, Request $request)
    {
        if(in_array('ROLE_ADMIN', $app['user']->getRoleUser()))
        {
            #On recupérer les informations de l'utilisateur.
            $infoUser = $app['idiorm.db']->for_table('users')->find_one($idUser);
            if($infoUser['roleUser'] == 'ROLE_ADMIN')
            {
                return $app->redirect($request->headers->get('referer').'?debannir=echec');
            }
            
            else
            {
                #On débannit l'utiliseur
                $app['idiorm.db']->for_table('users')->find_one($idUser)->set('roleUser','ROLE_USER')->save();
                $go = $request->get('go');
                if(!empty($go))
                {
                    return $app->redirect($app['url_generator']->generate($go).'?debannir=ok');
                }
                else
                {
                    return $app->redirect($request->headers->get('referer').'?debannir=ok');
                }
                
                
            }
        }
        
        #Sinon on le renvoit vers l'espace de connexion.
        else
        {
            return $app->redirect($app['url_generator']->generate('index_connexion'));
        }
    }
    
    public function AdminUtilisateurAction(Application $app, $idUser, Request $request)
    {
        if(in_array('ROLE_ADMIN', $app['user']->getRoleUser()))
        {
            #On recupérer les informations de l'utilisateur.
            $infoUser = $app['idiorm.db']->for_table('users')->find_one($idUser);
            if($infoUser['roleUser'] == 'ROLE_ADMIN')
            {
                return $app->redirect($request->headers->get('referer').'&?admin=echec');
            }
            
            else
            {
                #On promeut l'utilisateur au rang d'admin
                $bannisementUser = $app['idiorm.db']->for_table('users')->find_one($idUser)->set('roleUser','ROLE_ADMIN')->save();
                return $app->redirect($request->headers->get('referer').'?&admin=ok');
                
                
            }
        }
        
        #Sinon on le renvoit vers l'espace de connexion.
        else
        {
            return $app->redirect($app['url_generator']->generate('index_connexion'));
        }
    }
    
    
    public function gestionUtilisateurAction(Application $app)
    {
        $infoUsers = $app['idiorm.db']->for_table('users')->where_not_equal('roleUSER','ROLE_ADMIN')->find_result_set();
        return $app['twig']->render('gestionUtilisateurs.html.twig' , [
            'infoUsers' => $infoUsers
        ]);
    }



}













