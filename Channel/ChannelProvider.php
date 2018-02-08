<?php

namespace BrandcodeNL\SonataPublisherBundle\Channel;

class ChannelProvider
{
    
    private $channels;

    public function __construct()
    {
        $this->channels = array();
    }

    /**
     * Add a channel to the collection
     * @param ChannelInterface $channel
     */
    public function addChannel(ChannelInterface $channel)
    {
        $this->channels[] = $channel;
    }

    /**
     * Get all the channels
     * @return Array $channels
     */
    public function getChannels()
    {
        return $this->channels;
    }
}