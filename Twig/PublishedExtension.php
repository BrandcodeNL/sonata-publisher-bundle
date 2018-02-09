<?php

namespace BrandcodeNL\SonataPublisherBundle\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use BrandcodeNL\SonataPublisherBundle\Entity\PublishResponce;

class PublishedExtension extends AbstractExtension
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('isPublished', array($this, 'isPublishedFilter')),
        );
    }

    /**
     * Twig filter to check if given object is Published with the sonataPublisherBundle    
     * @param object $object
     * @return int $timesPublished
     */
    public function isPublishedFilter($object)
    {
        $count = $this->em->getRepository(PublishResponce::class)->createQueryBuilder('r')
            ->select("sum(r.count) as count")
            ->where('r.objectId = :object')           
            ->setParameter('object', $object->getId())
            ->getQuery()->getSingleResult();
            
        return $count['count'] ? $count['count'] : 0;
   
    }
}