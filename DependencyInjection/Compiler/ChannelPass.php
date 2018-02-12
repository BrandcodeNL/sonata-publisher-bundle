<?php
// src/AppBundle/DependencyInjection/Compiler/MailTransportPass.php

/*
 * This file is part of the BrandcodeNL SonataPublisherBundle.
 * (c) BrandcodeNL
 */
namespace BrandcodeNL\SonataPublisherBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use BrandcodeNL\SonataPublisherBundle\Channel\ChannelProvider;

/**
 * @author Jeroen de Kok <jeroen.dekok@aveq.nl>
 */
class ChannelPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
       
        // always first check if the primary service is defined
        if (!$container->has('brandcode_nl_sonata_publisher.channel_provider')) {
            return;
        }
     
        $definition = $container->findDefinition('brandcode_nl_sonata_publisher.channel_provider');

        // find all service IDs with the app.mail_transport tag
        $taggedServices = $container->findTaggedServiceIds('sonata_publisher.channel');
     
        foreach ($taggedServices as $id => $tags) {
            // add the channel service to the ChannelProvider service
            $definition->addMethodCall('addChannel', array(new Reference($id)));
        }
    }
}