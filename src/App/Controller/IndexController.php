<?php
namespace App\Controller;

use function print_r;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class IndexController
{

    public function indexAction(Application $app)
    {

        # Affichage dans la Vue
        return $app['twig']->render('index.html.twig');
    }

    # Affichage de la page de connexion
    public function connexionAction(Application $app, Request $request)
    {
        # Affichage dans la Vue
        return $app['twig'] -> render('connexion.html.twig', [
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username')
        ]);
    }

    # Affichage de la page de recherche
    public function rechercheAction(Application $app, Request $request)
    {
        # Récupération des catégories de services
        $categoriesService = function () use ($app)
        {
            # Récupération des catégories dans la BDD
            $categoriesService = $app['idiorm.db']->for_table('categorie_service')->find_result_set();

            # Formatage de l'affichage pour le champ select (ChoiceType) du formulaire
            $array = [];

            foreach ($categoriesService as $categorie) :
                $array[$categorie->nomCategorieService] = $categorie->idCategorieService;
            endforeach;

            return $array;
        };

        # Création des champs de recherche
        $form = $app['form.factory']->createBuilder(FormType::class)


            # -- Catégorie -- #
            ->add('categorie', ChoiceType::class, array(
                'choices'           => $categoriesService(),
                'expanded'          => false,
                'multiple'          => false,
                'label'             => false,
                'attr'              => array(
                    'class'         => 'form-control'
                )
            ))

            # -- Localisation -- #
            ->add('localisation', TextType::class, array(
                'required'          => false,
                'label'             => false,
                'attr'              => array(
                    'id'            => 'recherche',
                    'class'         => 'typeahead form-control',
                    'placeholder'   => 'Localisation'
                )
            ))

            -> getForm();

        $form->handleRequest($request);

        # Récupération des données du formulaire
       if (!empty($_GET))
       {
           $categorie       =  $request->get('form')['categorie'];
           $localisation    =  $app['idiorm.db']->for_table('villes_rhone')->where('commune', $request->get('form')['localisation'])->find_one();
           $codeINSEE       =  $localisation->codeINSEE;

           # Récupération des annonces
           $annoncesPubliees = $app['idiorm.db']->for_table('services')
               ->where('validation', 1)
               ->where('idCategorieService', $categorie)
               ->where('lieuService', $codeINSEE)
               ->order_by_desc('idService')
               ->find_result_set();

           return $app['twig']->render('recherche.html.twig', [
               'idCategorieService'    => $categorie,
               'lieuService'           => $localisation,
               'form'=> $form->createView(),
               'annoncesPubliees'  => $annoncesPubliees
           ]);

       } // END IF NOT EMPTY

        # Affichage dans la Vue
        return $app['twig'] -> render('recherche.html.twig', array(
            'form'=> $form->createView()
        ));

    }

}
