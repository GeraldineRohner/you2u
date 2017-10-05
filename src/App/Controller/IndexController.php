<?php
namespace App\Controller;

use function json_encode;
use function print_r;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use function utf8_encode;
use function var_dump;

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


        # Affichage dans la vue
        return $app['twig']->render('recherche.html.twig', [
            'form'                   => $form->createView()
        ]);

    }// Fin public function rechercheAction

    public function rechercheActionPost(Application $app, Request $request)
    {

        $annoncesPubliees[]  = null;
        $nbAnnoncesPubliees = null;
        $pageMax = null;
        $numeroPage = null;
        $categorie = null;
        $localisation = null;
        $page = $request->get('page');
        # --> PAGINATION <-- #

        $debug['localisation'] = $request->get('categorie');

        # Variable pagination : nombre d'annonce par page (limit)
        $limit = 10;
        # Vérification de l'existance et la conformité de GET
        if(null != $page && preg_match('#^[1-9][0-9]{0,9}$#', $page))
        {
            # si oui, on récupère l'information de GET
            $numeroPage = $page;
        }
        else
        {
            # si non, on prend la page 1 par défaut
            $numeroPage = 1;
        }
        # Création de l'offset
        $offset = ($numeroPage-1)*$limit;

        # --> FIN PAGINATION <-- #

        # --> CONDITIONS AFFICHAGE RESULTATS RECHERCHE <-- #
        if (!empty($_POST))
        {
            # --> GESTION CHAMPS DE RECHERCHE <-- #
            # Récupération des données GET pour la catégorie de service et la localisation
            $categorie       =  $request->get('categorie');


            $localisation    =  $app['idiorm.db']   ->for_table('villes_rhone')
                                                    ->where('commune', $request->get('localisation'))
                                                    ->find_one();

            # Si juste localisation remplie
            if($categorie == 1 AND !empty($localisation))
            {
                $codeINSEE   =  $localisation->codeINSEE;

                # Récupération des annonces
                $annoncesPubliees = $app['idiorm.db']   ->for_table('vue_liste_annonces')
                                                        ->where('validationService', 1)
                                                        ->where('ouvert', 1)
                                                        ->where('lieuService', $codeINSEE)
                                                        ->order_by_desc('idService')
                                                        //->limit($limit)
                                                        //->offset($offset)
                                                        ->find_one();

                # Récupération du nb d'annonces correspondant à la recherche
                $nbAnnoncesPubliees = $app['idiorm.db'] ->for_table('vue_liste_annonces')
                                                        ->where('validationService', 1)
                                                        ->where('ouvert', 1)
                                                        ->where('lieuService', $codeINSEE)
                                                        ->order_by_desc('idService')
                                                        ->count();
                $totalAnnonces = $nbAnnoncesPubliees;
                $pageMax = ceil($totalAnnonces/$limit) ;

            }

            # Si juste catégorie remplie
            if(empty($localisation) AND $categorie !=1)
            {
                # Récupération des annonces

                $annoncesPubliees = $app['idiorm.db']->for_table('vue_liste_annonces')
                                                     ->where('validationService', 1)
                                                     ->where('ouvert', 1)
                                                     ->where('idCategorieService', $categorie)
                                                     ->order_by_desc('idService')
                                                     //->limit($limit)
                                                     //->offset($offset)
                                                     ->find_array();

                # Récupération du nb d'annonces correspondant à la recherche
                $nbAnnoncesPubliees = $app['idiorm.db'] ->for_table('vue_liste_annonces')
                                                        ->where('validationService', 1)
                                                        ->where('ouvert', 1)
                                                        ->where('idCategorieService', $categorie)
                                                        ->order_by_desc('idService')
                                                        ->count();

                $totalAnnonces = $nbAnnoncesPubliees;
                $pageMax = ceil($totalAnnonces/$limit) ;

            }

            # Si localisation et catégorie remplies
            if(!empty($localisation) AND $categorie != 1)
            {
                $codeINSEE   =  $localisation->codeINSEE;

                # Récupération des annonces
                $annoncesPubliees = $app['idiorm.db']   ->for_table('vue_liste_annonces')
                                                        ->where('validationService', 1)
                                                        ->where('ouvert', 1)
                                                        ->where('idCategorieService', $categorie)
                                                        ->where('lieuService', $codeINSEE)
                                                        ->order_by_desc('idService')
                                                        //->limit($limit)
                                                        //->offset($offset)
                                                        ->find_many();

                # Récupération du nb d'annonces correspondant à la recherche
                $nbAnnoncesPubliees = $app['idiorm.db'] ->for_table('vue_liste_annonces')
                                                        ->where('validationService', 1)
                                                        ->where('ouvert', 1)
                                                        ->where('lieuService', $codeINSEE)
                                                        ->where('idCategorieService', $categorie)
                                                        ->order_by_desc('idService')
                                                        ->count();

                $totalAnnonces = $nbAnnoncesPubliees;
                $pageMax = ceil($totalAnnonces/$limit) ;

            }


        }
        # --> FIN CONDITIONS AFFICHAGE RESULTATS RECHERCHE <-- #

        $annoncesJson = [];
        foreach ($annoncesPubliees as $key => $data) {
            $annoncesJson[$key]['titreService']             = utf8_encode($data['titreService']);
            $annoncesJson[$key]['photo']                    = utf8_encode($data['photo']);
            $annoncesJson[$key]['nomCategorieService']      = utf8_encode($data['nomCategorieService']);
            $annoncesJson[$key]['prenom']                   = utf8_encode($data['prenom']);
            $annoncesJson[$key]['nom']                      = utf8_encode($data['nom']);
            $annoncesJson[$key]['tarifService']             = utf8_encode($data['tarifService']);
            $annoncesJson[$key]['datePublicationService']   = utf8_encode($data['datePublicationService']);
            $annoncesJson[$key]['commune']                  = utf8_encode($data['commune']);
            $annoncesJson[$key]['descriptionService']       = utf8_encode($data['descriptionService']);
        };

        $array=[
            'annoncesPubliees'   => $annoncesJson,
            'nbAnnoncesPubliees' => $nbAnnoncesPubliees,
            'pageMax'            => $pageMax,
            'numeroPage'         => $numeroPage,
            'idCategorieService' => $categorie,
            'lieuService'        => $localisation
        ];

        # Affichage dans la vue
        return json_encode($array);
        #return json_encode($debug);


    } # --> FIN FONCTION rechercheActionPost() <-- #

} // Fin class IndexController
