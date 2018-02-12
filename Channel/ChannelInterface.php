<?php
/*
 * This file is part of the BrandcodeNL SonataPublisherBundle.
 * (c) BrandcodeNL
 */
namespace BrandcodeNL\SonataPublisherBundle\Channel;

/**
 * @author Jeroen de Kok <jeroen.dekok@aveq.nl>
 */
interface ChannelInterface
{
    public function publish($object);
    public function __toString();

}