<?php

use App\Provider\IndexControllerProvider;
use App\Provider\MemberControllerProvider;

$app->mount('/', new IndexControllerProvider());
$app->mount('/membre', new MemberControllerProvider());