<?php
namespace jc\io ;

use jc\lang\Object;

class HtmlPrintStream extends PrintStream implements IOutputStream
{
	public function write($sBytes,$nLen=null,$bFlush=false)
	{		
		PrintStream::write(
				'<pre>'.($nLen===null?$sBytes:substr($sBytes, 0, $nLen)).'</pre>'
				, null, $bFlush
		) ;
	}
}
?>