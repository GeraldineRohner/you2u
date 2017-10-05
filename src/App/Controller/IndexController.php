<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class IndexController
{

    public function indexAction(Application $app)
    {

        # Connexion à la BDD & Récupération des annonces
        $services = $app['idiorm.db']->for_table('vue_services')->find_result_set();


        # Récupération des 3 annonces en spotlight (utilisateurs les mieux notés)









//         $notesUsers = $app['idiorm.db']->for_table('users')->order_by_desc('noteMoyenne')->find_many();

//         foreach ($notesUsers as $noteUsers)
//         {
//             $derniereAnnonce[] = $app['idiorm.db']->for_table('vue_services')->where('idUserProposantService', $noteUsers->idUser)->order_by_desc('idService')->find_one();
//         }



        /*$spotlight = $app['idiorm.db']->for_table('vue_services')->find_result_set();*/


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
        Application $app,
        Request $request)
    {

        # Récupération de l'annonce
        $service = $app['idiorm.db']->for_table('vue_services')
            /*->where('idService',$idService)*/
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
            ->order_by_desc('idService')
            # Je récupère les résultats
            ->find_result_set();

            
            #On génére le géocode 
            
            #Je recupére geo_point_2D 
            $latitude = substr($service['geo_point_2d'], 0, strpos($service['geo_point_2d'], ','));
            $longitude = substr($service['geo_point_2d'], strpos($service['geo_point_2d'],',')+strlen(','));
            
            
            #On crée le formulaire de notation et de commentaires.
            $form = $app['form.factory']->createBuilder(FormType::class)
            ->add('commentaires', TextareaType::class , [
                'required' => false,
                'label'    => false,
                'attr' => [
                    'class'         => 'form-control'
                ]
            ])
            ->add('note', ChoiceType::class, [
                'required' => false,
                'label'    => false,
                'attr' => [
                    'class'         => 'form-control'
                ],
                'choices'  => array(
                    '*' => 1,
                    '**' => 2,
                    '***' => 3,
                    '****' => 4,
                    '*****' => 5)
            ])
            ->getForm();
            
            #Traitement des donneés POST stockées dans $request.
            $form->handleRequest($request);
            
            #Verification de la validité du formulaire.
            $noteService = $form->getData();
            $dernierComment = $app['idiorm.db']->for_table('note_services')->where('idService', $idService)->where('idUserNotant', $app['user']->getIdUser())->order_by_desc('dateCommentaire')->limit(1)->find_one();
            $timeStampActuel = time();
            $delai = 60*60*24;
                if($form->isValid())
                {
                    if(!empty($noteService) AND (($timeStampActuel - $dernierComment['dateCommentaire']) > $delai))
                    {
                        $nouvelleNote = $app['idiorm.db']->for_table('note_services')->create();
                        #On associe les colonnes de notre BDD avec les valeurs du formulaire
                        #Colonne MYSQL                                              #Valeurs du Fomulaire
                        $nouvelleNote->idService             =                          $idService;
                        $nouvelleNote->idUserNotant          =                          $app['user']->getIdUser();
                        $nouvelleNote->note                  =                          $noteService['note'];
                        $nouvelleNote->commentaires          =                          $noteService['commentaires'];
                        $nouvelleNote->dateCommentaire       =                          time();
                        
                        
                        $nouvelleNote->save();
                        
                       
                         return $app->redirect($app['url_generator']->generate('index_annonce',
                         [
                           'idService'              =>                   $idService,
                           'nomCategorieService'    =>                   ucfirst($nomCategorieService),
                          'slugService'            =>                   $slugService
                        ]
                             ).'?note=success');
                         
                    }
                    
                    else
                    {
                        return $app->redirect($app['url_generator']->generate('index_annonce',
                            [
                                'idService'              =>                   $idService,
                                'nomCategorieService'    =>                   ucfirst($nomCategorieService),
                                'slugService'            =>                   $slugService
                            ]
                            ).'?note=error');
                    }    
    //                 return $app['twig']->render('annonce.html.twig', [
    //                     'service' => $service,
    //                     'suggestions' => $suggestions,
    //                     'latitude' => $latitude,
    //                     'longitude' => $longitude,
    //                     'form' => $form->createView()
    //                 ]);
                
            }
            

        # Transmission à la Vue
        # On recupere les notes liés à l'annonce.
        $noteMoyenne = $app['idiorm.db']->for_table('note_services')->where('idService', $idService)->avg('note');
        $commentairesService = $app['idiorm.db']->for_table('vue_commentaires_services')->where('idService', $idService)->where_not_equal('commentaires','')->find_result_set();
        $totalNote = $app['idiorm.db']->for_table('note_services')->where('idService', $idService)->count('note');
        $nombreStars = round($noteMoyenne, 0, PHP_ROUND_HALF_DOWN);
        if(($noteMoyenne-$nombreStars) > 0.25)
        {
            $halfstar = 'Halfstar';
        }
        else
        {
            $halfstar = '';
        }
        
        return $app['twig']->render('annonce.html.twig', [
            'service' => $service,
            'suggestions' => $suggestions,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'nombreStars' => $nombreStars,
            'commentairesService' => $commentairesService,
            'totalNote' => $totalNote,
            'halfstar' => $halfstar,
            
            'form' => $form->createView()
        ]);
    }


    public function inscriptionAction(Application $app) {

        # Création du formulaire d'inscription
        $form = $app['form.factory']->createBuilder(FormType::class)
            # -- Identifiant -- #
            ->add('nom', TextType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez entrer votre nom')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre nom'
                )
            ))
            ->add('prenom', TextType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez entrer votre prénom')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre prénom'
                )
            ))
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
            ->add('pseudo', TextType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez choisir un nom d\'utilisateur')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre pseudonyme'
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
            ->add('motDePasseConfirmation', PasswordType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez renseigner une seconde fois votre mot de passe')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Veuillez retaper votre mot de passe'
                )
            ))
            ->add('adresse', TextType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez entrer un nom et n° de voie')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'voie'
                )
            ))
            ->add('codePostal', TextType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez entrer un code postal valide')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre code postal'
                )
            ))
            ->add('codePostal', TextType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Veuillez entrer une ville')
                )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre ville de résidence'
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













        return $app['twig']->render('inscription.html.twig');
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
