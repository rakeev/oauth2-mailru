<?php
namespace Aego\OAuth2\Client\Test\Provider;

class MailruTest extends \PHPUnit_Framework_TestCase
{
    protected $response;
    protected $provider;
    protected $token;

    protected function setUp()
    {
        $this->response = json_decode('[{"uid":"1234567890123456789","email":"username@mail.ru","sex":0,'
            .'"has_pic":1,"pic":"http://mock.ph/oto.jpg","first_name":"First","last_name":"Last",'
            .'"location":{"country":{"name":"Россия","id":"24"},"city":{"name":"Тольятти","id":"561"},'
            .'"region":{"name":"Самарская обл.","id":"246"}},"link":"http://my.mail.ru/mail/username/"}]');
        $this->provider = new \Aego\OAuth2\Client\Provider\Mailru([
            'clientId' => 'mock',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
        $this->token = new \League\OAuth2\Client\Token\AccessToken([
            'access_token' => 'mock_token',
        ]);
    }

    public function testUrlUserDetails()
    {
        $query = parse_url($this->provider->urlUserDetails($this->token), PHP_URL_QUERY);
        parse_str($query, $param);

        $this->assertEquals($this->token->accessToken, $param['session_key']);
        $this->assertEquals($this->provider->clientId, $param['app_id']);
        $this->assertNotEmpty($param['sig']);
    }

    public function testUserDetails()
    {
        $user = $this->provider->userDetails($this->response, $this->token);
        $this->assertInstanceOf('League\\OAuth2\\Client\\Entity\\User', $user);
        $res = $this->response[0];
        $this->assertEquals($res->uid, $user->uid);
        $this->assertEquals($res->email, $user->email);
        $this->assertEquals($res->location->city->name, $user->location);
        $this->assertEquals($res->first_name.' '.$res->last_name, $user->name);
        $this->assertEquals($res->first_name, $user->firstName);
        $this->assertEquals($res->last_name, $user->lastName);
        $this->assertEquals($res->pic, $user->imageUrl);
        $this->assertEquals('male', $user->gender);
        $this->assertNotEmpty($user->urls);
    }

    public function testUserDetailsEmpty()
    {
        $this->response[0]->has_pic = 0;
        unset($this->response[0]->location);
        $user = $this->provider->userDetails($this->response, $this->token);
        $this->assertEmpty($user->location);
        $this->assertEmpty($user->imageUrl);
    }

    public function testUserUid()
    {
        $uid = $this->provider->userUid($this->response, $this->token);
        $this->assertEquals($this->response[0]->uid, $uid);
    }

    public function testUserEmail()
    {
        $email = $this->provider->userEmail($this->response, $this->token);
        $this->assertEquals($this->response[0]->email, $email);
    }

    public function testUserScreenName()
    {
        $name = $this->provider->userScreenName($this->response, $this->token);
        $this->assertEquals([$this->response[0]->first_name, $this->response[0]->last_name], $name);
    }
}
