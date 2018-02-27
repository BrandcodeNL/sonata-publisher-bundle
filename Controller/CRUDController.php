<?php
/*
 * This file is part of the BrandcodeNL SonataPublisherBundle.
 * (c) BrandcodeNL
 */
namespace BrandcodeNL\SonataPublisherBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use BrandcodeNL\SonataPublisherBundle\Entity\PublishResponce;
use BrandcodeNL\SonataPublisherBundle\Channel\ChannelProvider;
use Sonata\AdminBundle\Controller\CRUDController as Controller;

/**
 * @author Jeroen de Kok <jeroen.dekok@aveq.nl>
 */
class CRUDController extends Controller
{
    private $channelProvider;

    public function __construct(ChannelProvider $channelProvider)
    {
        $this->channelProvider = $channelProvider;
    }
    /**
     * Find available publishing channels and let the user choose
     */
    public function publishAction($id)
    {
        
        $object = $this->admin->getSubject();
        
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }
        
        return $this->renderWithExtraParams(
            'BrandcodeNLSonataPublisherBundle:CRUD:channel_picker.html.twig',        
            array(                
                'action' => 'publish',
                'object' => $object,
                'channels' => $this->channelProvider->getChannels()
            )    
        );
       
    }

    /**
     * Publish given object to registered 3th party channels
     * TODO use message queue / Sonata Notification bundle for processing ? 
     */
    public function publishConfirmedAction($id, Request $request)
    {
       
        $object = $this->admin->getSubject();
        
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        foreach($this->channelProvider->getChannels() as $key =>  $channel)
        {
            $locales = $request->get('locale');
            if($request->get('channel')[$key] == "true")
            { 
                //publish this object in this channel for each locale
                foreach($locales as $locale => $value)
                {
                    //make sure the object is in the correct language                  
                    $object = $this->translateObject($object, $locale);                   
                    $result = $channel->publish($object);     
                    if($result instanceof PublishResponce)
                    {
                        $result->setObjectId($id);
                    
                        $this->addFlash(
                            $result->getStatus(),
                            $this->trans('sonata_publish.success',array(
                                    '%locale%' => $locale, 
                                    '%object%' => strval($object), 
                                    '%count%' => $result->getCount(), 
                                    '%channel%' => $this->trans($result->getChannel(), array(), 'messages')
                            ),
                            'BrandcodeNLSonataPublisherBundle'
                            )
                        );                  
                
                        $this->get('doctrine')->getEntityManager()->persist($result);                  
                    } 
                }

                
            }
    
        }
        
        $this->get('doctrine')->getEntityManager()->flush();       
       
        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    private function translateObject($object, $locale)
    {
      
        if($object->getLocale() != $locale)
        {
            $object->setLocale($locale);
            $this->get('doctrine')->getEntityManager()->refresh($object);
        }
        
        return $object;

    }
}