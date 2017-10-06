<?php

namespace App\Controller;

use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;


class AdminController
{


    function signalementUtilisateurGestionAction(Application $app)
    {
        $signalementUtilisateur = $app['idiorm.db']->for_table('signalements_users')->find_result_set();
        return $app['twig']->render('gestionSignalementUtilisateur.html.twig',
            ['signalements' => $signalementUtilisateur]);
    }


    function signalementServiceGestionAction(Application $app)
    {
        $signalementService = $app['idiorm.db']->for_table('signalements_services')->find_result_set();
        return $app['twig']->render('gestionSignalementService.html.twig',
            ['signalements' => $signalementService]);

    }




}













