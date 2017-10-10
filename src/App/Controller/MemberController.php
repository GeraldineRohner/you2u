<?php
namespace App\Controller;



use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use App\Validator\Constraints\constraintVille;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;

class MemberController
{
    public function indexAction(Application $app)
    {

        # Déclaration d'un Message
        $message = 'Espace Membre You2u';


        #Simulation d'un profil
        $profilVisible = $app['idiorm.db']->for_table('users')->select('profilVisible')->where('idUser', $app['user']->getIdUser())->find_one();
        $noteMoyenne = $app['idiorm.db']->for_table('note_users')->where('idUserNoted', $app['user']->getIdUser())->avg('note');
        $nombreStars = round($noteMoyenne, 0, PHP_ROUND_HALF_DOWN);

        if (($noteMoyenne - $nombreStars) > 0.25) {
            $halfstar = 'Halfstar';
        }
            else
            {
                $halfstar = '';
            }
        $totalNote = $app['idiorm.db']->for_table('note_users')->where('idUserNoted',$app['user']->getIdUser())->count('note');
        $annoncesUser = $app['idiorm.db']->for_table('vue_services_profil')->where('idUser',$app['user']->getIdUser())->find_result_set();

    
        if($totalNote != 0)
        {
            # Affichage dans la Vue
            return $app['twig']->render('profil.html.twig', [
                'message' => $message,
                'noteMoyenne' => $noteMoyenne,
                'nombreStars' => $nombreStars,
                'halfstar'    => $halfstar,
                'annoncesUser' => $annoncesUser,
                'totalNote' => $totalNote,
                'profilVisible' => $profilVisible
            ]);
        } 
            else
            {
                # Affichage dans la Vue
                return $app['twig']->render('profil.html.twig',[
                    'message' => 'Bienvenue',
                    'totalNote' => $totalNote,
                    'annoncesUser' => $annoncesUser,
                    'profilVisible' => $profilVisible
                ]);
            }
    }

