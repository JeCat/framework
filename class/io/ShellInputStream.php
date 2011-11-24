<?php
namespace org\jecat\framework\io ;

use org\jecat\framework\util\String;
use org\jecat\framework\lang\Object;
use org\jecat\framework\io\IClosable;
use org\jecat\framework\io\IInputStream;

class ShellInputStream extends InputStream
{
	public function __construct()
	{
		parent::__construct(fopen('php://stdin','r')) ;
	}
}

?>