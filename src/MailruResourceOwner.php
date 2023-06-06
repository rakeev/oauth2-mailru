<?php

namespace Aego\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class MailruResourceOwner implements ResourceOwnerInterface
{

    /**
     * Response
     *
     * @var array
     */
    private $response;

    /**
     * Class constructor
     *
     * @param array $response
     * @return void
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * User's email address
     *
     * @return string Email address
     */
    public function getEmail()
    {
        return $this->response['email'];
    }

    /**
     * User's full name.
     *
     * Concatenated from first name and last name
     *
     * @return string Full name
     */
    public function getName()
    {
        return $this->response['first_name'] . ' ' . $this->response['last_name'];
    }

    /**
     * User's first name
     *
     * @return string First name
     */
    public function getFirstName()
    {
        return $this->response['first_name'];
    }

    /**
     * User's last name
     *
     * @return string Last name
     */
    public function getLastName()
    {
        return $this->response['last_name'];
    }

    /**
     * User's nickname
     *
     * @return string Nickname
     */
    public function getNickname()
    {
        return $this->response['nickname'];
    }

    /**
     * User's profile picture url
     *
     * @return string Profile picture url
     */
    public function getImageUrl()
    {
        return $this->response['image'] ?: '';
    }

    /**
     * User's gender
     *
     * @return string Gender
     */
    public function getGender()
    {
        return $this->response['gender'] === 'f' ? 'female' : 'male' ;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->response;
    }

}
