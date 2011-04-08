<?php

require_once '../../common.php';
require_once 'lib.php/pattern/composite/CompositeObject.php';
require_once 'PHPUnit/Framework/TestCase.php';

use jc\pattern\composite\CompositeObject ;

/**
 * CompositeObject test case.
 */
class CompositeObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeObject
     */
    private $CompositeObject;
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
        
        $this->CompositeObject = new CompositeObject(/* parameters */);
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        // TODO Auto-generated CompositeObjectTest::tearDown()
        $this->CompositeObject = null;
        parent::tearDown();
    }
    /**
     * Constructs the test case.
     */
    public function __construct ()
    {
        // TODO Auto-generated constructor
    }
    /**
     * Tests CompositeObject::type()
     */
    public function testType ()
    {
		$this->assertEquals(CompositeObject::type(), "jc\\pattern\\composite\\ContainedableObject") ;
    }
    /**
     * Tests CompositeObject->setChildTypes()
     */
    public function testAddChildTypes ()
    {
        $this->CompositeObject->addChildTypes("xxxxx") ;
        $this->CompositeObject->addChildTypes(array("yyyyy","zzzzzz")) ;
        $this->CompositeObject->addChildTypes(array("xxxxx","aaaaaa")) ;
        
        $this->assertAttributeEquals(array("xxxxx","yyyyy","zzzzzz","aaaaaa"),"arrTypes",$this->CompositeObject) ;
    }
    /**
     * Tests CompositeObject->addChild()
     */
    public function testAddChild ()
    {
        // TODO Auto-generated CompositeObjectTest->testAddChild()
        $this->markTestIncomplete("addChild test not implemented");
        $this->CompositeObject->addChild(/* parameters */);
    }
    /**
     * Tests CompositeObject->removeChild()
     */
    public function testRemoveChild ()
    {
        // TODO Auto-generated CompositeObjectTest->testRemoveChild()
        $this->markTestIncomplete("removeChild test not implemented");
        $this->CompositeObject->removeChild(/* parameters */);
    }
    /**
     * Tests CompositeObject->clearChildren()
     */
    public function testClearChildren ()
    {
        // TODO Auto-generated CompositeObjectTest->testClearChildren()
        $this->markTestIncomplete("clearChildren test not implemented");
        $this->CompositeObject->clearChildren(/* parameters */);
    }
    /**
     * Tests CompositeObject->child()
     */
    public function testChild ()
    {
        // TODO Auto-generated CompositeObjectTest->testChild()
        $this->markTestIncomplete("child test not implemented");
        $this->CompositeObject->child(/* parameters */);
    }
    /**
     * Tests CompositeObject->childrenIterator()
     */
    public function testChildrenIterator ()
    {
        // TODO Auto-generated CompositeObjectTest->testChildrenIterator()
        $this->markTestIncomplete(
        "childrenIterator test not implemented");
        $this->CompositeObject->childrenIterator(/* parameters */);
    }
    /**
     * Tests CompositeObject->findChildInFamily()
     */
    public function testFindChildInFamily ()
    {
        // TODO Auto-generated CompositeObjectTest->testFindChildInFamily()
        $this->markTestIncomplete(
        "findChildInFamily test not implemented");
        $this->CompositeObject->findChildInFamily(/* parameters */);
    }
    /**
     * Tests CompositeObject->adopt()
     */
    public function testAdopt ()
    {
        // TODO Auto-generated CompositeObjectTest->testAdopt()
        $this->markTestIncomplete("adopt test not implemented");
        $this->CompositeObject->adopt(/* parameters */);
    }
}

