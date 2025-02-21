<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\OAuth2\Adapter\Pdo;

use OAuth2\Storage\ClientInterface;

use function mt_getrandmax;
use function random_int;

class ClientTest extends AbstractBaseTest
{
    /** @dataProvider provideStorage */
    public function testGetClientDetails(ClientInterface $storage): void
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // nonexistant client_id
        $details = $storage->getClientDetails('fakeclient');
        $this->assertFalse($details);

        // valid client_id
        $details = $storage->getClientDetails('testclient');
        $this->assertNotNull($details);
        $this->assertArrayHasKey('client_id', $details);
        $this->assertArrayHasKey('client_secret', $details);
        $this->assertArrayHasKey('redirect_uri', $details);
    }

    /** @dataProvider provideStorage */
    public function testCheckRestrictedGrantType(ClientInterface $storage): void
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // Check valid
        $pass = $storage->checkRestrictedGrantType('testclient', 'authorization_code');
        $this->assertTrue($pass);

        // Check valid
        $pass = $storage->checkRestrictedGrantType('testclient', 'implicit');
        $this->assertTrue($pass);

        /** FIXME:  is true correct? */
        $this->assertTrue($storage->checkRestrictedGrantType('invalidclient', 'implicit'));
    }

    /** @dataProvider provideStorage */
    public function testGetAccessToken(ClientInterface $storage): void
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        // nonexistant client_id
        $details = $storage->getAccessToken('faketoken');
        $this->assertFalse($details);

        // valid client_id
        $details = $storage->getAccessToken('testtoken');
        $this->assertNotNull($details);
    }

    /** @dataProvider provideStorage */
    public function testSaveClient(ClientInterface $storage): void
    {
        if ($storage instanceof NullStorage) {
            $this->markTestSkipped('Skipped Storage: ' . $storage->getMessage());

            return;
        }

        $clientId = 'some-client-' . random_int(0, mt_getrandmax());

        // create a new client
        $success = $storage->setClientDetails(
            $clientId,
            'somesecret',
            'http://test.com',
            'client_credentials',
            'clientscope1',
            'userid'
        );
        $this->assertTrue($success);

        // valid client_id
        $details = $storage->getClientDetails($clientId);
        $this->assertEquals($details['redirect_uri'], 'http://test.com');
        $this->assertEquals($details['grant_types'], 'client_credentials');
        $this->assertEquals($details['scope'], 'clientscope1');
    }

    /** @dataProvider provideStorage */
    public function testIsPublicClient(ClientInterface $storage): void
    {
        $this->assertFalse($storage->isPublicClient('testclient'));
        // FIXME:  add a test which can return true
        // $this->assertTrue($storage->isPublicClient('oauth_test_client3'));
        $this->assertFalse($storage->isPublicClient('invalidclient'));
    }

    /** @dataProvider provideStorage */
    public function testGetClientScope(ClientInterface $storage): void
    {
        $this->assertEquals('clientscope1', $storage->getClientScope('testclient'));
        $this->assertFalse($storage->getClientScope('invalidclient'));
    }
}
