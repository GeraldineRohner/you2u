<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class constraintVille extends Constraint
{
    
    public $message = 'La ville "{{ string }}" doit correspondre à une ville du département du RHONE.';
    
  
    
}

