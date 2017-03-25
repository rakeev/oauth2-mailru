<?php

namespace Aego\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class MailruResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    private $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response[0];
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->response['uid'];
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->response['email'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->response['first_name'] . ' ' . $this->response['last_name'];
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->response['first_name'];
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->response['last_name'];
    }

    /**
    * @return string
    */
    public function getNickname()
    {
        return $this->response['nick'];
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return ($this->response['has_pic']) ? $this->response['pic'] : '' ;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return ($this->response['sex']) ? 'female' : 'male' ;
    }

    /**
     * Get the country
     * @return string Country name
     */
    public function getCountry()
    {
        return (isset($this->response['location']['country']['name']))
            ? $this->response['location']['country']['name'] : '';
    }

    /**
     * Get the country
     * @return string Country name
     */
    public function getCity()
    {
        return (isset($this->response['location']['city']['name']))
            ? $this->response['location']['city']['name'] : '';
    }

    /**
     * Get user's location as [Country, City]
     * @return string Location
     */
    public function getLocation()
    {
        $country = $this->getCountry();
        $city = $this->getCity();
        
        return (empty($country)) ? $city : $country . ', ' . $city;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->response;
    }
}
