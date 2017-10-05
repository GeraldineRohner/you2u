<?php

namespace App\Controller;

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
/*use Symfony\Component\Validator\Constraints\File;*/
/*use Symfony\Component\Form\Extension\Core\Type\FileType;*/



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
        Application $app)
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

        # Transmission à la Vue
        return $app['twig']->render('annonce.html.twig', [
            'service' => $service,
            'suggestions' => $suggestions
        ]);
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

        }
        else {
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
                    'idService' => $idService]).'?signalement=succes');


            }

            return $app['twig']->render('signalementService.html.twig', [
                'signalement' => $signalement->createView(),
                'idService' => $idService,
                'userProposantService' => $userProposantService->pseudo
            ]);

        }
        else{
            $message = "<div class=\"alert alert-warning\" style=\"text-align: center;\">Cette annonce n'existe pas.</div>";
            return $app['twig']->render('signalementService.html.twig', ['annonceNonDefinie' => $message]);
        }
    }
}
