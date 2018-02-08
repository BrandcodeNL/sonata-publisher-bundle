<?php

namespace BrandcodeNL\SonataPublisherBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use BrandcodeNL\SonataPublisherBundle\DependencyInjection\Compiler\ChannelPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BrandcodeNLSonataPublisherBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ChannelPass());
        
    }
}
