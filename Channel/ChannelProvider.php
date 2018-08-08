<?php
/*
 * This file is part of the BrandcodeNL SonataPublisherBundle.
 * (c) BrandcodeNL
 */
namespace BrandcodeNL\SonataPublisherBundle\Channel;

use BrandcodeNL\SonataPublisherBundle\Channel\BatchChannelInterface;

/**
 * @author Jeroen de Kok <jeroen.dekok@aveq.nl>
 */
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

    /**
     * Get all the channels that support batch actions
     * @return Array $channels
     */
    public function getBatchChannels()
    {
        $batchChannels = array();
        foreach($this->channels as $channel){
            if ($channel instanceof BatchChannelInterface){
                $batchChannels[] = $channel;
            }
        }

        return $batchChannels;
    }

}