<?php

use App\Provider\AdminControllerProvider;
use App\Provider\IndexControllerProvider;
use App\Provider\MemberControllerProvider;

$app->mount('/', new IndexControllerProvider());
$app->mount('/membre', new MemberControllerProvider());
$app->mount('/admin', new AdminControllerProvider());

