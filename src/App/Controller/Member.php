<?php


namespace App\Controller;

use Symfony\Component\Security\Core\User\UserInterface;

class Member implements UserInterface
{

    # Définition des propriétés
    private $idUser,
            $pseudo,
            $prenom,
            $nom,
            $email,
            $motDePasse,
            $adresse,
            $codePostal,
            $telMobile,
            $telFixe,
            $photo,
            $cleValidation,
            $roleUser;

    public function __construct(
        $idUser,
        $pseudo,
        $prenom,
        $nom,
        $email,
        $motDePasse,
        $adresse,
        $codePostal,
        $ville,
        $telMobile,
        $telFixe,
        $photo,
        $cleValidation,
        $roleUser)
    {
        $this->idUser           = $idUser;
        $this->pseudo           = $pseudo;
        $this->prenom           = $prenom;
        $this->nom              = $nom;
        $this->email            = $email;
        $this->motDePasse       = $motDePasse;
        $this->adresse          = $adresse;
        $this->codePostal       = $codePostal;
        $this->ville            = $ville;
        $this->telMobile        = $telMobile;
        $this->telFixe          = $telFixe;
        $this->photo            = $photo;
        $this->cleValidation    = $cleValidation;
        $this->roleUser         = $roleUser;
    }


    public function getIdUser()
    {
        return $this->idUser;
    }


    public function getPseudo()
    {
        return $this->pseudo;
    }


    public function getPrenom()
    {
        return $this->prenom;
    }


    public function getNom()
    {
        return $this->nom;
    }


    public function getEmail()
    {
        return $this->email;
    }


    public function getMotDePasse()
    {
        return $this->motDePasse;
    }


    public function getAdresse()
    {
        return $this->adresse;
    }


    public function getCodePostal()
    {
        return $this->codePostal;
    }


    public function getTelMobile()
    {
        return $this->telMobile;
    }


    public function getTelFixe()
    {
        return $this->telFixe;
    }


    public function getPhoto()
    {
        return $this->photo;
    }


    public function getCleValidation()
    {
        return $this->cleValidation;
    }


    public function getRoleUser()
    {
        return $this->roleUser;
    }



    # ------------------- Méthodes héritées de l'Interface ------------------- #

    # Récupération de l'identifiant de connexion
    public function getUsername()
    {
        return $this->email;
    }

    # Récupération du MDP de connexion
    public function getPassword()
    {
        return $this->motDePasse;
    }

    # Récupération du rôle du membre
    public function getRoles()
    {
        return array();
    }

    # Sécurisation du MDP avec un Salt
    public function getSalt()
    {
        return null;
    }

    # Supprimer des infos sensibles de l'utilisateur avant qu'elles ne partent dans l'application
    public function eraseCredentials()
    {}

}