<?php

namespace Zoop\Entity\Test;

use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;
use Zend\Http\Header\GenericHeader;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zoop\GomiModule\DataModel\User;
use Zoop\Shard\Core\Events;
use Zoop\Shard\Manifest;
use Zoop\Shard\Serializer\Unserializer;

abstract class AbstractTest extends AbstractHttpControllerTestCase
{
    protected static $documentManager;
    protected static $noAuthDocumentManager;
    protected static $dbName;
    protected static $manifest;
    protected static $unserializer;
    protected static $serializer;
    public $calls;

    public function setUp()
    {
        $this->setApplicationConfig(
            require __DIR__ . '/../../../test.application.config.php'
        );

        if (!isset(self::$documentManager)) {
            self::$documentManager = $this->getApplicationServiceLocator()
                ->get('doctrine.odm.documentmanager.commerce');

            self::$noAuthDocumentManager = $this->getApplicationServiceLocator()
                ->get('doctrine.odm.documentmanager.noauth');

            self::$dbName = $this->getApplicationServiceLocator()
                ->get('config')['doctrine']['odm']['connection']['commerce']['dbname'];

            self::$manifest = $this->getApplicationServiceLocator()
                ->get('shard.commerce.manifest');

            self::$unserializer = self::$manifest->getServiceManager()
                ->get('unserializer');

            self::$serializer = self::$manifest->getServiceManager()
                ->get('serializer');

            $eventManager = self::$documentManager->getEventManager();
            $eventManager->addEventListener(Events::EXCEPTION, $this);
        }
    }

    public static function tearDownAfterClass()
    {
        self::clearDb();
    }

    public static function clearDb()
    {
        $documentManager = self::getDocumentManager();

        if ($documentManager instanceof DocumentManager) {
            $collections = $documentManager->getConnection()
                ->selectDatabase(self::getDbName())
                ->listCollections();

            foreach ($collections as $collection) {
                /* @var $collection \MongoCollection */
                $collection->drop();
            }
        }
    }

    /**
     * @return DocumentManager
     */
    public static function getDocumentManager()
    {
        return self::$documentManager;
    }

    /**
     * @return DocumentManager
     */
    public static function getNoAuthDocumentManager()
    {
        return self::$noAuthDocumentManager;
    }

    /**
     * @return string
     */
    public static function getDbName()
    {
        return self::$dbName;
    }

    /**
     *
     * @return Manifest
     */
    public static function getManifest()
    {
        return self::$manifest;
    }

    /**
     *
     * @return Unserializer
     */
    public static function getUnserializer()
    {
        return self::$unserializer;
    }

    public function __call($name, $arguments)
    {
        $this->calls[$name] = $arguments;
    }

    public function applyUserToRequest($request, $key, $secret)
    {
        $request->getHeaders()->addHeaders([
                GenericHeader::fromString('Authorization: Basic ' . base64_encode(sprintf('%s:%s', $key, $secret)))
            ]);
    }

    public function applyJsonRequest($request)
    {
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $request->getHeaders()
            ->addHeaders([
                $accept,
                ContentType::fromString('Content-type: application/json'),
            ]);
    }
}