    public function modifAction(Application $app, Request $request)
    {

        # Déclaration d'un Message
        $message = 'Espace Membre You2u';

        $codePostaux = function () use ($app) {
            #recuperation des auteurs de la BDD
            $codePostaux = $app['idiorm.db']->for_table('villes_rhone')->order_by_asc('codePostal')->find_result_set();
            #on formate l'affichage pour le champ select (Choiceype)
            $array = [];
            foreach ($codePostaux as $codePostal) {
                $array[$codePostal->codePostal . '-' . $codePostal->commune] = $codePostal->codeINSEE;
            }

            return $array;
        };

        #creation du formulaire permettant la modification du profil.
        if ($app['user']->getProfilVisible() == 1)
        {
        $profilVisible = 'checked';
    }
    else {
        $profilVisible = false;
    }
        $form = $app['form.factory']->createBuilder(FormType::class)
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
                    'placeholder' => 'Votre pseudonyme',
                    'value' => $app['user']->getPseudo()
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
                        'message' => 'Votre prénom ne peut contenir que des lettres, tirets ou apostrophes et doit commencer et se terminer par une lettre'
                    ))),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre prénom',
                    'value' => $app['user']->getPrenom()
                )
            ))
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
                        'message' => 'Votre nom ne peut contenir que des lettres, tirets apostrophes ou espaces, et doit commencer et se terminer par une lettre'
                    ))),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Votre nom',
                    'value' => $app['user']->getNom()
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
                    'placeholder' => 'votre.email@exemple.fr',
                    'value' => $app['user']->getEmail()
                )
            ))
            ->add('adresse', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'value' => $app['user']->getAdresse()
                ]
            ])
            ->add('descriptionUser', TextType::class, [
                'required' => false,
                'label' => false,
                'constraints' => array(
                    new Length(array( # Contrainte de longueur
                        'min' => 0,
                        'max' => 500,
                        'maxMessage' => 'Votre description ne peut pas contenir plus de cinq cents caractères'
                    ))),
                'attr' => [
                    'class' => 'form-control',
                    'value' => $app['user']->getDescriptionUser()
                ]
            ])
            ->add('ville', TextType::class, [
                'required' => false,
                'label' => false,
                'constraints' => array(new constraintVille(
                    array('message' => 'Vous devez saisir une ville correcte ')
                )
                ),
                'attr' => [
                    'class' => 'form-control typeahead',
                    'value' => $app['user']->getVille(),
                ]
            ])
            ->add('telFixe', TextType::class, [
                'required' => false,
                'label' => false,
                'constraints' => array(new Regex(array( # Contraite de contenu
                    'pattern' => '/^\d{2}([\s\-.]?)((\d){2}\1){3}(\d){2}$/',
                    'message' => 'Le n° de téléphone entré est invalide'
                ))),
                'attr' => [
                    'class' => 'form-control',
                    'value' => $app['user']->getTelFixe()
                ]
            ])
            ->add('telMobile', TextType::class, [
                'required' => false,
                'label' => false,
                'constraints' => array(new Regex(array( # Contraite de contenu
                    'pattern' => '/^\d{2}([\s\-.]?)((\d){2}\1){3}(\d){2}$/',
                    'message' => 'Le n° de téléphone entré est invalide'
                ))),
                'attr' => [
                    'class' => 'form-control',
                    'value' => $app['user']->getTelMobile()
                ]
            ])
            ->add('profilVisible', CheckboxType::class, [

                'label' => false,
                'required' => false,
                'attr' => array('checked'   => $profilVisible)
            ])
            ->add('photo', FileType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'dropify'
                ],
                'constraints' => [new File([
                    'maxSize' => '4096k',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                        'image/gif'
                    ]
                ])]

            ])
            ->add('submit', SubmitType::class, ['label' => 'publier'])
            ->getForm();

        #Traitement des donneés POST stockées dans $request.
        $form->handleRequest($request);

        #Verification de la validité du formulaire.
        if ($form->isValid()) {
            $modifProfil = $form->getData();
            $file = $modifProfil['photo'];
            if (!empty($file)) {
                $urlFichier = $modifProfil['pseudo'] . time() . '.jpg';
                $urlFichier = strtolower(str_replace(' ', '-', $urlFichier));
                $file->move(PATH_PUBLIC . '/img/img_user/', $urlFichier);
            } else {
                $urlFichier = $app['user']->getPhoto();
            }


            #On récupérer la ville et le CP avec le code Insee
            $villeCP = $app['idiorm.db']->for_table('villes_rhone')->where('commune', $modifProfil['ville'])->find_one();


            #On modifie l'enregistrement qu'on retrouvera par la variable ID USER
            $modifUser = $app['idiorm.db']->for_table('users')->find_one($app['user']->getIdUser());

            #si le changement d'email est effectué on deconnecte l'utilisateur.
            if ($modifProfil['email'] != $modifUser['email']) {
                $changementEmail = 1;
            } else {
                $changementEmail = 0;
            }
            $modifUser->set(array(
                    'pseudo' => htmlspecialchars(utf8_encode($modifProfil['pseudo'])),
                    'prenom' => htmlspecialchars(utf8_encode($modifProfil['prenom'])),
                    'nom' => htmlspecialchars(utf8_encode($modifProfil['nom'])),
                    'email' => htmlspecialchars($modifProfil['email']),
                    'adresse' => htmlspecialchars(utf8_encode($modifProfil['adresse'])),
                    'ville' => $villeCP['commune'],
                    'codePostal' => $villeCP['codePostal'],
                    'codeINSEE' => $villeCP['codeINSEE'],
                    'telMobile' => htmlspecialchars($modifProfil['telMobile']),
                    'telFixe' => htmlspecialchars($modifProfil['telFixe']),
                    'profilVisible' => htmlspecialchars($modifProfil['profilVisible']),
                    'photo' => $urlFichier,
                    'descriptionUser' => htmlspecialchars(utf8_encode($modifProfil['descriptionUser']))
                )
            );
            $modifUser->save();

            if ($changementEmail == 0) {
                return $app->redirect($app['url_generator']->generate('membre_index'));
            } #si on a change l'email on deconnecte l'utilisateur.
            else {
                return $app->redirect($app['url_generator']->generate('deconnexion'));
            }


        }
        return $app['twig']->render('modificationprofil.html.twig', [

            'form' => $form->createView(),
            'message' => 'message'
        ]);


    }

    public function modifMdpAction(Application $app, Request $request)
    {


        $form = $app['form.factory']->createBuilder(FormType::class)
            ->add('motDePasse', PasswordType::class, [
                'required' => true,
                'label' => false,
                'constraints' => array(new Length(array( # Contrainte de longueur
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
                    'placeholder' => '*********'
                )
            ])
            ->add('confirm_motDePasse', PasswordType::class, [
                'required' => true,
                'label' => false,
                'constraints' => array(new NotBlank(
                    array('message' => 'Vous devez saisir un mot de passe')
                )
                ),
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => '*********'
                )
            ])
            ->getForm();

        #Traitement du formulaire.
        $form->handleRequest($request);

        if ($form->isValid()) {

            #On verifie que les deux champs PASSWORD et CONFIRMATION PASSWORD correspondent
            $modificationMotDePasse = $form->getData();
            if ($modificationMotDePasse['motDePasse'] === $modificationMotDePasse['confirm_motDePasse']) {

                #On encode les password
                $el = substr($modifProfil['pseudo'], 0, 3);
                $motDePasseModifie = $app['security.encoder.bcrypt']->encodePassword($modificationMotDePasse['motDePasse'], $el);

                #On modifie dans la base de données
                $modifMDP = $app['idiorm.db']->for_table('users')->find_one($app['user']->getIdUser());
                $modifMDP->set(array(
                        'motDePasse' => $motDePasseModifie
                    )
                );
                $modifMDP->save();
                return $app->redirect($app['url_generator']->generate('deconnexion'));
            }

        }


        return $app['twig']->render('modificationmdp.html.twig', [
            'form' => $form->createView(),
            'message' => 'message'
        ]);


    }


    public function ajoutAnnonceAction(Application $app, Request $request)
    {
        # Récupération des catégories de services
        $categoriesService = function () use ($app) {
            # Récupération des catégories dans la BDD
            $categoriesService = $app['idiorm.db']->for_table('categorie_service')->where_not_equal('idCategorieService', 1)->find_result_set();

            # Formatage de l'affichage pour le champ select (ChoiceType) du formulaire
            $array = [];
            foreach ($categoriesService as $categorie) :
                $array[$categorie->nomCategorieService] = $categorie->idCategorieService;
            endforeach;

            return $array;
        };

        # Récupération des villes du Rhône
        $villesRhone = function () use ($app) {
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
                'required' => true,
                'label' => false,
                'constraints' => array(
                    new Length(array( # Contraite de contenu
                        'min' => 3,
                        'max' => 100,
                        'minMessage' => 'Votre titre doit contenir au moins trois caractères',
                        'maxMessage' => 'Votre titre ne peut contenir plus de cent caractères'
                    ))
                    ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            # -- Catégorie -- #
            ->add('idCategorieService', ChoiceType::class, array(
                'choices' => $categoriesService(),
                'expanded' => false,
                'multiple' => false,
                'label' => false,
                'constraints' => array(
                    new Regex(array( # Contrainte de longueur
                        'pattern' => '/^[1-9]{1}[0-9]{0,5}$/i',
                        'message' => 'N° d\'annonce non valide'
                    ))),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            # -- Tarif -- #
            ->add('tarifService', NumberType::class, array(
                'label' => false,
                'required' => true,
                'constraints' => array(
                    new Regex(array( # Contrainte de longueur
                        'pattern' => '/^[0-9]{1,4}$/i',
                        'message' => 'Prix invalide. Il doit être compris entre 1 et 9999 (mettez 0 si vous ne souhaitez pas facturer le service proposé)'
                    ))),
                'attr' => array(
                    'class' => 'form-control',
                    'min' => 0
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
            # -- Périmètre d'action possible -- #
            ->add('perimetreAction', IntegerType::class, array(
                'label' => false,
                'required' => false,
                'constraints' => array(
                    new Regex(array( # Contrainte de longueur
                        'pattern' => '/^[1-2]{1}[0-9]{0,2}$/i',
                        'message' => 'Périmetre invalide. Veuillez saisir un nombre compris entre 1 et 299'
                    ))),
                'attr' => array(
                    'class' => 'form-control',
                    'min' => 0
                )
            ))
            # -- Localisation -- #
            ->add('lieuService', TextType::class, [
                'required' => true,
                'label' => false,
                'constraints' => array(new constraintVille(
                    array('message' => 'Vous devez saisir une ville correcte ')
                )
                ),
                'attr' => [
                    'class' => 'form-control typeahead'
                ]
            ])
            # -- Description -- #
            ->add('descriptionService', TextareaType::class, array(
                'required' => true,
                'label' => false,
                'constraints' => array(
                    new Length(array( # Contraite de contenu
                        'min' => 5,
                        'max' => 500,
                        'minMessage' => 'Votre description doit contenir au moins cinq caractères',
                        'maxMessage' => 'Votre description ne peut contenir plus de cinq cents caractères'
                    ))
                    ),
                'attr' => array(
                    'class' => 'form-control'
                )
            ))
            # -- Submit -- #
            ->add('submit', SubmitType::class, array(
                'label' => 'Publier',
                'attr' => array(
                    'class' => 'btn btn-info'
                )
            ))
            ->getForm();

        $form->handleRequest($request);

        # Vérification de la validité du formulaire
        if ($form->isValid()) :

            # Récupération des données du formulaire
            $annonce = $form->getData();


            # Recupération du code INSEE 
            $villeCP = $app['idiorm.db']->for_table('villes_rhone')->where('commune', $annonce['lieuService'])->find_one();
            # Insertion en BDD
            $annonceDb = $app['idiorm.db']->for_table('services')->create();

            # Association des colonnes de la BDD avec les champs du formulaire
            # Colonnes BDD                      # Champs du formulaire
            $annonceDb->titreService = htmlspecialchars(utf8_encode($annonce['titreService']));
            $annonceDb->idCategorieService = $annonce['idCategorieService'];

            if ($annonce['tarifService'] == 0) {
                $annonceDb->tarifService = htmlspecialchars(utf8_encode($annonce['tarifService']));
            }
            else {
                $tarif = ltrim(htmlspecialchars(utf8_encode($annonce['tarifService'])), '0');
                $annonceDb->tarifService = $tarif;
            }
            $annonceDb->lieuService = $villeCP['codeINSEE'];
            $annonceDb->perimetreAction = htmlspecialchars(utf8_encode($annonce['perimetreAction']));
            $annonceDb->descriptionService = htmlspecialchars(utf8_encode($annonce['descriptionService']));
            $annonceDb->datePublicationService = time();
            $annonceDb->idUserProposantService = $app['user']->getIdUser();

            # Insertion en BDD
            $annonceDb->save();

            return $app->redirect($app['url_generator']->generate('membre_index') . '?ajoutAnnonce=success');

        endif;

        # Affichage dans la Vue
        return $app['twig']->render('ajoutAnnonce.html.twig', array(
            'form' => $form->createView()
        ));
    }


    public function contactAction(Application $app, Request $request)
    {
/*$utilisateurContactant = $app['idiorm.db']->for_table('users')->select()->*/
$idUser = $app['user']->getIduser();
$userContactant = $app['user']->getPrenom().' '.$app['user']->getNom();
$userEmail = $app['user']->getEmail();

            $contact = $app['form.factory']
                ->createBuilder(FormType::class)
                # On affiche le nom de l'utilisateur a signaler dans un champ verrouillé
                ->add('userContactant', TextType::class, array(
                    'required' => false,
                    'label' => false,
                    'disabled' => true,
                    'attr' => array(
                        'class' => 'form-control',
                        'value' => utf8_encode($userContactant)
                    )
                ))
                ->add('userEmail', TextType::class, array(
                    'required' => false,
                    'label' => false,
                    'disabled' => true,
                    'attr' => array(
                        'class' => 'form-control',
                        'value' => utf8_encode($userEmail)
                    )
                ))
                ->add('messageSujet', TextType::class, [
                    'required' => true,
                    'label' => false,
                    'constraints' => array(
                        new Length(array( # Contrainte de longueur
                            'min' => 1,
                            'max' => 100,
                            'minMessage' => 'Votre sujet doit contenir au moins un caractère',
                            'maxMessage' => 'Votre sujet ne peut contenir plus de cent caractères'
                        )),
                        new Regex(array(  # Contraite de contenu
                            'pattern' => '/^[\w -\'éèàùûêâôë]+$/i',
                            'message' => 'Votre sujet ne peut contenir que des caractères alphanumériques, tirets apostrophes ou espaces'
                        ))),
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])




                # Champ texte où l'utilisateur signalant pourra décrire son problème
                ->add('messageContact', TextareaType::class, [
                    'required' => true,
                    'label' => false,
                    'constraints' => array(
                        new Length(array( # Contrainte de longueur
                            'min' => 5,
                            'max' => 500,
                            'minMessage' => 'Votre message doit contenir au moins cinq caractères',
                            'maxMessage' => 'Votre message ne peut contenir plus de cinq cents caractères'
                        ))),
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ])
                ->add('submit', SubmitType::class, array(
                    'label' => 'Envoyer votre message',
                    'attr' => array(
                        'class' => 'btn btn-primary'
                    )
                ))
                ->getForm();

            $contact->handleRequest($request);

            if ($contact->isValid()) {


                $dernierContact = $app['idiorm.db']->for_table('contact')->select('dateContact')->select('idUserContactant')->where('idUserContactant', $idUser)->order_by_desc('dateContact')->find_one();
                $timeStampActuel = time();
                $delai = 3600;

                if (($timeStampActuel- $dernierContact['dateContact']) > $delai )

                {


                    $envoiMessage = $contact ->getData();
                    $enregistrementMessage = $app['idiorm.db']->for_table('contact')->create();

                    # Insertion BDD (infos utilisateur signalé/signalant, et timestamp)
                    $enregistrementMessage->idUserContactant = $idUser;
                    $enregistrementMessage->dateContact = time();
                    $enregistrementMessage->messageSujet = utf8_encode(htmlspecialchars($envoiMessage['messageSujet']));
                    $enregistrementMessage->messageContact = htmlspecialchars($envoiMessage['messageContact']);

                    # Enregistrement
                    $enregistrementMessage->save();


                    # Si le signalement est posté, on redirige l'utilisateur vers un message de confirmation.
                    return $app->redirect($app['url_generator']->generate('membre_contact').'?contact=succes');
                }
                else
                {
                    $message = '';
                    return $app['twig']->render('contact.html.twig', [
                        'erreur' => $message]);
                }
            }

            return $app['twig']->render('contact.html.twig', ['contact' => $contact->createView()]);




    }


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

                # S'il n'existe pas déjà un signalement de l'utilisateur vers l'utilisateur ciblé
                if ( !($app['idiorm.db']->for_table('signalements_users')->where('idUserAlertant' ,$app['user']->getIdUser())->where('idUserSignale', $idUser)->find_one()) ) {

                    $envoiSignalement = $signalement->getData();
                    $enregistrementSignalement = $app['idiorm.db']->for_table('signalements_users')->create();

                    # Insertion BDD (infos utilisateur signalé/signalant, et timestamp)
                    $enregistrementSignalement->idUserAlertant = $app['user']->getIdUser();
                    $enregistrementSignalement->idUserSignale = $idUser;
                    $enregistrementSignalement->dateAlerte = time();
                    $enregistrementSignalement->message = utf8_encode(htmlspecialchars($envoiSignalement['signalement']));

                    # Enregistrement
                    $enregistrementSignalement->save();


                    # Si le signalement est posté, on redirige l'utilisateur vers un message de confirmation.
                    return $app->redirect($app['url_generator']->generate('membre_signalement_utilisateur', [
                            'idUser' => $idUser]) . '?signalement=succes');
                }
                else
                {
                    $message = "<div class=\"alert alert-warning\" style=\"text-align: center;\">Vous avez déjà signalé cet utilisateur. Il n'est pas possible d'envoyer un autre signalement pour le moment.</div>";
                    return $app['twig']->render('signalementUtilisateur.html.twig', ['erreur' => $message]);
                }
            }

            return $app['twig']->render('signalementUtilisateur.html.twig', ['signalement' => $signalement->createView(),
                'idUser' => $idUser
            ]);

        } else {
            $message = "<div class=\"alert alert-warning\" style=\"text-align: center;\">Cet utilisateur n'existe pas.</div>";
            return $app['twig']->render('signalementUtilisateur.html.twig', ['erreur' => $message]);
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

                if (!($app['idiorm.db']->for_table('signalements_services')->where('idUserAlertant', $app['user']->getIdUser())->where('idServiceSignale', $idService)->find_one())) {
                    $envoiSignalement = $signalement->getData();

                    $enregistrementSignalement = $app['idiorm.db']->for_table('signalements_services')->create();

                    # Insertion BDD (infos utilisateur signalé/signalant, service signalé, et timestamp)
                    $enregistrementSignalement->idServiceSignale = $idService;
                    $enregistrementSignalement->idUserAlertant = $app['user']->getIdUser();
                    $enregistrementSignalement->idUserSignale = $userProposantService->idUser;
                    $enregistrementSignalement->dateAlerte = time();
                    $enregistrementSignalement->message = utf8_encode(htmlspecialchars($envoiSignalement['signalement']));


                    $enregistrementSignalement->save();


                    return $app->redirect($app['url_generator']->generate('membre_signalement_annonce', [
                            'idService' => $idService]) . '?signalement=succes');

                }

                else
                {
                    $message = "<div class=\"alert alert-warning\" style=\"text-align: center;\">Vous avez déjà signalé cette annonce. Il n'est pas possible d'envoyer un autre signalement pour le moment.</div>";
                    return $app['twig']->render('signalementService.html.twig', ['erreur' => $message]);
                }


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

}