<?php

/**
 * @see       https://github.com/laminas/laminas-cache for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cache/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cache/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Cache\Storage\Adapter;

use Laminas\Cache\Storage\Adapter\MongoDbResourceManager;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Cache
 * @covers Laminas\Cache\Storage\Adapter\MongoDbResourceManager
 */
class MongoDbResourceManagerTest extends TestCase
{
    /**
     * @var MongoDbResourceManager
     */
    protected $object;

    public function setUp()
    {
        if (getenv('TESTS_LAMINAS_CACHE_MONGODB_ENABLED') != 'true') {
            $this->markTestSkipped('Enable TESTS_LAMINAS_CACHE_MONGODB_ENABLED to run this test');
        }

        if (! extension_loaded('mongo') || ! class_exists('\Mongo') || ! class_exists('\MongoClient')) {
            // Allow tests to run if Mongo extension is loaded, or we have a polyfill in place
            $this->markTestSkipped("Mongo extension is not loaded");
        }

        $this->object = new MongoDbResourceManager();
    }

    public function testSetResourceAlreadyCreated()
    {
        $this->assertAttributeEmpty('resources', $this->object);

        $id = 'foo';

        $clientClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';
        $client = new $clientClass(getenv('TESTS_LAMINAS_CACHE_MONGODB_CONNECTSTRING'));
        $resource = $client->selectCollection(
            getenv('TESTS_LAMINAS_CACHE_MONGODB_DATABASE'),
            getenv('TESTS_LAMINAS_CACHE_MONGODB_COLLECTION')
        );

        $this->object->setResource($id, $resource);

        $this->assertSame($resource, $this->object->getResource($id));
    }

    public function testSetResourceArray()
    {
        $this->assertAttributeEmpty('resources', $this->object);

        $id     = 'foo';
        $server = 'mongodb://test:1234';

        $this->object->setResource($id, ['server' => $server]);

        $this->assertSame($server, $this->object->getServer($id));
    }

    public function testSetResourceThrowsException()
    {
        $id = 'foo';
        $resource = new \stdClass();

        $this->expectException('Laminas\Cache\Exception\InvalidArgumentException');
        $this->object->setResource($id, $resource);
    }

    public function testHasResourceEmpty()
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));
    }

    public function testHasResourceSet()
    {
        $id = 'foo';

        $this->object->setResource($id, ['foo' => 'bar']);

        $this->assertTrue($this->object->hasResource($id));
    }

    public function testGetResourceNotSet()
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));

        $this->expectException('Laminas\Cache\Exception\RuntimeException');
        $this->object->getResource($id);
    }

    public function testGetResourceInitialized()
    {
        $id = 'foo';

        $clientClass = (version_compare(phpversion('mongo'), '1.3.0', '<')) ? '\Mongo' : '\MongoClient';
        $client = new $clientClass(getenv('TESTS_LAMINAS_CACHE_MONGODB_CONNECTSTRING'));
        $resource = $client->selectCollection(
            getenv('TESTS_LAMINAS_CACHE_MONGODB_DATABASE'),
            getenv('TESTS_LAMINAS_CACHE_MONGODB_COLLECTION')
        );

        $this->object->setResource($id, $resource);

        $this->assertSame($resource, $this->object->getResource($id));
    }

    public function testGetResourceNewResource()
    {
        $id                = 'foo';
        $server            = getenv('TESTS_LAMINAS_CACHE_MONGODB_CONNECTSTRING');
        $connectionOptions = ['connectTimeoutMS' => 5];
        $database          = getenv('TESTS_LAMINAS_CACHE_MONGODB_DATABASE');
        $collection        = getenv('TESTS_LAMINAS_CACHE_MONGODB_COLLECTION');

        $this->object->setServer($id, $server);
        $this->object->setConnectionOptions($id, $connectionOptions);
        $this->object->setDatabase($id, $database);
        $this->object->setCollection($id, $collection);

        $this->assertInstanceOf('\MongoCollection', $this->object->getResource($id));
    }

    public function testGetResourceUnknownServerThrowsException()
    {
        $id                = 'foo';
        $server            = 'mongodb://unknown.unknown';
        $connectionOptions = ['connectTimeoutMS' => 5];
        $database          = getenv('TESTS_LAMINAS_CACHE_MONGODB_DATABASE');
        $collection        = getenv('TESTS_LAMINAS_CACHE_MONGODB_COLLECTION');

        $this->object->setServer($id, $server);
        $this->object->setConnectionOptions($id, $connectionOptions);
        $this->object->setDatabase($id, $database);
        $this->object->setCollection($id, $collection);

        $this->expectException('Laminas\Cache\Exception\RuntimeException');
        $this->object->getResource($id);
    }

    public function testGetSetCollection()
    {
        $resourceId     = 'testResource';
        $collectionName = 'testCollection';

        $this->object->setCollection($resourceId, $collectionName);
        $this->assertSame($collectionName, $this->object->getCollection($resourceId));
    }

    public function testGetSetConnectionOptions()
    {
        $resourceId        = 'testResource';
        $connectionOptions = ['test1' => 'option1', 'test2' => 'option2'];

        $this->object->setConnectionOptions($resourceId, $connectionOptions);
        $this->assertSame($connectionOptions, $this->object->getConnectionOptions($resourceId));
    }

    public function testGetSetServer()
    {
        $resourceId = 'testResource';
        $server     = 'testServer';

        $this->object->setServer($resourceId, $server);
        $this->assertSame($server, $this->object->getServer($resourceId));
    }

    public function testGetSetDriverOptions()
    {
        $resourceId    = 'testResource';
        $driverOptions = ['test1' => 'option1', 'test2' => 'option2'];

        $this->object->setDriverOptions($resourceId, $driverOptions);
        $this->assertSame($driverOptions, $this->object->getDriverOptions($resourceId));
    }

    public function testGetSetDatabase()
    {
        $resourceId = 'testResource';
        $database   = 'testDatabase';

        $this->object->setDatabase($resourceId, $database);
        $this->assertSame($database, $this->object->getDatabase($resourceId));
    }
}
