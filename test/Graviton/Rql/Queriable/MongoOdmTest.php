<?php
/**
 * acceptence tests for the MongoOdm queriable.
 */

namespace Graviton\Rql\Queriable;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\Rql\Query;
use Graviton\Rql\Queriable\MongoOdm;
use Graviton\Rql\DataFixtures\MongoOdm as MongoOdmFixtures;
use Doctrine\Common\DataFixtures\Executor\MongoDBExecutor;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

/**
 * run tests against local mongodb with loaded fixtures
 *
 * @category MongoODM
 * @package  RqlParser
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class MongoOdmTest extends \PHPUnit_Framework_TestCase
{
    private $repo;

    /**
     * setup mongo-odm and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        AnnotationDriver::registerAnnotationClasses();

        $config = new Configuration();
        $config->setHydratorDir('/tmp/hydrators');
        $config->setHydratorNamespace('Hydrators');
        $config->setProxyDir('/tmp/proxies');
        $config->setProxyNamespace('Proxies');
        $config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__ . '/Documents/'));

        $dm = DocumentManager::create(new Connection(), $config);

        $loader = new Loader();
        $loader->addFixture(new MongoOdmFixtures());

        $executor = new MongoDBExecutor($dm, new MongoDBPurger());
        $executor->execute($loader->getFixtures());

        $this->repo = $dm->getRepository('Graviton\Rql\Queriable\Documents\Foo');
    }

    /**
     * @dataProvider basicQueryProvider
     *
     * @param string  $query    rql query string
     * @param array[] $expected structure of expected return value
     *
     * @return void
     */
    public function testBasicQueries($query, $expected)
    {
        //$parser = new Query($query);
        $mongo = new MongoOdm($this->repo);

        //$parser->applyToQueriable($mongo);
        $results = $mongo->getDocuments();

        $this->markTestSkipped("refactoring to visitor is needed");

        $this->assertEquals(count($expected), count($results), 'record count mismatch');

        foreach ($expected AS $position => $data) {
            foreach ($data AS $name => $value) {
                $this->assertEquals($value, $results[$position]->$name);
            }
        }
    }

    /**
     * @return array<string>
     */
    public function basicQueryProvider()
    {
        return array(
            'eq search for non existant document' => array(
                'eq(name,Not My Sprocket)', array()
            ),
            'eq search for document by name' => array(
                'eq(name,My First Sprocket)', array(
                    array('name' => 'My First Sprocket')
                )
            ),
            'eq OR search' => array(
                'or(eq(name,My First Sprocket),eq(name,The Third Wheel))', array(
                    array('name' => 'My First Sprocket'),
                    array('name' => 'The Third Wheel')
                )
            ),
            'eq OR search with sugar' => array(
                'eq(name,My First Sprocket)|eq(name,The Third Wheel)', array(
                    array('name' => 'My First Sprocket'),
                    array('name' => 'The Third Wheel')
                )
            ),
            'ne search' => array(
                'ne(name,My First Sprocket)', array(
                    array('name' => 'The Third Wheel'),
                    array('name' => 'A Simple Widget'),
                )
            ),
            'eq AND search' => array(
                'and(eq(name,My First Sprocket),eq(count,10))', array(
                    array('name' => 'My First Sprocket'),
                )
            ),
            'eq AND search with sugar' => array(
                'eq(name,My First Sprocket)&eq(count,10)', array(
                    array('name' => 'My First Sprocket'),
                )
            ),
            'gt 10 search' => array(
                'gt(count,10)', array(
                    array('name' => 'A Simple Widget', 'count' => 100)
                )
            ),
            'gte 10 search' => array(
                'gte(count,10)', array(
                    array('name' => 'My First Sprocket'),
                    array('name' => 'A Simple Widget', 'count' => 100)
                )
            ),
            'lt 10 search' => array(
                'lt(count,10)', array(
                    array('name' => 'The Third Wheel', 'count' => 3)
                )
            ),
            'lte 10 search' => array(
                'lte(count,10)', array(
                    array('name' => 'My First Sprocket', 'count' => 10),
                    array('name' => 'The Third Wheel', 'count' => 3)
                )
            ),
            'sort by int' => array(
                'sort(count)', array(
                    array('count' => 3),
                    array('count' => 10),
                    array('count' => 100),
                )
            ),
            'sort by int explicit' => array(
                'sort(+count)', array(
                    array('count' => 3),
                    array('count' => 10),
                    array('count' => 100),
                )
            ),
            'reverse sort by int' => array(
                'sort(-count)', array(
                    array('count' => 100),
                    array('count' => 10),
                    array('count' => 3),
                )
            ),
            'string sort' => array(
                'sort(name)', array(
                    array('name' => 'A Simple Widget', 'count' => 100),
                    array('name' => 'My First Sprocket', 'count' => 10),
                    array('name' => 'The Third Wheel', 'count' => 3),
                )
            ),
            'string sort explicit ' => array(
                'sort(+name)', array(
                    array('name' => 'A Simple Widget', 'count' => 100),
                    array('name' => 'My First Sprocket', 'count' => 10),
                    array('name' => 'The Third Wheel', 'count' => 3),
                )
            ),
            'reverse string sort' => array(
                'sort(-name)', array(
                    array('name' => 'The Third Wheel', 'count' => 3),
                    array('name' => 'My First Sprocket', 'count' => 10),
                    array('name' => 'A Simple Widget', 'count' => 100),
                )
            ),
        );

    }

}
