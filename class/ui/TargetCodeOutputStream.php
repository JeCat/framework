<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\ui ;

use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\io\IOutputStream;

class TargetCodeOutputStream extends OutputStreamBuffer implements IOutputStream
{
	public function start($bIntact=true)
	{
		$this->write("<?php") ;
		$this->write("use org\\jecat\\framework as jc ;\r\n") ;

		$this->write("// 预处理 ---------") ;
		$this->write("if(!empty(\$bPreProcess))") ;
		$this->write("{") ;
		
		$this->aPreprocessStream = new self() ;
		$this->write($this->aPreprocessStream) ;
		
		$this->write("}") ;
		$this->write("if(!empty(\$bRendering))") ;
		$this->write("{") ;
	}
	
	public function finish()
	{
		$this->write("\r\n\r\n}") ;
	}
	
	/**
	 * @return TargetCodeOutputStream
	 */
	public function preprocessStream()
	{
		return $this->aPreprocessStream ;
	}
	
	public function bufferBytes($bClear=true)
	{
		$this->generateOutputCode() ;

		return parent::bufferBytes($bClear) ;
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
	
	private $bUseHereDoc = true ;
}

