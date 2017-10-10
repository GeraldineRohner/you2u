<?php

namespace App\Controller;

use App\Validator\Constraints\constraintVille;
use App\Validator\Constraints\constraintVilleValidator;
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
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

use function utf8_encode;
use function var_dump;


class IndexController
{

    public function indexAction(Application $app)
    {


        # Connexion à la BDD & Récupération des annonces
        $services = $app['idiorm.db']->for_table('vue_services_profil')->where('validationService',1)->where('ouvert',1)->order_by_desc('idService')->limit(3)->find_result_set();



        $spotlight = $app['idiorm.db']->for_table('vue_services')->find_result_set();


        # Affichage dans le Vue
        return $app['twig']->render('index.html.twig', [
            'services' => $services,
            'spotlight' => $spotlight // penser à remplacer ce paramètre par $derniereAnnonce
        ]);
    }

    public function liste_categorieAction(Application $app)

    {
        $categories = $app['idiorm.db']->for_table('categorie_service')->where_not_equal('idCategorieService', 1)->find_result_set();

        return $app['twig']->render('categories.html.twig',
            ['categories' => $categories]);
    }


    public function categorie_serviceAction($nomCategorieService, Application $app)
    {

        # Connexion à la BDD et la Récupération des services de la categorie choisie par l'utilisateur
        $services = $app['idiorm.db']->for_table('vue_services')
            ->where('nomCategorieService', ucfirst($nomCategorieService))
            ->where('validationService' , 1)
            ->where('ouvert', 1)
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
        $service = $app['idiorm.db']->for_table('vue_services')->find_one($idService);

        # Récupérer des Articles de la Catégories (suggestions)
        $suggestions = $app['idiorm.db']->for_table('vue_services')
            # Je récupère uniquement les annonces de la même catégorie que mon annonce
            ->where('nomCategorieService', ucfirst($nomCategorieService))
            #Et que l'annonce est ouverte
            ->where('validationService', 1)
            ->where('ouvert', 1)
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
                'constraints' => array(
                    new Length(array(  # Contraite de taille
                        'min' => 5,
                        'max' => 500,
                        'minMessage' => 'Votre message doit contenir au moins cinq caractères.',
                        'maxMessage' => 'Votre nom ne peut contenir plus de cinq cents caractères.'
                    ))
                    ),
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('note', ChoiceType::class, [
                'required' => true,
                'label' => false,
                'constraints' => array(
                    new Regex(array(  # Contraite de contenu
                        'pattern' => '/^[1-5]{1,5}$/'
                    ))),
                'attr' => [
                    'class' => 'form-control'
                ],
                'choices' => array(
                    '1/5' => 1,
                    '2/5' => 2,
                    '3/5' => 3,
                    '4/5' => 4,
                    '5/5' => 5)
            ])
            ->getForm();

        #Traitement des donneés POST stockées dans $request.
        $form->handleRequest($request);

        #Verification de la validité du formulaire.
        $noteService = $form->getData();
        if (!empty($app['user'])) {
            #On verifie quel utilisateur propose le service 
            $infoService = $app['idiorm.db']->for_table('note_services')->where('idService', $idService)->find_one();
            
            #On recupère le dernier commentaire.
            $dernierComment = $app['idiorm.db']->for_table('note_services')->where('idService', $idService)->where('idUserNotant', $app['user']->getIdUser())->order_by_desc('dateCommentaire')->limit(1)->find_one();
            $timeStampActuel = time();
            $delai = 60 * 60 * 24;
            if ($form->isValid()) {
                if (!empty($noteService) AND (($timeStampActuel - $dernierComment['dateCommentaire']) > $delai) AND ($infoService['idUserProposantService'] != $app['user']->getIdUser())) {
                    $nouvelleNote = $app['idiorm.db']->for_table('note_services')->create();
                    #On associe les colonnes de notre BDD avec les valeurs du formulaire
                    #Colonne MYSQL                                              #Valeurs du Fomulaire
                    $nouvelleNote->idService = $idService;
                    $nouvelleNote->idUserNotant = $app['user']->getIdUser();
                    $nouvelleNote->note = utf8_encode(htmlspecialchars($noteService['note']));
                    $nouvelleNote->commentaires = utf8_encode(htmlspecialchars($noteService['commentaires']));
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
                'constraints' => array(
                    new Length(array( # Contrainte de longueur
                        'min' => 2,
                        'max' => 35,
                        'minMessage' => 'Votre nom doit contenir au moins deux caractères',
                        'maxMessage' => 'Votre nom ne peut contenir plus de trente-cinq caractères'
                    )),
                    new Regex(array(  # Contraite de contenu
                        'pattern' => '/^[a-zéèàùûêâôë]{1}[a-zéèàùûêâôë \'-]*[a-zéèàùûêâôë]$/i',
                        'message' => 'Votre nom ne peut contenir que des caractères alphanumériques, tirets apostrophes ou espaces, et doit commencer et se terminer par une lettre'
                    ))),

                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre nom'
                )
            ))
            ->add('prenom', TextType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(
                    new Length(array( # Contrainte de longueur
                        'min' => 3,
                        'max' => 35,
                        'minMessage' => 'Votre prénom doit contenir au moins trois caractères',
                        'maxMessage' => 'Votre prénom ne peut contenir plus de  trente-cinq caractères'
                    )),
                    new Regex(array( # Contraite de contenu
                        'pattern' => '/^[a-zéèàùûêâôë]{1}[a-zéèàùûêâôë\'-]*[a-zéèàùûêâôë]$/i',
                        'message' => 'Votre prénom ne peut contenir que des caractères alphanumériques, tirets ou apostrophes et doit commencer et se terminer par une lettre'
                    ))),

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
                ),
                    new Email(array(
                            'message' => 'L\'adresse email saisie est invalide',)
                    )),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'votre.email@exemple.fr'
                )
            ))
            ->add('pseudo', TextType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(
                    new Length(array( # Contrainte de longueur
                        'min' => 3,
                        'max' => 20,
                        'minMessage' => 'Votre pseudonyme doit contenir au moins trois caractères',
                        'maxMessage' => 'Votre pseudonyme ne peut contenir plus de  vingt caractères'
                    )),
                    new Regex(array( # Contraite de contenu
                        'pattern' => '/^[\w-\']+$/',
                        'message' => 'Votre pseudonyme ne doit contenir que des caractères alphanumériques, tirets, apostrophes ou underscores'
                    ))),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre pseudonyme'
                )
            ))
            # -- Mot de passe -- #
            ->add('motDePasse', PasswordType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(
                    new Length(array( # Contrainte de longueur
                        'min' => 6,
                        'max' => 20,
                        'minMessage' => 'Votre mot de passe doit contenir au moins six caractères',
                        'maxMessage' => 'Votre mot de passe ne peut contenir plus de  vingt caractères'
                    )),
                    new Regex(array( # Contraite de contenu
                        'pattern' => '/^[\w-\']+$/',
                        'message' => 'Votre mot de passe ne doit contenir que des caractères alphanumériques, tirets, apostrophes ou underscores'
                    ))),
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

            ->add('accepterConditions', CheckboxType::class, array(
                'label' => false,
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


            $insertionMembre = $app['idiorm.db']->for_table('users')->create();

            $insertionMembre->nom = htmlspecialchars(utf8_encode($creationMembre['nom']));
            $insertionMembre->prenom = htmlspecialchars(utf8_encode($creationMembre['prenom']));





            $insertionMembre->pseudo = htmlspecialchars(utf8_encode($creationMembre['pseudo']));




            # On recherche dans la BDD un mail similaire, et si la requête n'aboutit pas (false), on sauvegarde le mail
            if (!$app['idiorm.db']->for_table('users')->select('email')->where('email', $creationMembre['email'])->find_one()) {


                $insertionMembre->email = htmlspecialchars(utf8_encode($creationMembre['email']));


            } else {
                $erreurs[] = "<div class='alert alert-danger' style='text-align:center;'>Cette adresse email est <strong>déjà utilisée</strong>. Merci d'en choisir une différente.</div>";
            }

            if ($creationMembre['motDePasse'] === $creationMembre['motDePasseConfirmation'] /*&& mb_strlen($creationMembre['motDePasse']) > 5*/) {

                #On encode le password (bcrypt + sel, différent pour chaque utilisateur -on prend ici les 3 premieres lettres du pseudo)
                $el = substr($creationMembre['pseudo'], 0, 3);
                $insertionMembre->motDePasse = $app['security.encoder.bcrypt']->encodePassword($creationMembre['motDePasse'], $el);
            }
            else{
                $erreurs[]="<div class='alert alert-danger' style='text-align:center;'>Veuillez saisir deux mots de passe identiques.</div>";
            }



            if (!isset($erreurs)) { # Si pas d'erreurs dans le formulaire
                $insertionMembre->dateInscription = time(); # On log la date d'inscription
                $insertionMembre->save(); # Insertion en BDD

                return $app->redirect($app['url_generator']->generate('index_connexion') . '?inscription=ok');
            } else {

                return $app['twig']->render('inscription.html.twig', [
                    'formInscription' => $formInscription->createView(),
                    'messages' => $erreurs]);


            }
        }
        else {
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



    # Affichage de la page de recherche
    public function rechercheAction(Application $app, Request $request)
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
                'constraints' => array(new constraintVille(
                    array('message' => 'Vous devez saisir une ville correcte ')
                )
                ),
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

    public function rechercheActionPost(Application $app, Request $request)
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
            
            # Si aucune des conditions n'est remplie.
            if (empty($localisation) AND $categorie == 1) {
               
                
                # Récupération des annonces
                $annoncesPubliees = $app['idiorm.db']->for_table('vue_liste_annonces')
                ->where('validationService', 1)
                ->where('ouvert', 1)
                ->order_by_desc('idService')
                //->limit($limit)
                //->offset($offset)
                ->find_array();
                
                # Récupération du nb d'annonces correspondant à la recherche
                $nbAnnoncesPubliees = $app['idiorm.db']->for_table('vue_liste_annonces')
                ->where('validationService', 1)
                ->where('ouvert', 1)
                ->order_by_desc('idService')
                ->count();
                
                $totalAnnonces = $nbAnnoncesPubliees;
                $pageMax = ceil($totalAnnonces / $limit);
                
            }
            


        }
       
       
        # --> FIN CONDITIONS AFFICHAGE RESULTATS RECHERCHE <-- #

        $annoncesJson = [];
        foreach ($annoncesPubliees as $key => $data) {
            $annoncesJson[$key]['titreService'] = str_replace('?', '', utf8_decode($data['titreService']));
            $annoncesJson[$key]['titreServiceSlug'] = preg_replace('~[^\pL\d]+~u','-',utf8_decode($data['titreService']));
            $annoncesJson[$key]['photo'] = utf8_decode($data['photo']);
            $annoncesJson[$key]['nomCategorieService'] = utf8_decode(lcfirst($data['nomCategorieService']));
            $annoncesJson[$key]['idService'] = utf8_decode($data['idService']);
            $annoncesJson[$key]['prenom'] = utf8_decode($data['prenom']);
            $annoncesJson[$key]['nom'] = utf8_decode($data['nom']);
            $annoncesJson[$key]['tarifService'] = utf8_decode($data['tarifService']);
            $annoncesJson[$key]['datePublicationService'] = utf8_decode(date("d/m/Y", $data['datePublicationService']));
            $annoncesJson[$key]['commune'] = utf8_decode($data['commune']);
            $annoncesJson[$key]['descriptionService'] = utf8_decode($data['descriptionService']);
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
                'required' => true,
                'label'    => false,
                'attr' => [
                    'class'         => 'form-control'
                ],
                'choices'  => array(
                    '1/5' => 1,
                    '2/5' => 2,
                    '3/5' => 3,
                    '4/5' => 4,
                    '5/5' => 5)
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
                    $nouvelleNote->idNotedBy             =                          $app['user']->getIdUser();
                    $nouvelleNote->note                  =                          htmlspecialchars(utf8_encode($noteService['note']));
                    $nouvelleNote->commentaires          =                          htmlspecialchars(utf8_encode($noteService['commentaires']));
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
            'commentairesUser'           => $commentairesUser,

        ]);
    }

    # Affichage de la page CGU
    public function cguAction(Application $app)
    {
        # Affichage dans la vue
        return $app['twig']->render('cgu.html.twig');
    }

    # Affichage de la page Qui sommes nous
    public function quiSommesNousAction(Application $app)
    {

        # Affichage dans la vue
        return $app['twig']->render('presentation.html.twig');

    }


} // Fin class IndexController