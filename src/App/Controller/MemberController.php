<?php


namespace App\Controller;


use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class MemberController
{
    public function ajoutAnnonceAction(Application $app, Request $request)
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

        # Récupération des villes du Rhône
        $villesRhone = function() use($app)
        {
            $villesRhone = $app['idiorm.db']->for_table('villes_rhone')->find_result_set();

          $array = [];
          foreach ($villesRhone as $ville):
              $array[$ville->commune] = $ville->codeINSEE;
          endforeach;

          return $array;
        };


        # Création du formulaire pour ajouter une annonce
        $form = $app['form.factory']->createBuilder(FormType::class)


            # -- Titre -- #
            ->add('titreService', TextType::class, array(
                'required'          => true,
                'label'             => false,
                'constraints'       => array(new NotBlank(array(
                    'message'       => 'Veuillez renseigner un titre')
                )),
                'attr'              => array(
                    'class'         => 'form-control'
                )
            ))

            # -- Catégorie -- #
            ->add('idCategorieService', ChoiceType::class, array(
                'choices'           => $categoriesService(),
                'expanded'          => false,
                'multiple'          => false,
                'label'             => false,
                'attr'              => array(
                    'class'         => 'form-control'
                )
            ))

            # -- Tarif -- #
            ->add('tarifService', NumberType::class, array(
                'label'             => false,
                'required'          => true,
                'constraints'       => array(new NotBlank(array(
                        'message'       => 'Veuillez renseigner un tarif (mettez 0 si vous souhaitez rendre ce service gratuitement)')
                )),
                'attr'              => array(
                    'class'         => 'form-control',
                    'min'           => 0
                )
            ))


            # -- Localisation -- #
            ->add('lieuService', ChoiceType::class, array(
                'choices'           => $villesRhone(),
                'required'          => false,
                'label'             => false,
                'attr'              => array(
                    'id'            => 'recherche',
                    'class'         => 'form-control'
                )
            ))

            # -- Périmètre d'action possible -- #
            ->add('perimetreAction', IntegerType::class, array(
                'label'             => false,
                'required'          => false,
                'attr'              => array(
                    'class'         => 'form-control',
                    'min'           => 0
                )
            ))

            /*# -- Localisation -- #
            ->add('lieuService', TextType::class, array(
                'required'          => false,
                'label'             => false,
                'attr'              => array(
                    'id'            => 'recherche',
                    'class'         => 'form-control'
                )
            ))*/

            # -- Description -- #
            ->add('descriptionService', TextareaType::class, array(
                'required'           => true,
                'label'             => false,
                'constraints'        => array(new NotBlank(array(
                    'message'       => 'Veuillez renseigner le descriptif de votre annonce')
                )),
                'attr'              => array(
                    'class'         => 'form-control'
                )
            ))

            # -- Submit -- #
            ->add('submit', SubmitType::class, array(
                'label'             => 'Publier',
                'attr'              => array(
                    'class'         => 'btn btn-info'
                )
            ))

            -> getForm();

        $form->handleRequest($request);

        # Vérification de la validité du formulaire
        if ($form->isValid()) :

            # Récupération des données du formulaire
            $annonce = $form->getData();

            # Insertion en BDD
            $annonceDb = $app['idiorm.db']->for_table('services')->create();

            # Association des colonnes de la BDD avec les champs du formulaire
            # Colonnes BDD                      # Champs du formulaire
            $annonceDb->titreService            = $annonce['titreService'];
            $annonceDb->idCategorieService      = $annonce['idCategorieService'];
            $annonceDb->tarifService            = $annonce['tarifService'];
            $annonceDb->lieuService             = $annonce['lieuService'];
            $annonceDb->perimetreAction         = $annonce['perimetreAction'];
            $annonceDb->descriptionService      = $annonce['descriptionService'];
            $annonceDb->idUserProposantService  = $app['user']->getIdUser();

            # Insertion en BDD
            $annonceDb->save();

            return $app->redirect('ajouter?ajoutAnnonce=success');

        endif;

        # Affichage dans la Vue
        return $app['twig']->render('ajoutAnnonce.html.twig', array(
            'form'  => $form->createView()
        ));
    }
}