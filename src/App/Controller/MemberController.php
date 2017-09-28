<?php
namespace App\Controller;

use Silex\Application;

class MemberController
{
    public function indexAction(Application $app, $idUser=NULL) {
        
        # Déclaration d'un Message
        $message = 'Espace Membre You2u';
        
        
        #Simulation d'un profil 
        $membre = $app['idiorm.db']
        ->for_table('users')
        ->find_one(1);
        
        # Affichage dans la Vue
        return $app['twig']->render('profil.html.twig',[
            'membre'  => $membre
        ]);
    }
}

