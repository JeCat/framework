<?php
namespace org\jecat\framework\ui ;

use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\io\IOutputStream;

class TargetCodeOutputStream extends OutputStreamBuffer implements IOutputStream
{
	public function open(IOutputStream $aWriter,$bStartScript=true)
	{
		/*$aWriter = $aCompiledFile->openWriter(false) ;
		if(!$aWriter)
		{
			throw new Exception("保存XHTML模板的编译文件时无法打开文件:%s",$aCompiledFile->url()) ;
		}*/
		
		$this->aCompiledWriter = $aWriter ;
		
		if($bStartScript)
		{
			$this->write("<?php\r\n") ;
		}
	}
	
	public function bufferBytes($bClear=true)
	{
		$this->generateOutputCode() ;

		return parent::bufferBytes($bClear) ;
	}
	
	public function close($bStartScript=true)
	{
		$this->generateOutputCode() ;
		
		if($bStartScript)
		{
			$this->write("?>") ;
		}
		
		$this->aCompiledWriter->write($this->bufferBytes(true)) ;
		$this->aCompiledWriter->close() ;
	}
	
	public function write($sBytes,$nLen=null,$bFlush=false)
	{
		$this->generateOutputCode() ;
		
		parent::write($sBytes) ;
		parent::write("\r\n") ;
	}
	
	
	public function output($sBytes)
	{
		$this->sOutputContents.= $sBytes ; 
	}
	
	protected function generateOutputCode()
	{
		if(!$this->sOutputContents)
		{
			return ;
		}
		
		
		// 收拢纯空白字符串
		if( !trim($this->sOutputContents) )
		{
			$this->sOutputContents = str_replace("\n","\\n",$this->sOutputContents) ;
			$this->sOutputContents = str_replace("\r","\\r",$this->sOutputContents) ;
			$this->sOutputContents = str_replace("\t","\\t",$this->sOutputContents) ;
			
			parent::write("\r\n// output text content -------------\r\n") ;
			parent::write("\$aDevice->write(\"{$this->sOutputContents}\") ;\r\n") ;
			parent::write("// ---------------------------------\r\n") ;
		}
		
		else
		{
			parent::write("\r\n// output text content -------------\r\n") ;
			if($this->bUseHereDoc)
			{
				// 转义 \ 和 $
				$this->sOutputContents = addcslashes($this->sOutputContents, '\\') ;
				$this->sOutputContents = str_replace('$','\\$',$this->sOutputContents) ;
				
				parent::write("\$aDevice->write(<<<OUTPUT\r\n{$this->sOutputContents}\r\nOUTPUT\r\n) ;") ;
			}
			else
			{
				$sOutputContents = addslashes($this->sOutputContents) ;
				parent::write("\$aDevice->write(\"{$sOutputContents}\") ;") ;
			}
			parent::write("") ;
			parent::write("// ---------------------------------\r\n") ;
		}
		$this->sOutputContents = '' ;
	}
	
	public function useHereDoc($bUseHereDoc=true)
	{
		$this->bUseHereDoc = $bUseHereDoc ;
	}
	
	private $sOutputContents ;
	
	private $aCompiledWriter ;
	
	private $bUseHereDoc = true ;
}

?>