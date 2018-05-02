<?php
/*
 * This file is part of the BrandcodeNL SonataPublisherBundle.
 * (c) BrandcodeNL
 */
namespace BrandcodeNL\SonataPublisherBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Jeroen de Kok <jeroen.dekok@aveq.nl>
 * @ORM\Entity 
 */
class PublishResponce
{

     /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $count;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $resultData;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $channel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $objectId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $locale;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $user;
    

    public function __construct($status, $count, $resultData, $channel)
    {   
        $this->status = $status;
        $this->count = $count;
        $this->resultData = $resultData;
        $this->channel = $channel;
        $this->createdAt = new \DateTime();
    }
    
    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
        return $this;
    }


    public function getStatus(){
        return $this->status;
    }

    public function setStatus($status){
        $this->status = $status;
        return $this;
    }

    public function getCount(){
        return $this->count;
    }

    public function setCount($count){
        $this->count = $count;
        return $this;
    }

    public function getResultData(){
        return $this->resultData;
    }

    public function setResultData($resultData){
        $this->resultData = $resultData;
        return $this;
    }

    public function getChannel(){
        return $this->channel;
    }

    public function setChannel($channel){
        $this->channel = $channel;
        return $this;
    }

    public function getObjectId(){
        return $this->objectId;
    }

    public function setObjectId($objectId){
        $this->objectId = $objectId;
        return $this;
    }

    public function getLocale(){
        return $this->locale;
    }

    public function setLocale($locale){
        $this->locale = $locale;
        return $this;
    }

    public function getUser(){
        return $this->user;
    }

    public function setUser($user){
        $this->user  = $user;
        return $this;
    }

    public function getCreatedAt(){
        return $this->createdAt;
    }


}
