<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Silex\Application;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\ConstraintValidator;
use Idiorm\Silex\Provider\IdiormServiceProvider;
use Idiorm\Silex\Service\IdiormService;

class constraintVilleValidator extends ConstraintValidator
{
    protected $context;
   
    
    public function initialize(ExecutionContextInterface $context) {
        $this->context = $context;
    }

    
    public function validate($value, Constraint $constraint)
    {
        \ORM::configure('mysql:host='.DBHOST.';dbname='.DBNAME);
        \ORM::configure('username', DBUSERNAME);
        \ORM::configure('password', DBPASSWORD);
        
        if(\ORM::for_table('villes_rhone')->where('commune', $value)->count() != 1) {

            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->addViolation();
        }
        
    }
}

