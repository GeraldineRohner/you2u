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

class MemberController
{
    public function indexAction(Application $app) {
        
        # Déclaration d'un Message
        $message = 'Espace Membre You2u';
        
        
        #Simulation d'un profil 
        $noteMoyenne = $app['idiorm.db']->for_table('note_users')->where('idUserNoted',$app['user']->getIdUser())->avg('note');
        $nombreStars = round($noteMoyenne, 0, PHP_ROUND_HALF_DOWN);
        
        if(($noteMoyenne-$nombreStars) > 0.25)
        {
            $halfstar = 'Halfstar';
        }
            else
            {
                $halfstar = '';
            }
        $totalNote = $app['idiorm.db']->for_table('note_users')->where('idUserNoted',$app['user']->getIdUser())->count('note');
    
        if($totalNote != 0)
        {
            # Affichage dans la Vue
            return $app['twig']->render('profil.html.twig',[
                'message' => $message,
                'noteMoyenne' => $noteMoyenne,
                'nombreStars' => $nombreStars,
                'halfstar'    => $halfstar,
                'totalNote' => $totalNote
            ]);
        }
            else
            {
                # Affichage dans la Vue
                return $app['twig']->render('profil.html.twig',[
                    'message' => 'Bienvenue',
                    'totalNote' => $totalNote
                ]);
            }
    }
    
