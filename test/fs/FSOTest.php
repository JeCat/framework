<?php
require_once '../common.php';

require_once 'lib.php/fs/FSO.php';
require_once 'PHPUnit/Framework/TestCase.php';

use jc\fs\FSO ;

/**
 * FSO test case.
 */
class FSOTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FSO
     */
    private $FSO;
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
        // TODO Auto-generated FSOTest::setUp()
        $this->FSO = new FSO(/* parameters */);
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        // TODO Auto-generated FSOTest::tearDown()
        $this->FSO = null;
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
     * Tests FSO->__construct()
     */
    public function test__construct ()
    {
        // TODO Auto-generated FSOTest->test__construct()
        $this->markTestIncomplete("__construct test not implemented");
        $this->FSO->__construct(/* parameters */);
    }
    /**
     * Tests FSO->path()
     */
    public function testPath ()
    {
        // TODO Auto-generated FSOTest->testPath()
        $this->markTestIncomplete("path test not implemented");
        $this->FSO->path(/* parameters */);
    }
    /**
     * Tests FSO->name()
     */
    public function testName ()
    {
        // TODO Auto-generated FSOTest->testName()
        $this->markTestIncomplete("name test not implemented");
        $this->FSO->name(/* parameters */);
    }
    /**
     * Tests FSO->parentPath()
     */
    public function testParentPath ()
    {
        // TODO Auto-generated FSOTest->testParentPath()
        $this->markTestIncomplete("parentPath test not implemented");
        $this->FSO->parentPath(/* parameters */);
    }
    /**
     * Tests FSO->exists()
     */
    public function testExists ()
    {
        // TODO Auto-generated FSOTest->testExists()
        $this->markTestIncomplete("exists test not implemented");
        $this->FSO->exists(/* parameters */);
    }
    /**
     * Tests FSO->lastModified()
     */
    public function testLastModified ()
    {
        // TODO Auto-generated FSOTest->testLastModified()
        $this->markTestIncomplete("lastModified test not implemented");
        $this->FSO->lastModified(/* parameters */);
    }
    /**
     * Tests FSO->canRead()
     */
    public function testCanRead ()
    {
        // TODO Auto-generated FSOTest->testCanRead()
        $this->markTestIncomplete("canRead test not implemented");
        $this->FSO->canRead(/* parameters */);
    }
    /**
     * Tests FSO->canWrite()
     */
    public function testCanWrite ()
    {
        // TODO Auto-generated FSOTest->testCanWrite()
        $this->markTestIncomplete("canWrite test not implemented");
        $this->FSO->canWrite(/* parameters */);
    }
    /**
     * Tests FSO->canExecute()
     */
    public function testCanExecute ()
    {
        // TODO Auto-generated FSOTest->testCanExecute()
        $this->markTestIncomplete("canExecute test not implemented");
        $this->FSO->canExecute(/* parameters */);
    }
    /**
     * Tests FSO->perms()
     */
    public function testPerms ()
    {
        // TODO Auto-generated FSOTest->testPerms()
        $this->markTestIncomplete("perms test not implemented");
        $this->FSO->perms(/* parameters */);
    }
    /**
     * Tests FSO->setPerms()
     */
    public function testSetPerms ()
    {
        // TODO Auto-generated FSOTest->testSetPerms()
        $this->markTestIncomplete("setPerms test not implemented");
        $this->FSO->setPerms(/* parameters */);
    }
    /**
     * Tests FSO->delete()
     */
    public function testDelete ()
    {
        // TODO Auto-generated FSOTest->testDelete()
        $this->markTestIncomplete("delete test not implemented");
        $this->FSO->delete(/* parameters */);
    }
    /**
     * Tests FSO::formatPath()
     */
    public function testFormatPath ()
    {
		$this->assertEquals( FSO::formatPath("C:\\xxx\\ddd\\.\\..\\xx"), "C:\\xxx/xx" ) ;
		$this->assertEquals( FSO::formatPath("C:\\..\\xxx\\ddd\\.\\..\\xx"), "C:\\xxx/xx" ) ;
		$this->assertEquals( FSO::formatPath("C:\\..\\xxx\\ddd\\.\\..\\xx\\.\\"), "C:\\xxx/xx/" ) ;
		$this->assertEquals( FSO::formatPath("C:\\..\\xxx\\ddd\\.\\..\\xx\\..\\"), "C:\\xxx/" ) ;
		$this->assertEquals( FSO::formatPath("C:\\..\\xxx\\ddd\\.\\..\\xx\\.."), "C:\\xxx" ) ;
		
		$this->assertEquals( FSO::formatPath("/../xxx/ddd/./../xx"), "/xxx/xx" ) ;
		$this->assertEquals( FSO::formatPath("/../xxx/ddd/./../xx/./"), "/xxx/xx/" ) ;
		$this->assertEquals( FSO::formatPath("/../xxx/ddd/./../xx/../"), "/xxx/" ) ;
		$this->assertEquals( FSO::formatPath("/../xxx/ddd/./../xx/.."), "/xxx" ) ;
		$this->assertEquals( FSO::formatPath("/aaa/../xxx/ddd/./../xx/.."), "/xxx" ) ;
		
		$this->assertEquals( FSO::formatPath("/aaa/../xxx/ddd/./..//xx//.."), "/xxx" ) ;
		$this->assertEquals( FSO::formatPath("/aaa/..\\\\xxx/ddd/./..//xx//.."), "/xxx" ) ;
    }
}

