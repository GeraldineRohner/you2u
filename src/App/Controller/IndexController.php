<?php
namespace App\Controller;

use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class IndexController
{

    public function indexAction(Application $app)
    {
        # Déclaration d'un Message
        $message = 'Mon Application Silex !';
       
        # Affichage dans la Vue
        return $app['twig']->render('index.html.twig',[
            'message'  => $message
        ]);
    }

    # Affichage de la page de connexion
    public function connexionAction(Application $app, Request $request)
    {
        # Création du formulaire de connexion
        $form = $app['form.factory']->createBuilder(FormType::class)

        # -- Identifiant -- #
        ->add('email', EmailType::class, array(
            'required'          => true,
            'label'             => false,
            'constraints'       => array(new NotBlank(array(
                'message'       => 'Veuillez renseigner votre adresse email')
            )),
            'attr'              => array(
                'class'         => 'form-control',
                'placeholder'   => 'votre.email@exemple.fr'
            )
        ))
        # -- Mot de passe -- #
        ->add('motDePasse', PasswordType::class, array(
            'required'          => true,
            'label'             => false,
            'constraints'       => array(new NotBlank(array(
                'message'       => 'Veuillez renseigner votre mot de passe')
            )),
            'attr'              => array(
                'class'         => 'form-control',
                'placeholder'   => '******'
            )
        ))
        # -- Connexion -- #
        ->add('submit', SubmitType::class, array(
            'label'             => 'Connexion',
            'attr'              => array(
                'class'         => 'btn btn-primary'
            )
        ))

        # --> La sécurisation du formulaire de connexion est géré par Silex directement <-- #

        ->getForm();

        $form->handleRequest($request);

        # Affichage dans la Vue
        return $app['twig'] -> render('connexion.html.twig', [
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
            'form'          => $form->createView()
        ]);
    }
}
