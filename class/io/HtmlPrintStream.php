<?php
namespace org\jecat\framework\io ;

use org\jecat\framework\lang\Object;

class HtmlPrintStream extends PrintStream implements IOutputStream
{
	public function println($sBytes)
	{
		$this->printstr($sBytes) ;
	}
	
	public function printstr($sBytes,$nLen=null,$bFlush=false)
	{
		if(!$sBytes)
		{
			return  ;
		}
		
		if($nLen===null)
		{
			$this->write('<pre>',null,$bFlush) ;
			$this->write($sBytes,null,$bFlush) ;
			$this->write('</pre>',null,$bFlush) ;
		}
		
		else 
		{
			if(!$nLen)
			{
				return ;
			}
			
			$this->write('<pre>',null,$bFlush) ;
			$this->write($sBytes, $nLen, $bFlush) ;
			$this->write('</pre>',null,$bFlush) ;
		}
	}
}
?>