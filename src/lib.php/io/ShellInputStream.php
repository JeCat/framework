<?php
namespace jc\io ;

use jc\util\String;
use jc\lang\Object;
use jc\io\IClosable;
use jc\io\IInputStream;

class ShellInputStream extends InputStream
{
	public function __construct()
	{
		parent::__construct(fopen('php://stdin','r')) ;
	}
}

?>