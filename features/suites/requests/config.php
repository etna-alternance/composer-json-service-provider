<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $container->parameters()->set("application_name", "Super appli");
    $container->parameters()->set("version", "1.4.2");

    $container->extension("framework", [
        "secret" => 'pouet',
        "test"   => true
    ]);
};
