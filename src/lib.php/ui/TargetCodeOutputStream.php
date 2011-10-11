<?php
namespace jc\ui ;

use jc\lang\Exception;
use jc\fs\IFile;
use jc\io\IOutputStream;

class TargetCodeOutputStream implements IOutputStream
{
	public function open(IOutputStream $aWriter)
	{
		/*$aWriter = $aCompiledFile->openWriter(false) ;
		if(!$aWriter)
		{
			throw new Exception("保存XHTML模板的编译文件时无法打开文件:%s",$aCompiledFile->url()) ;
		}*/
		
		$this->aCompiledWriter = $aWriter ;
		
		$this->write("<?php\r\n") ;
	}
	
	public function close()
	{
		$this->generateOutputCode() ;
		
		$this->write("?>") ;
		$this->aCompiledWriter->close() ;
	}
	
	public function write($sBytes,$nLen=null,$bFlush=false)
	{
		$this->generateOutputCode() ;
		
		$this->aCompiledWriter->write($sBytes) ;
		$this->aCompiledWriter->write("\r\n") ;
	}
	
	public function bufferBytes()
	{}
	
	public function clean()
	{}
	
	public function flush()
	{}
	
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
		
		// 转义 \ 和 $
		$this->sOutputContents = addcslashes($this->sOutputContents, '\\') ;
		$this->sOutputContents = str_replace('$','\\$',$this->sOutputContents) ;
		
		$this->aCompiledWriter->write("\$aDevice->write(<<<OUTPUT\r\n{$this->sOutputContents}\r\nOUTPUT\r\n) ;\r\n") ;
		$this->sOutputContents = '' ;
	}
	
	private $sOutputContents ;
	
	private $aCompiledWriter ;
}

?>