    public function modifAction(Application $app, Request $request) {
        
        # Déclaration d'un Message
        $message = 'Espace Membre You2u';
       
        $codePostaux = function() use ($app) {
            #recuperation des auteurs de la BDD
            $codePostaux = $app['idiorm.db']->for_table('villes_rhone')->order_by_asc('codePostal')->find_result_set();
            #on formate l'affichage pour le champ select (Choiceype)
            $array = [];
            foreach($codePostaux as $codePostal) {
                $array[$codePostal->codePostal.'-'.$codePostal->commune] = $codePostal->codeINSEE;
            }
            
            return $array;
        };
        
        #creation du formulaire permettant la modification du profil. 
        $form = $app['form.factory']->createBuilder(FormType::class)
        ->add('pseudo', TextType::class , [
            'required' => true,
            'label'    => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir un pseudo')
                )
           ),
            'attr' => [
                'class'         => 'form-control',
                'value'   =>  $app['user']->getPseudo()
            ]   
        ])
        ->add('prenom', TextType::class , [
            'required' => true,
            'label'    => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir un prenom')
                )
            ),
            'attr' => [
                'class'         => 'form-control',
                'value'   =>  $app['user']->getPrenom()
            ]
        ])
        ->add('nom', TextType::class , [
            'required' => true,
            'label'    => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir un prenom')
                )
            ),
            'attr' => [
                'class'         => 'form-control',
                'value'   =>  $app['user']->getNom()
            ]
        ])
        ->add('email', EmailType::class , [
            'required' => true,
            'label'    => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir un email')
                )
            ),
            'attr' => [
                'class'         => 'form-control',
                'value'   => $app['user']->getEmail()
            ]
        ])
        ->add('adresse', TextType::class , [
            'required' => false,
            'label'    => false,
            'attr' => [
                'class'         => 'form-control',
                'value'   => $app['user']->getAdresse()
            ]
        ])
        ->add('ville', TextType::class , [
            'required' => true,
            'label'    => false,
            'constraints' => array(new constraintVille(
                array('message' => 'Vous devez saisir une ville correcte ')
                )
            ),
            'attr'      => [
                'class' => 'form-control typeahead',
                'value' => $app['user']->getVille(),
            ]
        ])
        ->add('telFixe', TextType::class , [
            'required' => false,
            'label'    => false,
            'attr' => [
                'class'         => 'form-control',
                'value'   =>  $app['user']->getTelFixe()
            ]
        ])
        ->add('telMobile', TextType::class , [
            'required' => false,
            'label'    => false,
            'attr' => [
                'class'         => 'form-control',
                'value'   =>  $app['user']->getTelMobile()
            ]
        ])
        ->add('profilVisible', CheckboxType::class, [
            'label'    => '',
            'required' => false,
        ])
        ->add('photo', FileType::class , [
            'required'      =>  false,
            'label'         =>  false,
            'attr'          =>  [
                'class'     => 'dropify'
            ],
            'constraints'   => [new File([
                'maxSize' => '4096k',
                'mimeTypes' => [
                    'image/png',
                    'image/jpeg',
                    'image/gif'
                ]
            ])]
               
        ])
        ->add('submit', SubmitType::class , ['label' => 'publier'])
        
        
        ->getForm();
        
        #Traitement des donneés POST stockées dans $request.
        $form->handleRequest($request);
        
        #Verification de la validité du formulaire.
        if($form->isValid()){
            $modifProfil = $form->getData();
            $file = $modifProfil['photo'];
            if(!empty($file))
            {
                $urlFichier = $modifProfil['pseudo'].time().'.jpg';
                $urlFichier = strtolower(str_replace(' ','-',$urlFichier));
                $file->move(PATH_PUBLIC.'/img/img_user/', $urlFichier);
            }
                else
                {
                    $urlFichier = $app['user']->getPhoto();
                }
            
                
            #On récupérer la ville et le CP avec le code Insee
               $villeCP = $app['idiorm.db']->for_table('villes_rhone')->where('commune', $modifProfil['ville'])->find_one();
  
            
            #On modifie l'enregistrement #1 a modifié par l'id USER qu'on retrouvera par la variable ID USER
               $modifUser = $app['idiorm.db']->for_table('users')->find_one($app['user']->getIdUser());
            $modifUser->set(array(
                'pseudo'                    => $modifProfil['pseudo'],
                'prenom'                    => $modifProfil['prenom'],
                'nom'                       => $modifProfil['nom'],
                'email'                     => $modifProfil['email'],
                'adresse'                   => $modifProfil['adresse'],
                'ville'                     => $villeCP['commune'],
                'codePostal'                => $villeCP['codePostal'],
                'codeINSEE'                 => $villeCP['codeINSEE'],
                'telMobile'                 => $modifProfil['telMobile'],
                'telFixe'                   => $modifProfil['telFixe'],
                'profilVisible'             => $modifProfil['profilVisible'],
                'photo'                     => $urlFichier
                )
            );
            $modifUser->save();
            return $app->redirect($app['url_generator']->generate('membre_index'));
            
            
        }
        return $app['twig']->render('modificationprofil.html.twig', [
            
            'form' => $form->createView(),
            'message' => 'message'
        ]);
        
       
    }
    
    public function modifMdpAction( Application $app, Request $request) {
        
     
     
        $form = $app['form.factory']->createBuilder(FormType::class)
        ->add('motDePasse', PasswordType::class, [
            'required'  => true,
            'label'     => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir un mot de passe')
                )
            ),
            'attr'             => array(
                'class'        => 'form-control',
                'placeholder'  => '*********'
            )
        ])
        ->add('confirm_motDePasse', PasswordType::class, [
            'required'  => true,
            'label'     => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir un mot de passe')
                )
            ),
            'attr'             => array(
                'class'        => 'form-control',
                'placeholder'  => '*********'
            )
        ])
        ->getForm();
        
        #Traitement du formulaire.
        $form->handleRequest($request);
        
        if ($form->isValid()) {
           
            #On verifie que les deux champs PASSWORD et CONFIRMATION PASSWORD correspondent
            $modificationMotDePasse = $form->getData();
            if($modificationMotDePasse['motDePasse'] === $modificationMotDePasse['confirm_motDePasse'])
            {
                
                #On encode les password
                $motDePasseModifie = $app['security.encoder.digest']
                ->encodePassword($modificationMotDePasse['motDePasse'],'');
                
                #On modifie dans la base de données
                $modifMDP = $app['idiorm.db']->for_table('users')->find_one($app['user']->getIdUser());
                $modifMDP->set(array(
                    'motDePasse'                    => $motDePasseModifie
                    )
                  );
                $modifMDP->save();
                return $app->redirect($app['url_generator']->generate('deconnexion'));    
            }
            
        }
        
        
        return $app['twig']->render('modificationmdp.html.twig' , [
            'form' => $form->createView(),
            'message' => 'message'
        ]);
        
        
    }
    

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
            ->add('localisation', TextType::class, array(
                'required'          => false,
                'label'             => false,
                'attr'              => array(
                    'id'            => 'recherche',
                    'class'         => 'typeahead form-control',
                    'placeholder'   => 'Localisation'
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

            # -- Localisation -- #
            ->add('lieuService', TextType::class , [
                'required' => true,
                'label'    => false,
                'constraints' => array(new constraintVille(
                    array('message' => 'Vous devez saisir une ville correcte ')
                )
                ),
                'attr'      => [
                    'class' => 'form-control typeahead'
                ]
            ])

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

            
            # Recupération du code INSEE 
            $villeCP = $app['idiorm.db']->for_table('villes_rhone')->where('commune', $annonce['lieuService'])->find_one();
            # Insertion en BDD
            $annonceDb = $app['idiorm.db']->for_table('services')->create();

            # Association des colonnes de la BDD avec les champs du formulaire
            # Colonnes BDD                      # Champs du formulaire
            $annonceDb->titreService            = $annonce['titreService'];
            $annonceDb->idCategorieService      = $annonce['idCategorieService'];
            $annonceDb->tarifService            = $annonce['tarifService'];
            $annonceDb->lieuService             = $villeCP['codeINSEE'];
            $annonceDb->perimetreAction         = $annonce['perimetreAction'];
            $annonceDb->descriptionService      = $annonce['descriptionService'];
            $annonceDb->datePublicationService  = time();
            $annonceDb->idUserProposantService  = $app['user']->getIdUser();

            # Insertion en BDD
            $annonceDb->save();

            return $app->redirect($app['url_generator']->generate('membre_index').'?ajoutAnnonce=success');

        endif;

        # Affichage dans la Vue
        return $app['twig']->render('ajoutAnnonce.html.twig', array(
            'form'  => $form->createView()
        ));
    }
}

