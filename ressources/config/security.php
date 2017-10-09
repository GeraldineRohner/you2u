<?php

use Silex\Provider\SecurityServiceProvider;

use Silex\Provider\SessionServiceProvider;
use App\Provider\MemberProvider;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

# use Silex\Provider\SessionServiceProvider;
$app->register(new SessionServiceProvider());
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls'                => array(
        'main'                              => array(
            'pattern'                           => '^/',
            'http'                              => true,
            'anonymous'                         => true,
            'form'                              => array(
                'login_path'                        => '/connexion',
                'check_path'                        => '/admin/login_check'
            ),
            'logout'                            => array(
                'logout_path'           => '/deconnexion',
                'invalidate_session'    => true
            ),
            'users'                     =>
                function() use($app)
                {
                    return new MemberProvider($app['idiorm.db']);
                }
        )

    ),'security.access_rules' => array(
    # Seul les utilisateurs ayant un ROLE ADMIN, pourront
    # accéder aux routes commençant par /admin
    array('^/admin', 'ROLE_ADMIN', 'http'),
    array('^/membre', 'ROLE_USER', 'http')
),
    'security.role_hierarchy' => array(
    'ROLE_ADMIN' => array('ROLE_USER')
) # Fin de 'security.role_hierarchy'
));



# use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
$app['security.encoder.bcrypt'] = function() use($app) {
    return new BCryptPasswordEncoder(10);
};

$app['security.default_encoder'] = function() use($app) {
    return $app['security.encoder.bcrypt'];
};



