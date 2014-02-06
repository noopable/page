<?php
namespace PageTest;
/*
 *
 *
 * @copyright Copyright (c) 2013-2013 KipsProduction (http://www.kips.gr.jp)
 * @license   http://www.kips.gr.jp/newbsd/LICENSE.txt New BSD License
 */
use Page\BlockInitializer;
use Page\Service;
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-12-16 at 15:40:54.
 */
class BlockInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockInitializer
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->service = new Service;
        $this->object = new BlockInitializer($this->service);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Page\BlockInitializer::setCreationOptions
     * @todo   Implement testSetCreationOptions().
     */
    public function testSetCreationOptions()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Page\BlockInitializer::initialize
     * @todo   Implement testInitialize().
     */
    public function testInitialize()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Page\BlockInitializer::getBlocks
     * @todo   Implement testGetBlocks().
     */
    public function testGetBlocks()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Page\BlockInitializer::setServiceLocator
     * @todo   Implement testSetServiceLocator().
     */
    public function testSetServiceLocator()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Page\BlockInitializer::getServiceLocator
     * @todo   Implement testGetServiceLocator().
     */
    public function testGetServiceLocator()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}