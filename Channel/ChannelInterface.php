<?php

namespace BrandcodeNL\SonataPublisherBundle\Channel;

interface ChannelInterface
{
    public function publish($object);
    public function __toString();

}