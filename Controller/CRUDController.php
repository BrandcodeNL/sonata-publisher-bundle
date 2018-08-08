<?php
/*
 * This file is part of the BrandcodeNL SonataPublisherBundle.
 * (c) BrandcodeNL
 */
namespace BrandcodeNL\SonataPublisherBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use BrandcodeNL\SonataPublisherBundle\Entity\PublishResponce;
use BrandcodeNL\SonataPublisherBundle\Channel\ChannelProvider;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Jeroen de Kok <jeroen.dekok@aveq.nl>
 */
class CRUDController extends Controller
{
    private $channelProvider;
    private $tokenStorage;

    public function __construct(ChannelProvider $channelProvider, TokenStorageInterface $tokenStorage)
    {
        $this->channelProvider = $channelProvider;
        $this->tokenStorage = $tokenStorage;
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
        $history = $this->get('doctrine')->getEntityManager()->getRepository(PublishResponce::class)->findBy(
            array('objectId' => $id),
            array('id' => 'DESC')
        );
        return $this->renderWithExtraParams(
            'BrandcodeNLSonataPublisherBundle:CRUD:channel_picker.html.twig',        
            array(                
                'action' => 'publish',
                'object' => $object,
                'channels' => $this->channelProvider->getChannels(),
                'history' => $history
            )    
        );
       
    }

    
    /**
     * Publish given object to registered 3th party channels
     * TODO use message queue / Sonata Notification bundle for processing ? 
     */
    public function publishConfirmedAction(Request $request)
    {
        $modelManager = $this->admin->getModelManager();
        $targets = $modelManager->findBy(        
            $this->admin->getClass(),        
            array(
            'id' => explode(",", $request->get('_text_targetId'))
            )
        ); 
      
        
        if (!$targets) {
            throw new NotFoundHttpException(sprintf('unable to find the objects',  $request->get('_text_targetId')));
        }

        foreach($this->channelProvider->getChannels() as $key =>  $channel)
        {
            $locales = $request->get('locale');
            if(is_array($locales) && count($locales) > 0)
            {            
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
                            $result->setLocale($locale);
                            $result->setUser( (string) $this->tokenStorage->getToken()->getUser());
                        
                            $this->addFlash(
                                $result->getStatus(),
                                $this->trans('sonata_publish.'.$result->getStatus(),array(
                                        '%locale%' => $locale, 
                                        '%object%' => strval($object), 
                                        '%count%' => $result->getCount(), 
                                        '%message%' => json_encode($result->getResultData()), 
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
    
        }
        
        $this->get('doctrine')->getEntityManager()->flush();       
       
        return new RedirectResponse($this->admin->generateUrl('list'));
    }


    /**
     * @param ProxyQueryInterface $selectedModelQuery
     * @param Request             $request
     *
     * @return RedirectResponse
     */
    public function batchActionpublish(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {
        $modelManager = $this->admin->getModelManager();
        $targets = implode(",",$request->get('idx'));
        $targets = $modelManager->find($this->admin->getClass(), $targets);

        if ($targets === null){
            $this->addFlash('sonata_flash_info', 'flash_batch_merge_no_target');

            return new RedirectResponse(
                $this->admin->generateUrl('list', [
                    'filter' => $this->admin->getFilterParameters()
                ])
            );
        }

        $selectedModels = $selectedModelQuery->execute();
       
        //batch prepare templates
        $prepareTemplates = array();
        foreach($this->channelProvider->getBatchChannels() as $key=> $channel){
            $batchPrepare = $channel->batchPrepare($selectedModels);
            $prepareTemplates[$key] = $this->get('twig')->render($batchPrepare['template'], $batchPrepare['parameters']);
        }

        return $this->renderWithExtraParams(
            'BrandcodeNLSonataPublisherBundle:CRUD:batch_channel_picker.html.twig',        
            array(                
                'action' => 'publish',                
                'targets' => $selectedModels,
                'targetId' => implode(",", $request->get('idx')),
                'channels' => $this->channelProvider->getBatchChannels(),
                'batchTemplates' => $prepareTemplates,
                'history' => null
            )    
        );
       
    }

    /**
     * Batch publish confirmed action
     *
     * @param Request $request
     * @return void
     */
    public function batchPublishConfirmedAction (Request $request = null)
    {   
        $modelManager = $this->admin->getModelManager();
        $targets = $modelManager->findBy(        
            $this->admin->getClass(),        
            array(
            'id' => explode(",", $request->get('_text_targetId'))
            )
        );

        foreach($this->channelProvider->getChannels() as $key =>  $channel)
        {
            $locales = $request->get('locale');
            if(is_array($locales) && count($locales) > 0)
            {            
                if(isset($request->get('channel')[$key]) && $request->get('channel')[$key] == "true")
                { 
                    //publish the objects in this channel for each locale
                    foreach($locales as $locale => $value)
                    {
                        //make sure the objects are in the correct language                  
                        foreach($targets as $target){
                            $target = $this->translateObject($target, $locale);  
                        }

                        $result = $channel->publishBatch($targets);     
                        if($result instanceof PublishResponce)
                        {
                            $result->setObjectId(null);
                            $result->setLocale($locale);
                            $result->setUser( (string) $this->tokenStorage->getToken()->getUser());
                        
                            $this->addFlash(
                                $result->getStatus(),
                                $this->trans('sonata_publish.'.$result->getStatus(),array(
                                        '%locale%' => $locale, 
                                        '%object%' => "batch", 
                                        '%count%' => $result->getCount(), 
                                        '%message%' => json_encode($result->getResultData()), 
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