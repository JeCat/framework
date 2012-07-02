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
//  正在使用的这个版本是：0.8
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

use org\jecat\framework\ui\xhtml\Expression;

use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\io\IOutputStream;

class TargetCodeOutputStream extends \org\jecat\framework\lang\Object
{
	public function __construct($sTemplateSignature,$bIntact)
	{
		$this->sTemplateSignature = $sTemplateSignature ;
		$this->bIntact = $bIntact ;
	}
			
	public function bufferBytes($bClear=true)
	{
		return $this->generateCompiled() ;
	}
	

	public function output($bytes,$sSubTempName=null)
	{
		$sSubTempName = $sSubTempName?:$this->sDefaultSubTemplate ;
		
		if(!isset($this->arrOutputBuffers[$sSubTempName]))
		{
			$this->arrOutputBuffers[$sSubTempName] = '' ;
		}
		$this->arrOutputBuffers[$sSubTempName].= $bytes ;
	}
	
	public function putCode($source,$sSubTempName=null,$bNewLine=true)
	{
		$sSubTempName = $sSubTempName?:$this->sDefaultSubTemplate ;
		
		$this->generateOutputCode($sSubTempName) ;

		if($source instanceof Expression)
		{
			$source->generate($this,$sSubTempName) ;
		}
		else
		{
			$this->arrCompileds[$sSubTempName].= $source ;
			if($bNewLine)
			{
				$this->arrCompileds[$sSubTempName].= "\r\n" ;
			}
		}
	}
	
	public function write($sBytes,$nLen=null,$bFlush=false)
	{
		trigger_error('正在访问一个过时的方法：TargetCodeOutputStream::write() 方法已经改名为: putCode()',E_USER_DEPRECATED ) ;
		$this->putCode($sBytes) ;
	}
	
	protected function generateOutputCode($sSubTempName=null)
	{
		$sSubTempName = $sSubTempName?:$this->sDefaultSubTemplate ;
		
		if(!isset($this->arrCompileds[$sSubTempName]))
		{
			$this->arrCompileds[$sSubTempName] = '' ;
		}
		
		if(empty($this->arrOutputBuffers[$sSubTempName]))
		{
			return ;
		}

		$this->arrCompileds[$sSubTempName].= "\r\n// output text content -------------\r\n" ;
		
		// 只有空白字符
		if( !trim($this->arrOutputBuffers[$sSubTempName]) )
		{
			// 收拢纯空白字符串
			$this->arrOutputBuffers[$sSubTempName] = str_replace("\n","\\n",$this->arrOutputBuffers[$sSubTempName]) ;
			$this->arrOutputBuffers[$sSubTempName] = str_replace("\r","\\r",$this->arrOutputBuffers[$sSubTempName]) ;
			$this->arrOutputBuffers[$sSubTempName] = str_replace("\t","\\t",$this->arrOutputBuffers[$sSubTempName]) ;
			
			$this->arrCompileds[$sSubTempName].= "\$aDevice->write(\"{$this->arrOutputBuffers[$sSubTempName]}\") ;\r\n" ;
		}
		
		// 正常html输出
		else
		{
			if($this->bUseHereDoc)
			{
				// 转义 \ 和 $
				$this->arrOutputBuffers[$sSubTempName] = addcslashes($this->arrOutputBuffers[$sSubTempName], '\\') ;
				$this->arrOutputBuffers[$sSubTempName] = str_replace('$','\\$',$this->arrOutputBuffers[$sSubTempName]) ;
				
				$this->arrCompileds[$sSubTempName].= "\$aDevice->write(<<<OUTPUT\r\n{$this->arrOutputBuffers[$sSubTempName]}\r\nOUTPUT\r\n) ;" ;
			}
			else
			{
				$sOutputBuffer = addslashes($this->arrOutputBuffers[$sSubTempName]) ;
				$this->arrCompileds[$sSubTempName].= "\$aDevice->write(\"{$sOutputBuffer}\") ;" ;
			}
		}
		
		$this->arrCompileds[$sSubTempName].= "// ---------------------------------\r\n" ;
		
		$this->arrOutputBuffers[$sSubTempName] = '' ;
	}
	
	public function generateCompiled()
	{
		if( $this->bIntact )
		{
			$sTemplateCompiled = "<?php\r\n" ;
			$sTemplateCompiled.= "use org\\jecat\\framework as jc ;\r\n" ;
			
			$sTemplateCompiled.= "\r\n" ;
			$sTemplateCompiled.= "define('{$this->sTemplateSignature}',__FILE__) ;\r\n" ;
		}
		else
		{
			$sTemplateCompiled = "" ;
		}

		foreach(array_keys($this->arrOutputBuffers) as $sSubTempName)
		{
			$this->generateOutputCode($sSubTempName) ;
		}
		
		foreach($this->arrCompileds as $sSubTemplate=>&$sCompiled)
		{
			$this->generateOutputCode($sSubTemplate) ;
			
			$sTemplateCompiled.= "function _{$this->sTemplateSignature}_{$sSubTemplate}(\\org\\jecat\\framework\\ui\\UI \$aUI,\\org\\jecat\\framework\\util\\IHashTable \$aVariables,\\org\\jecat\\framework\\io\\IOutputStream \$aDevice)\r\n" ;
			$sTemplateCompiled.= "{\r\n" ;
			
			// variables declare
			if(!empty($this->arrDeclareVariables[$sSubTemplate]))
			{
				$sTemplateCompiled.= "	// declare variables\r\n" ;
				foreach($this->arrDeclareVariables[$sSubTemplate] as $sVarName=>&$sInitExpression)
				{
					if($sInitExpression!==null)
					{
						$sTemplateCompiled.= "	\${$sVarName} = {$sInitExpression} ;\r\n" ;
					}
					else
					{
						$sTemplateCompiled.= "	\${$sVarName} ;\r\n" ;
					}
				}
				$sTemplateCompiled.= "\r\n" ;
			}
			
			
			$sTemplateCompiled.= $sCompiled ;
			$sTemplateCompiled.= "}\r\n" ;
		}
		
		return $sTemplateCompiled ;
	}
	
	public function useHereDoc($bUseHereDoc=true)
	{
		$this->bUseHereDoc = $bUseHereDoc ;
	}

	public function declareVarible($sVarName,$sInitExpression=null,$sSubTempName=null)
	{
		$sSubTempName = $sSubTempName?:$this->sDefaultSubTemplate ;
		
		$this->arrDeclareVariables[$sSubTempName][$sVarName] = $sInitExpression ;
	}
	
	public function hasDeclared($sVarName,$sSubTempName=null)
	{
		$sSubTempName = $sSubTempName?:$this->sDefaultSubTemplate ;
		
		return array_key_exists($sVarName,$this->arrDeclareVariables) ;
	}
	
	public function setDefaultSubTemplate($sDefaultSubTemplate)
	{
		$this->sDefaultSubTemplate = $sDefaultSubTemplate ;
	}
	public function defaultSubTemplate()
	{
		return $this->sDefaultSubTemplate ;
	}
	public function templateSignature()
	{
		return $this->sTemplateSignature ;
	}
	private $arrCompileds = array() ;
	private $arrOutputBuffers = array() ;
	private $arrDeclareVariables = array() ;
	
	private $sTemplateSignature ;
	private $bIntact = true ;

	private $sDefaultSubTemplate = 'render' ;
	
	private $bUseHereDoc = true ;
}

