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

        # Connexion à la BDD & Récupération des annonces
        $services = $app['idiorm.db']->for_table('services')->find_result_set();

        # Récupération des 3 annonces en spotlight (utilisateurs les mieux notés)
        /*$spotlight = $app[idiorm.db]->
            "SELECT services . idService, services . titreService, users . pseudo, AVG(note_users . note) as moyenne
	         FROM services
             INNER JOIN users ON services . idUserProposantService = users . idUser
             INNER JOIN note_users ON users . idUser = note_users . idUserNoted
             GROUP BY users . idUser
             ORDER BY moyenne DESC";*/

        # Affichage dans le Vue
        return $app['twig']->render('index.html.twig', [
            'services' => $services
        ]);
    }


    public function categorie_serviceAction($nomCategorieService, Application $app)
    {

        # Connexion à la BDD et la Récupération des services de la categorie choisie par l'utilisateur
        $services = $app['idiorm.db']->for_table('vue_services')
            ->where('nomCategorieService', ucfirst($nomCategorieService))
            ->find_result_set();


        # Transmission à la vue
        return $app['twig']->render('service.html.twig', [
            'services' => $services,
            'nomCategorieService' => $nomCategorieService
        ]);
    }


    public function serviceAction(
        $nomCategorieService,
        $slugService,
        $idService,
        Application $app)
    {

        # Récupération de l'annonce
        $service = $app['idiorm.db']->for_table('vue_services')
            ->find_one($idService);

        # Récupérer des Articles de la Catégories (suggestions)
        $suggestions = $app['idiorm.db']->for_table('vue_services')
            # Je récupère uniquement les annonces de la même catégorie que mon annonce
            ->where('nomCategorieService', ucfirst($nomCategorieService))
            # Sauf mon annonce en cours
            ->where_not_equal('idService', $idService)
            # 3 annonces maximum
            ->limit(3)
            # Par ordre décroissant
            ->order_by_desc('IDARTICLE')
            # Je récupère les résultats
            ->find_result_set();

        # Transmission à la Vue
        return $app['twig']->render('annonce.html.twig', [
            'service' => $service,
            'suggestions' => $suggestions
        ]);
    }


    # Affichage de la page de connexion
    public function connexionAction(Application $app, Request $request)
    {
        # Création du formulaire de connexion
        $form = $app['form.factory']->createBuilder(FormType::class)
            # -- Identifiant -- #
            ->add('email', EmailType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez renseigner votre adresse email')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'votre.email@exemple.fr'
                )
            ))
            # -- Mot de passe -- #
            ->add('motDePasse', PasswordType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez renseigner votre mot de passe')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '******'
                )
            ))
            # -- Connexion -- #
            ->add('submit', SubmitType::class, array(
                'label' => 'Connexion',
                'attr' => array(
                    'class' => 'btn btn-primary'
                )
            ))
            # --> La sécurisation du formulaire de connexion est géré par Silex directement <-- #

            ->getForm();

        $form->handleRequest($request);

        # Affichage dans la Vue
        return $app['twig']->render('connexion.html.twig', [
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
            'form' => $form->createView()
        ]);
    }
}
