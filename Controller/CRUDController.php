<?php

namespace BrandcodeNL\SonataPublisherBundle\Controller;

use BrandcodeNL\SonataPublisherBundle\Channel\ChannelProvider;
use Sonata\AdminBundle\Controller\CRUDController as Controller;

class CRUDController extends Controller
{
    private $channelProvider;

    public function __construct(ChannelProvider $channelProvider)
    {
        $this->channelProvider = $channelProvider;
    }
    /**
     * Publish given object to registered 3th party channels
     */
    public function publishAction($id)
    {
        $object = $this->admin->getSubject();
        
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        foreach($this->channelProvider->getChannels() as $channel)
        {
            $channel->publish($object);   
        }
       
    }
}