<?php
namespace Aego\OAuth2\Client\Test\Provider;

use Aego\OAuth2\Client\Provider\Mailru;
use Aego\OAuth2\Client\Provider\MailruResourceOwner;

class MailruTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sample JSON response
     *
     * @var array
     */
    protected $response;

    /**
     * Mail.ru instance provider
     *
     * @var Aego\OAuth2\Client\Provider\Mailru
     */
    protected $provider;

    /**
     * Setup this unit test
     *
     * @return void
     */
    protected function setUp()
    {
        // Response example taken from: http://api.mail.ru/docs/reference/rest/users-getinfo/
        $this->response = '[{"uid":"15410773191172635989","first_name":"Евгений","last_name":"Маслов",'
            .'"nick":"maslov","email":"emaslov@mail.ru","sex":0,"birthday":"15.02.1980",'
            .'"has_pic":1,"pic":"http://avt.appsmail.ru/mail/emaslov/_avatar",'
            .'"pic_small":"http://avt.appsmail.ru/mail/emaslov/_avatarsmall",'
            .'"pic_big":"http://avt.appsmail.ru/mail/emaslov/_avatarbig",'
            .'"link":"http://my.mail.ru/mail/emaslov/","referer_type":"","referer_id":"",'
            .'"is_online":1,"friends_count":145,"is_verified":1,"vip":0,"app_installed":1,'
            .'"location":{"country":{"name":"Россия","id":"24"},"city":{"name":"Москва","id":'
            .'"25"},"region":{"name":"Москва","id":"999999"}}}]';

        $this->provider = new Mailru([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);

        parent::setUp();
    }

    /**
     * Tear down this unit test
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->provider = null;
        $this->response = null;
    }

    /**
     * Test base url
     *
     * @return void
     */
    public function testGetBaseAccessTokenUrl()
    {
        $this->assertEquals('https://connect.mail.ru/oauth/token', $this->provider->getBaseAccessTokenUrl([]));
    }

    /**
     * Test authorization url request
     *
     * @return void
     */
    public function testGetAuthorizationUrl()
    {
        $uri = parse_url($this->provider->getAuthorizationUrl());
        parse_str($uri['query'], $query);

        $this->assertEquals('connect.mail.ru', $uri['host']);
        $this->assertEquals('/oauth/authorize', $uri['path']);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertEquals('code', $query['response_type']);

        $this->assertNotNull($this->provider->getState());
    }

    /**
     * Test access token
     *
     * @return void
     */
    public function testGetAccessToken()
    {
        $response = $this->getMock('Psr\Http\Message\ResponseInterface');

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn('{"access_token":"mock_access_token","token_type":"bearer","expires_in":3600}');

        $response->expects($this->any())
            ->method('getHeader')
            ->willReturn(['content-type' => 'json']);

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);

        $client = $this->getMock('GuzzleHttp\ClientInterface');
        $this->provider->setHttpClient($client);
        $client->expects($this->once())
            ->method('send')
            ->willReturn($response);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertGreaterThan(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    /**
     * Test resource owner request
     *
     * @return void
     */
    public function testGetResourceOwnerDetailsUrl()
    {
        $test_uri = 'app_id=mock_client_id&method=users.getInfo&secure=1&session_key=mock_access_token';
        $test_sig = md5(str_replace('&', '', $test_uri) . 'mock_secret');
        $test_url = 'http://www.appsmail.ru/platform/api?' . $test_uri . '&sig=' . $test_sig;

        $token = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->once())
            ->method('getToken')
            ->willReturn('mock_access_token');

        $url = $this->provider->getResourceOwnerDetailsUrl($token);
        $this->assertEquals($test_url, $url);
    }

    /**
     * Test MailruResourceOwner
     *
     * @return void
     */
    public function testGetResourceOwner()
    {
        $response = json_decode($this->response, true);

        $token = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = $this->getMockBuilder(Mailru::class)
            ->setMethods(array('fetchResourceOwnerDetails'))
            ->getMock();

        $provider->expects($this->once())
            ->method('fetchResourceOwnerDetails')
            ->with($this->identicalTo($token))
            ->willReturn($response);

        $resource = $provider->getResourceOwner($token);

        $this->assertInstanceOf(MailruResourceOwner::class, $resource);
        $this->assertEquals('15410773191172635989', $resource->getId());
        $this->assertEquals('emaslov@mail.ru', $resource->getEmail());
        $this->assertEquals('Евгений Маслов', $resource->getName());
        $this->assertEquals('Евгений', $resource->getFirstName());
        $this->assertEquals('Маслов', $resource->getLastName());
        $this->assertEquals('maslov', $resource->getNickname());
        $this->assertEquals('http://avt.appsmail.ru/mail/emaslov/_avatar', $resource->getImageUrl());
        $this->assertEquals('male', $resource->getGender());
        $this->assertEquals('Россия', $resource->getCountry());
        $this->assertEquals('Москва', $resource->getCity());
        $this->assertEquals('Россия, Москва', $resource->getLocation());
    }
}
