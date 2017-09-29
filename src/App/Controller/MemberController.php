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


class MemberController
{
    public function indexAction(Application $app, $idUser=NULL) {
        
        # Déclaration d'un Message
        $message = 'Espace Membre You2u';
        
        
        #Simulation d'un profil 
        $membre = $app['idiorm.db']
        ->for_table('users')
        ->find_one(1);
        
        # Affichage dans la Vue
        return $app['twig']->render('profil.html.twig',[
            'membre'  => $membre
        ]);
    }
    
    public function modifAction(Application $app, $idUser=NULL, Request $request) {
        
        # Déclaration d'un Message
        $message = 'Espace Membre You2u';
        
        
        #Simulation d'un profil
        $membre = $app['idiorm.db']
        ->for_table('users')
        ->find_one(1);
        
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
                'value'   =>  $membre['pseudo']
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
                'value'   =>  $membre['prenom']
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
                'value'   =>  $membre['nom']
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
                'value'   =>  $membre['email']
            ]
        ])
        ->add('adresse', TextType::class , [
            'required' => true,
            'label'    => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir une adresse')
                )
            ),
            'attr' => [
                'class'         => 'form-control',
                'value'   =>  $membre['adresse']
            ]
        ])
        ->add('codePostal',  ChoiceType::class, [
            'choices'   => $codePostaux(),
            'expanded' =>  false,
            'multiple'  => false,
            'label'     => false,
            'data' => $membre['codeINSEE'],
            'attr'      => [
                'class' => 'form-control',
            ]
        ])
        ->add('telFixe', TextType::class , [
            'required' => true,
            'label'    => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir une adresse')
                )
            ),
            'attr' => [
                'class'         => 'form-control',
                'value'   =>  $membre['telFixe']
            ]
        ])
        ->add('telMobile', TextType::class , [
            'required' => true,
            'label'    => false,
            'constraints' => array(new NotBlank(
                array('message' => 'Vous devez saisir une adresse')
                )
            ),
            'attr' => [
                'class'         => 'form-control',
                'value'   =>  $membre['telMobile']
            ]
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
                    $urlFichier = $membre['photo'];
                }
            
                
            #On récupérer la ville et le CP avec le code Insee
               $villeCP = $app['idiorm.db']->for_table('villes_rhone')->where('codeINSEE', $modifProfil['codePostal'])->find_one();
            
            #On modifie l'enregistrement #1 a modifié par l'id USER qu'on retrouvera par la variable ID USER
            $modifUser = $app['idiorm.db']->for_table('users')->find_one(1);
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
                'photo'                     => $urlFichier
                )
            );
            $modifUser->save();
            return $app->redirect($app['url_generator']->generate('membre_index'));
            
            
        }
        return $app['twig']->render('modificationprofil.html.twig', [
            
            'form' => $form->createView(),
            'membre' => $membre
        ]);
        
       
    }
    
    public function modifMdp( Application $app , $idUser=NULL, Request $req) {
        
        # Déclaration d'un Message
        $message = 'Espace Membre You2u';
        
        
        #Simulation d'un profil
        $membre = $app['idiorm.db']
        ->for_table('users')
        ->find_one(1);
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
        
        return $app['twig']->render('modificationmdp.html.twig' , [
            'membre' => $membre,
            'form' => $form->createView()
        ]);
        
        
    }
    
}

