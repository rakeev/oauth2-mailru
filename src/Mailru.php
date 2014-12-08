<?php
namespace Aego\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\AbstractProvider;

class Mailru extends AbstractProvider
{
    public $uidKey = 'x_mailru_vid';

    public function urlAuthorize()
    {
        return 'https://connect.mail.ru/oauth/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://connect.mail.ru/oauth/token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        $param = 'app_id='.$this->clientId.'&method=users.getInfo&secure=1&session_key='.$token;
        $sign = md5(str_replace('&', '', $param).$this->clientSecret);
        return 'http://www.appsmail.ru/platform/api?'.$param.'&sig='.$sign;
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User;
        $res = $response[0];
        $user->uid = $res->uid;
        $user->email = $res->email;
        $user->firstName = $res->first_name;
        $user->lastName = $res->last_name;
        $user->name = $user->firstName.' '.$user->lastName;
        $user->gender = $res->sex?'female':'male';
        $user->urls = $res->link;
        if (isset($res->location)) {
            $user->location = $res->location->city->name;
        }
        if ($res->has_pic) {
            $user->imageUrl = $res->pic;
        }
        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response[0]->uid;
    }

    public function userEmail($response, AccessToken $token)
    {
        return $response[0]->email;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return [$response[0]->first_name, $response[0]->last_name];
    }
}
