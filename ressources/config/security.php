<?php

use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Silex\Provider\SessionServiceProvider;
use App\Provider\MemberProvider;

# use Silex\Provider\SessionServiceProvider;
$app->register(new SessionServiceProvider());

#use Silex\Provider\SecurityServiceProvider;
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls'                => array(
        'main'                          => array(
            'pattern'                   => '^/',
            'http'                      => true,
            'anonymous'                 => true,
            'form'                      => array(
                'login_path'            => '/connexion',
                'check_path'            => '/membre/login_check'
            ),
            'logout'                    => array(
                'logout_path'           => '/deconnexion',
                'invalidate_session'    => true
            ),
            'users'                     => function() use($app)
            {
                return new MemberProvider($app['idiorm.db']);
            }
        ),
        'security.access_rules' => array(),
        'security.role_hierarchy' => array()
    )
));

# use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
$app['security.encoder.digest'] = function() use($app) {
    return new MessageDigestPasswordEncoder('sha1', false, 1);
};

$app['security.default_encoder'] = function() use($app) {
    return $app['security.encoder.digest'];
};