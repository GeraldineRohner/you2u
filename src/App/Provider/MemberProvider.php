<?php


namespace App\Provider;

use App\Controller\Member;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MemberProvider implements UserProviderInterface
{

    private $_db;

    # Récupération de l'instance de la BDD (Idiorm ou Doctrine DBAL)
    public function __construct($db)
    {
        $this->_db = $db;
    }

    # ------------------- Méthodes héritées de l'Interface ------------------- #
    public function supportsClass($class)
    {
        return $class === 'App\Controller\Member';
    }

    public function refreshUser(UserInterface $users)
    {
        if(!$users instanceof Member){
            throw new UnsupportedUserException(
                sprintf('Les instances de "%s" ne sont pas autorisées.', get_class($users))
            );
        }
        return $this->loadUserByUsername($users->getUsername());
    }

    public function loadUserByUsername($email)
    {
        $users = $this->_db->for_table('users')
            ->where('email', $email)
            ->find_one();

        if(empty($users)){
            throw new UsernameNotFoundException(
                sprintf('Cet utilisateur "%s" n\'existe pas.', $email)
            );
        }
        return new Member(
            $users->idUser,
            $users->pseudo,
            $users->prenom,
            $users->nom,
            $users->email,
            $users->motDePasse,
            $users->adresse,
            $users->codePostal,
            $users->ville,
            $users->telMobile,
            $users->telFixe,
            $users->photo,
            $users->cleValidation,
            $users->roleUser,
            $users->codeINSEE,
            $users->descriptionUser
        );
    }
}
