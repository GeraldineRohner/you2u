<?php

namespace App\Controller;

use function json_encode;
use function print_r;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use function utf8_encode;
use function var_dump;


class IndexController
{

    public function indexAction(Application $app)
    {


        # Connexion à la BDD & Récupération des annonces
        $services = $app['idiorm.db']->for_table('vue_services')->find_result_set();


#----Récupération des 3 annonces en spotlight (utilisateurs les mieux notés)-------#
#------------------NON FONCTIONNEl, A TRAITER RAPIDEMENT---------------------------#
#----------------------------------------------------------------------------------#


        /*  $notesUsers = $app['idiorm.db']->for_table('users')->order_by_desc('noteMoyenne')->find_many();

          foreach ($notesUsers as $noteUsers) {
              $derniereAnnonce[] = $app['idiorm.db']->for_table('vue_services')->where('idUserProposantService', $noteUsers->idUser)->order_by_desc('idService')->find_one();
          }*/


#----------------------------------------------------------------------------------#
#----------------------------------------------------------------------------------#
#----------------------------------------------------------------------------------#


        $spotlight = $app['idiorm.db']->for_table('vue_services')->find_result_set();


        # Affichage dans le Vue
        return $app['twig']->render('index.html.twig', [
            'services' => $services,
            'spotlight' => $spotlight // penser à remplacer ce paramètre par $derniereAnnonce
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
        $longitude = substr($service['geo_point_2d'], strpos($service['geo_point_2d'], ',') + strlen(','));


        #On crée le formulaire de notation et de commentaires.
        $form = $app['form.factory']->createBuilder(FormType::class)
            ->add('commentaires', TextareaType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('note', ChoiceType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'choices' => array(
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
        if (!empty($app['user'])) {
            $dernierComment = $app['idiorm.db']->for_table('note_services')->where('idService', $idService)->where('idUserNotant', $app['user']->getIdUser())->order_by_desc('dateCommentaire')->limit(1)->find_one();
            $timeStampActuel = time();
            $delai = 60 * 60 * 24;
            if ($form->isValid()) {
                if (!empty($noteService) AND (($timeStampActuel - $dernierComment['dateCommentaire']) > $delai)) {
                    $nouvelleNote = $app['idiorm.db']->for_table('note_services')->create();
                    #On associe les colonnes de notre BDD avec les valeurs du formulaire
                    #Colonne MYSQL                                              #Valeurs du Fomulaire
                    $nouvelleNote->idService = $idService;
                    $nouvelleNote->idUserNotant = $app['user']->getIdUser();
                    $nouvelleNote->note = $noteService['note'];
                    $nouvelleNote->commentaires = $noteService['commentaires'];
                    $nouvelleNote->dateCommentaire = time();


                    $nouvelleNote->save();


                    return $app->redirect($app['url_generator']->generate('index_annonce',
                            [
                                'idService' => $idService,
                                'nomCategorieService' => ucfirst($nomCategorieService),
                                'slugService' => $slugService
                            ]
                        ) . '?note=success');

                } else {
                    return $app->redirect($app['url_generator']->generate('index_annonce',
                            [
                                'idService' => $idService,
                                'nomCategorieService' => ucfirst($nomCategorieService),
                                'slugService' => $slugService
                            ]
                        ) . '?note=error');
                }
                //                 return $app['twig']->render('annonce.html.twig', [
                //                     'service' => $service,
                //                     'suggestions' => $suggestions,
                //                     'latitude' => $latitude,
                //                     'longitude' => $longitude,
                //                     'form' => $form->createView()
                //                 ]);

            }
        }


        # Transmission à la Vue
        # On recupere les notes liés à l'annonce.
        $noteMoyenne = $app['idiorm.db']->for_table('note_services')->where('idService', $idService)->avg('note');
        $commentairesService = $app['idiorm.db']->for_table('vue_commentaires_services')->where('idService', $idService)->where_not_equal('commentaires', '')->find_result_set();
        $totalNote = $app['idiorm.db']->for_table('note_services')->where('idService', $idService)->count('note');
        $nombreStars = round($noteMoyenne, 0, PHP_ROUND_HALF_DOWN);
        if (($noteMoyenne - $nombreStars) > 0.25) {
            $halfstar = 'Halfstar';
        } else {
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

        # Affichage dans la Vue
        return $app['twig']->render('index.html.twig');

    }


    public function inscriptionAction(Application $app, Request $request)
    {

        # Création du formulaire d'inscription
        $formInscription = $app['form.factory']->createBuilder(FormType::class)
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
            /*  ------------------------------------------------------------------------------
            ------------------------------------------------------------------------------------
            CES CHAMPS SERONT A REMPLIR DANS LE PROFIL AFIN D'ALLEGER LA PROCÉDURE D'INSCRIPTION
            ------------------------------------------------------------------------------------
            ------------------------------------------------------------------------------------

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
                  'disabled' => true,
                  'constraints' => array(new NotBlank(array(
                          'message' => 'Veuillez entrer un code postal valide')
                  )),
                  'attr' => array(
                      'class' => 'form-control',
                      'placeholder' => 'Votre code postal'
                  )
              ))
              ->add('ville', TextType::class, array(
                  'required' => true,
                  'label' => false,
                  'constraints' => array(new NotBlank(array(
                          'message' => 'Veuillez entrer une ville')
                  )),
                  'attr' => array(
                      'class' => 'form-control typeahead',
                      'placeholder' => 'Votre ville de résidence'
                  )
              ))

              ->add('telFixe', TextType::class, array(
                      'required' => false,
                      'label'    => false,
                      'attr' => array(
                      'class' => 'form-control',
                      'placeholder' => 'N° de téléphone fixe'
                      )
              ))


              ->add('telMobile', TextType::class, array(
                  'required' => false,
                  'label'    => false,
                  'attr' => array(
                      'class' => 'form-control',
                      'placeholder' => 'N° de téléphone mobile'
                  )
              ))

              ->add('photo', FileType::class, [

                  'required'      => false,
                  'label'         => false,
                  'attr'          =>
                      ['class' => 'dropify'],
                  'constraints'   => [new File([
                      'maxSize' => '4096k',
                      'mimeTypes' => [
                          'image/png',
                          'image/jpeg',
                          'image/gif'
                      ]
                  ])]
              ])
              ->add('profilVisible', CheckboxType::class, array(
                  'label'    => '',
                  'required' => false,
              ))


            ------------------------------------------------------------------------------------
            ------------------------------------------------------------------------------------*/

            ->add('accepterConditions', CheckboxType::class, array(
                'label' => '',
                'required' => true,
                'constraints' => array(new NotBlank(array(
                        'message' => 'Vous devez accepter les conditions générales de vente')
                )
                )
            ))
            ->add('submit', SubmitType::class, ['label' => 'S\'inscrire',
                'attr' => array(
                    'class' => 'btn btn-primary'
                )])
            ->getForm();


        # Traitement des données POST
        $formInscription->handleRequest($request);


        # Vérifier si le Formulaire est valide


        if ($formInscription->isValid()) {


            $creationMembre = $formInscription->getData();

            if ($creationMembre['motDePasse'] === $creationMembre['motDePasseConfirmation']) {

                # Récupération des données du Formulaire


                # Récupération de l'image

                /*


                ------------------------------------------------------------------------------------
                ----------INSERTION DE LA PHOTO ET DU CODE INSEE (A METTRE DANS PROFIL)-------------
                ------------------------------------------------------------------------------------

                $photo  = $creationMembre['photo'];
                    $chemin = PATH_PUBLIC.'/img';





                    $villeCI = $app['idiorm.db']->for_table('villes_rhone')->select('codeINSEE')->where('codePostal', $creationMembre['codePostal'])->where('commune',strtoupper($creationMembre['ville']))->find_one();
                   */


                #On récupère la ville et le CP avec le code Insee


                #On associe les colonnes de notre BDD avec les valeurs du Formulaire
                #Colonne mySQL                         #Valeurs du Formulaire

                $insertionMembre = $app['idiorm.db']->for_table('users')->create();

                $insertionMembre->nom = $creationMembre['nom'];
                $insertionMembre->prenom = $creationMembre['prenom'];
                $insertionMembre->pseudo = $creationMembre['pseudo'];

                #On encode le password
                $insertionMembre->motDePasse = $app['security.encoder.digest']->encodePassword($creationMembre['motDePasse'], '');

                /*
                ------------------------------------------------------------------------------------
                -----------------------DONNÉES A INSERER DANS LA BDD VIA LE PROFIL------------------
                ------------------------------------------------------------------------------------

                $insertionMembre->adresse              = $creationMembre['adresse'];
                $insertionMembre->codePostal           = $creationMembre['codePostal'];
                $insertionMembre->codeINSEE            = $villeCI;
                $insertionMembre->ville                = $creationMembre['ville'];
                $insertionMembre->telFixe              = $creationMembre['telFixe'];
                $insertionMembre->telMobile            = $creationMembre['telMobile'];
                $insertionMembre->profilVisible        = $creationMembre['profilVisible'];



                if (isset($creationMembre['photo']))
                {
                    $extension = $photo->guessExtension();
                    $photo->move($chemin, $this->generateSlug($photo) . '.' . $extension);

                    $insertionMembre->photo = $this->generateSlug($creationMembre['photo'] . '.' . $extension);
                }

                ------------------------------------------------------------------------------------
                ------------------------------------------------------------------------------------
                ------------------------------------------------------------------------------------*/


                # Insertion en BDD
                $insertionMembre->save();


                # --> La sécurisation du formulaire de connexion est gérée par Silex directement <-- #
                echo "INSCRIPTION OK"; // Test, à supprimer
                return $app->redirect($app['url_generator']->generate('index_inscription?inscription=ok'));

            } else {
                $erreurMdp = "<div class='alert alert-danger' style='text-align:center;'>Veuillez saisir deux mot de passe identiques.</div>";
                return $app['twig']->render('inscription.html.twig', [
                    'formInscription' => $formInscription->createView(),
                    'message' => $erreurMdp]);
            }

        } else {
            return $app['twig']->render('inscription.html.twig', ['formInscription' => $formInscription->createView()]);
        }

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
            # --> La sécurisation du formulaire de connexion est gérée par Silex directement <-- #

            ->getForm();

        $form->handleRequest($request);

        # Affichage dans la Vue
        return $app['twig']->render('connexion.html.twig', [
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
            'form' => $form->createView()

        ]);
    }


    # Page de signalement utilisateur
    public function signalementUserAction(Application $app, Request $request, $idUser)

    {


        # Formulaire


        $userSignale = $app['idiorm.db']->for_table('users')->find_one($idUser);

        if ($userSignale != false) {


            $signalement = $app['form.factory']
                ->createBuilder(FormType::class)
                # On affiche le nom de l'utilisateur a signaler dans un champ verrouillé
                ->add('userProposantService', TextType::class, array(
                    'required' => false,
                    'label' => false,
                    'disabled' => true,
                    'attr' => array(
                        'class' => 'form-control',
                        'value' => utf8_encode($userSignale->pseudo)
                    )
                ))
                # Champ texte où l'utilisateur signalant pourra décrire son problème
                ->add('signalement', TextareaType::class, [
                    'required' => true,
                    'label' => false,
                    'constraints' => array(new NotBlank(
                        array('message' => 'Merci de décrire le problème rencontré')
                    )
                    ),
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
                ->add('submit', SubmitType::class, array(
                    'label' => 'Effectuer un signalement',
                    'attr' => array(
                        'class' => 'btn btn-primary'
                    )
                ))
                ->getForm();

            $signalement->handleRequest($request);

            if ($signalement->isValid()) {

                # Insertion BDD
                $envoiSignalement = $signalement->getData();
                $enregistrementSignalement = $app['idiorm.db']->for_table('signalements_users')->create();

                # Insertion BDD (infos utilisateur signalé/signalant, et timestamp)
                $enregistrementSignalement->idUserAlertant = $app['user']->getIdUser();
                $enregistrementSignalement->idUserSignale = $idUser;
                $enregistrementSignalement->dateAlerte = time();
                $enregistrementSignalement->message = $envoiSignalement['signalement'];

                # Enregistrement
                $enregistrementSignalement->save();

                # Si le signalement est posté, on redirige l'utilisateur vers un message de confirmation.
                return $app->redirect($app['url_generator']->generate('index_signalement_utilisateur', [
                        'idUser' => $idUser]) . '?signalement=succes');


            }

            return $app['twig']->render('signalementUtilisateur.html.twig', ['signalement' => $signalement->createView(),
                'idUser' => $idUser
            ]);

        } else {
            $message = "<div class=\"alert alert-warning\" style=\"text-align: center;\">Cet utilisateur n'existe pas.</div>";
            return $app['twig']->render('signalementUtilisateur.html.twig', ['userNonDefini' => $message]);
        }

    }

    # Page de signalement service
    public function signalementServiceAction(Application $app, Request $request, $idService)

    {


        # Formulaire
        $serviceSignale = $app['idiorm.db']->for_table('services')->find_one($idService);

        if ($serviceSignale != false) {
            $userProposantService = $app['idiorm.db']->for_table('users')->where('idUser', $serviceSignale->idUserProposantService)->find_one();


            $signalement = $app['form.factory']
                ->createBuilder(FormType::class)
                # On affiche le titre du service
                ->add('titreService', TextType::class, array(
                    'required' => false,
                    'label' => false,
                    'disabled' => true,
                    'attr' => array(
                        'class' => 'form-control',
                        'value' => utf8_encode($serviceSignale->titreService)
                    )
                ))
                ->add('datePublicationService', TextType::class, array(
                    'required' => false,
                    'label' => false,
                    'disabled' => true,
                    'attr' => array(
                        'class' => 'form-control',
                        'value' => 'Annonce publiée le ' . $serviceSignale->datePublicationService
                    )
                ))
                # On affiche le nom de l'utilisateur proposant le service a signaler dans un champ verrouillé
                ->add('userProposantService', TextType::class, array(
                    'required' => false,
                    'label' => false,
                    'disabled' => true,
                    'attr' => array(
                        'class' => 'form-control',
                        'value' => utf8_encode($userProposantService->pseudo)
                    )
                ))
                ->add('signalement', TextareaType::class, [
                    'required' => true,
                    'label' => false,
                    'constraints' => array(new NotBlank(
                        array('message' => 'Merci de décrire le problème rencontré')
                    )
                    ),
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
                ->add('submit', SubmitType::class, array(
                    'label' => 'Effectuer un signalement',
                    'attr' => array(
                        'class' => 'btn btn-primary'
                    )
                ))
                ->getForm();

            $signalement->handleRequest($request);

            if ($signalement->isValid()) {


                $envoiSignalement = $signalement->getData();

                $enregistrementSignalement = $app['idiorm.db']->for_table('signalements_services')->create();

                # Insertion BDD (infos utilisateur signalé/signalant, service signalé, et timestamp)
                $enregistrementSignalement->idServiceSignale = $idService;
                $enregistrementSignalement->idUserAlertant = $app['user']->getIdUser();
                $enregistrementSignalement->idUserSignale = $userProposantService->idUser;
                $enregistrementSignalement->dateAlerte = time();
                $enregistrementSignalement->message = $envoiSignalement['signalement'];


                $enregistrementSignalement->save();


                return $app->redirect($app['url_generator']->generate('index_signalement_annonce', [
                        'idService' => $idService]) . '?signalement=succes');


            }

            return $app['twig']->render('signalementService.html.twig', [
                'signalement' => $signalement->createView(),
                'idService' => $idService,
                'userProposantService' => $userProposantService->pseudo
            ]);

        } else {
            $message = "<div class=\"alert alert-warning\" style=\"text-align: center;\">Cette annonce n'existe pas.</div>";
            return $app['twig']->render('signalementService.html.twig', ['annonceNonDefinie' => $message]);
        }
    }


# Affichage de la page de recherche
public
function rechercheAction(Application $app, Request $request)
{
    # Récupération des catégories de services
    $categoriesService = function () use ($app) {
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
            'choices' => $categoriesService(),
            'expanded' => false,
            'multiple' => false,
            'label' => false,
            'attr' => array(
                'class' => 'form-control'
            )
        ))
        # -- Localisation -- #
        ->add('localisation', TextType::class, array(
            'required' => false,
            'label' => false,
            'attr' => array(
                'id' => 'recherche',
                'class' => 'typeahead form-control',
                'placeholder' => 'Localisation'
            )
        ))
        ->getForm();

    $form->handleRequest($request);


    # Affichage dans la vue
    return $app['twig']->render('recherche.html.twig', [
        'form' => $form->createView()
    ]);

}// Fin public function rechercheAction

public
function rechercheActionPost(Application $app, Request $request)
{

    $annoncesPubliees[] = null;
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
    if (null != $page && preg_match('#^[1-9][0-9]{0,9}$#', $page)) {
        # si oui, on récupère l'information de GET
        $numeroPage = $page;
    } else {
        # si non, on prend la page 1 par défaut
        $numeroPage = 1;
    }
    # Création de l'offset
    $offset = ($numeroPage - 1) * $limit;

    # --> FIN PAGINATION <-- #

    # --> CONDITIONS AFFICHAGE RESULTATS RECHERCHE <-- #
    if (!empty($_POST)) {
        # --> GESTION CHAMPS DE RECHERCHE <-- #
        # Récupération des données GET pour la catégorie de service et la localisation
        $categorie = $request->get('categorie');


        $localisation = $app['idiorm.db']->for_table('villes_rhone')
            ->where('commune', $request->get('localisation'))
            ->find_one();

        # Si juste localisation remplie
        if ($categorie == 1 AND !empty($localisation)) {

            $codeINSEE = $localisation->codeINSEE;

            # Récupération des annonces
            $annoncesPubliees = $app['idiorm.db']->for_table('vue_liste_annonces')
                ->where('validationService', 1)
                ->where('ouvert', 1)
                ->where('lieuService', $codeINSEE)
                ->order_by_desc('idService')
                //->limit($limit)
                //->offset($offset)
                ->find_array();

            # Récupération du nb d'annonces correspondant à la recherche
            $nbAnnoncesPubliees = $app['idiorm.db']->for_table('vue_liste_annonces')
                ->where('validationService', 1)
                ->where('ouvert', 1)
                ->where('lieuService', $codeINSEE)
                ->order_by_desc('idService')
                ->count();
            $totalAnnonces = $nbAnnoncesPubliees;
            $pageMax = ceil($totalAnnonces / $limit);

        }

        # Si juste catégorie remplie
        if (empty($localisation) AND $categorie != 1) {
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
            $nbAnnoncesPubliees = $app['idiorm.db']->for_table('vue_liste_annonces')
                ->where('validationService', 1)
                ->where('ouvert', 1)
                ->where('idCategorieService', $categorie)
                ->order_by_desc('idService')
                ->count();

            $totalAnnonces = $nbAnnoncesPubliees;
            $pageMax = ceil($totalAnnonces / $limit);

        }

        # Si localisation et catégorie remplies
        if (!empty($localisation) AND $categorie != 1) {
            $codeINSEE = $localisation->codeINSEE;

            # Récupération des annonces
            $annoncesPubliees = $app['idiorm.db']->for_table('vue_liste_annonces')
                ->where('validationService', 1)
                ->where('ouvert', 1)
                ->where('idCategorieService', $categorie)
                ->where('lieuService', $codeINSEE)
                ->order_by_desc('idService')
                //->limit($limit)
                //->offset($offset)
                ->find_array();

            # Récupération du nb d'annonces correspondant à la recherche
            $nbAnnoncesPubliees = $app['idiorm.db']->for_table('vue_liste_annonces')
                ->where('validationService', 1)
                ->where('ouvert', 1)
                ->where('lieuService', $codeINSEE)
                ->where('idCategorieService', $categorie)
                ->order_by_desc('idService')
                ->count();

            $totalAnnonces = $nbAnnoncesPubliees;
            $pageMax = ceil($totalAnnonces / $limit);

        }


    }
    # --> FIN CONDITIONS AFFICHAGE RESULTATS RECHERCHE <-- #

    $annoncesJson = [];
    foreach ($annoncesPubliees as $key => $data) {
        $annoncesJson[$key]['titreService'] = utf8_encode($data['titreService']);
        $annoncesJson[$key]['photo'] = utf8_encode($data['photo']);
        $annoncesJson[$key]['nomCategorieService'] = utf8_encode($data['nomCategorieService']);
        $annoncesJson[$key]['prenom'] = utf8_encode($data['prenom']);
        $annoncesJson[$key]['nom'] = utf8_encode($data['nom']);
        $annoncesJson[$key]['tarifService'] = utf8_encode($data['tarifService']);
        $annoncesJson[$key]['datePublicationService'] = utf8_encode($data['datePublicationService']);
        $annoncesJson[$key]['commune'] = utf8_encode($data['commune']);
        $annoncesJson[$key]['descriptionService'] = utf8_encode($data['descriptionService']);
    };

    $array = [
        'annoncesPubliees' => $annoncesJson,
        'nbAnnoncesPubliees' => $nbAnnoncesPubliees,
        'pageMax' => $pageMax,
        'numeroPage' => $numeroPage,
        'idCategorieService' => $categorie,
        'lieuService' => $localisation
    ];

    # Affichage dans la vue
    return json_encode($array);
    #return json_encode($debug);


} 
    
    public function affichageProfilAction($idUser ,Application $app, Request $request)
    {
        #On fait une requete pour récupérer les informations de l'utilisateur.
        $infoUser = $app['idiorm.db']
        ->for_table('users')
        ->where('idUser', $idUser)
        ->find_one();
        
        #On fait une requete pour récuper les services de l'utilisateur
        $servicesUser = $app['idiorm.db']
        ->for_table('vue_services_profil')
        ->where('idUser', $idUser)
        ->where('ouvert', 1)
        ->find_result_set();
        
        
        $commentairesUser = $app['idiorm.db']
        ->for_table('vue_commentaires_user')
        ->where('idUserNoted', $idUser)
        ->find_result_set();
        
        #On fait une requete pour récupérer note
        #Simulation d'un profil
        $noteMoyenne = $app['idiorm.db']->for_table('note_users')->where('idUserNoted',$idUser)->avg('note');
        $nombreStars = round($noteMoyenne, 0, PHP_ROUND_HALF_DOWN);
        
        if(($noteMoyenne-$nombreStars) > 0.25)
        {
            $halfstar = 'Halfstar';
        }
        else
        {
            $halfstar = '';
        }
        $totalNote = $app['idiorm.db']->for_table('note_users')->where('idUserNoted',$idUser)->count('note');
        
       
        
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
        if(!empty($app['user']))
        {
            $dernierComment = $app['idiorm.db']->for_table('note_users')->where('idUserNoted', $idUser)->where('idNotedBy', $app['user']->getIdUser())->order_by_desc('dateCommentaire')->limit(1)->find_one();
            $timeStampActuel = time();
            $delai = 60*60*24;
            if($form->isValid())
            {
                if(!empty($noteService) AND (($timeStampActuel - $dernierComment['dateCommentaire']) > $delai) AND ($app['user']->getIdUser() != $idUser))
                {
                    $nouvelleNote = $app['idiorm.db']->for_table('note_users')->create();
                    #On associe les colonnes de notre BDD avec les valeurs du formulaire
                    #Colonne MYSQL                                              #Valeurs du Fomulaire
                    $nouvelleNote->idUserNoted           =                          $idUser;
                    $nouvelleNote->idNotedBy         =                              $app['user']->getIdUser();
                    $nouvelleNote->note                  =                          $noteService['note'];
                    $nouvelleNote->commentaires          =                          $noteService['commentaires'];
                    $nouvelleNote->dateCommentaire       =                          time();
                    
                    
                    $nouvelleNote->save();
                    
                    
                    return $app->redirect($app['url_generator']->generate('index_profil',
                        [
                            'idUser'                 =>                   $idUser
                        ]
                        ).'?note=success');
                    
                }
                
                else
                {
                    return $app->redirect($app['url_generator']->generate('index_profil',
                        [
                            'idUser'                 =>                   $idUser
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
        }      
        
      
        return $app['twig']->render('affichageProfil.html.twig', [
            'infoUser'                   => $infoUser,
            'servicesUser'               => $servicesUser,
            'totalNote'                  => $totalNote,
            'halfstar'                   => $halfstar,
            'noteMoyenne'                => $noteMoyenne,
            'nombreStars'                => $nombreStars,
            'form'                       => $form->createView(),
            'commentairesUser'           => $commentairesUser
        ]);
    }

} // Fin class IndexController
