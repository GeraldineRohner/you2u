<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;


class IndexController
{

    public function indexAction(Application $app)
    {

        # Connexion à la BDD & Récupération des annonces
        $services = $app['idiorm.db']->for_table('vue_services')->find_result_set();


        # Récupération des 3 annonces en spotlight (utilisateurs les mieux notés)









        $notesUsers = $app['idiorm.db']->for_table('users')->order_by_desc('noteMoyenne')->find_many();

        foreach ($notesUsers as $noteUsers)
        {
            $derniereAnnonce[] = $app['idiorm.db']->for_table('vue_services')->where('idUserProposantService', $noteUsers->idUser)->order_by_desc('idService')->find_one();
        }



        /*$spotlight = $app['idiorm.db']->for_table('vue_services')->find_result_set();*/


        # Affichage dans le Vue
        return $app['twig']->render('index.html.twig', [
            'services' => $services,
            'spotlight' => $derniereAnnonce
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


    public function inscriptionAction(Application $app, Request $request) {

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
                    'label'    => '',
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






        if($formInscription->isValid())


        {



            $creationMembre = $formInscription->getData();

            if ($creationMembre['motDePasse'] === $creationMembre['motDePasseConfirmation'])

            {

            # Récupération des données du Formulaire


            # Récupération de l'image

        /*


        ------------------------------------------------------------------------------------
        ----------INSERTION DE LA PHOTO ET DU CODE INSEE (A METTRE DANS PROFIL)-------------
        ------------------------------------------------------------------------------------

        $photo  = $creationMembre['photo'];
            $chemin = PATH_PUBLIC.'/img';




            $insertionMembre = $app['idiorm.db']->for_table('users')->create();
            $villeCI = $app['idiorm.db']->for_table('villes_rhone')->select('codeINSEE')->where('codePostal', $creationMembre['codePostal'])->where('commune',strtoupper($creationMembre['ville']))->find_one();
           */


            #On récupère la ville et le CP avec le code Insee


            #On associe les colonnes de notre BDD avec les valeurs du Formulaire
            #Colonne mySQL                         #Valeurs du Formulaire
            $insertionMembre->nom                  = $creationMembre['nom'];
            $insertionMembre->prenom               = $creationMembre['prenom'];
            $insertionMembre->pseudo               = $creationMembre['pseudo'];

            #On encode le password
            $insertionMembre->motDePasse           = $app['security.encoder.digest']->encodePassword($creationMembre['motDePasse'],'');

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
                echo "INSCRIPTION OK";
                return $app->redirect($app['url_generator']->generate('index_inscription?inscription=ok'));

            }

            else

            {
                $erreurMdp = "<div class='alert alert-danger' style='text-align:center;'>Veuillez saisir deux mot de passe identiques.</div>";
                return $app['twig']->render('inscription.html.twig', [
                    'formInscription' => $formInscription->createView(),
                    'message' => $erreurMdp]);
            }

        }
        else
        {
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
