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
namespace org\jecat\framework\lang\oop ;

use org\jecat\framework\fs\Folder;

/**
 * @wiki /设计模式/AOP/“影子类”
 * 影子类包，该包下没有真实定义的类，所有类都是某个类的“影子”子类：覆盖所有父类的方法，但是在实现时，只是简单的调用父类方法，并返回父类方法传回的结果。
 * 影子类主要用于 AOP ：不同的对像创建自不同的影子类，而不是相同的父类，这样在AOP时就可以分别对待了。
 * 在 JeCat 框架中， db Model 和 Prototype 已经被设定为“影子类”，系统会根据 Model/Prototype 对应的数据表，建立影子类。
 *  
 */
class ShadowClassPackage extends Package implements \Serializable
{
	public function __construct($sParentClass,$sNamespace,Folder $aFolder=null)
	{
		$this->sParentClass = $sParentClass ;
		
		parent::__construct($sNamespace,$aFolder) ;
	} 
	
	/**
	 * @return js\fs\File
	 */
	public function searchClassEx($sSubFolder,$sShortClassName)
	{
		if( !$sClassFile = parent::searchClassEx($sSubFolder,$sShortClassName) )
		{
			$sClassFile = $this->generateShadowlClass($sShortClassName) ;
		}
		return $sClassFile ;
	}
	
	public function generateShadowlClass($sShortClass)
	{
		if( !$aClassFile = $this->folder()->createChildFile($sShortClass.'.php') )
		{
			throw new Exception("无法自动创建影子类 %s 的类文件：%s",array($sClass,$sClassFilePath)) ;
		}

		$aWriter = $aClassFile->openWriter() ;			

		$sNamespace = $this->ns() ;		
		$aClassRef = new \ReflectionClass($this->sParentClass) ;
	
		$aWriter->write( "<?php " ) ;
		$aWriter->write( "namespace {$sNamespace};\r\n"  ) ;
		$aWriter->write( "class ".basename(str_replace('\\','/',$sNamespace.'\\'.$sShortClass))." extends \\{$this->sParentClass}\r\n"  ) ;
		$aWriter->write( "{\r\n"  ) ;
	
		foreach($aClassRef->getMethods() as $aMethodRef)
		{
			if( $aMethodRef->isFinal() or $aMethodRef->isAbstract() or $aMethodRef->isPrivate() )
			{
				continue ;
			}
	
			$sMethodName = $aMethodRef->getName() ;
	
			$aWriter->write( "\t"  ) ;
			if( $aMethodRef->isStatic() )
			{
				$aWriter->write( 'static '  ) ;
			}
			$aWriter->write( $aMethodRef->isPublic()? 'public ': 'protected '  ) ;
			$aWriter->write( 'function '  ) ;
			if( $aMethodRef->returnsReference() )
			{
				$aWriter->write( ' & '  ) ;
			}
			$aWriter->write( $sMethodName .'( '  ) ;
			$sCallParams = '' ;
	
			// 参数
			foreach($aMethodRef->getParameters() as $aParamRef)
			{
				if($aParamRef->getPosition())
				{
					$aWriter->write( ', '  ) ;
					$sCallParams.= ',' ;
				}
				// 参数类型
				if($aParamClass=$aParamRef->getClass())
				{
					$aWriter->write( '\\'.$aParamClass->getName().' '  ) ;
				}
				else if($aParamRef->isArray())
				{
					$aWriter->write( 'array '  ) ;
				}
				// 引用传递
				if($aParamRef->isPassedByReference())
				{
					$aWriter->write( '&'  ) ;
				}
				// 参数名称/默认值
				$aWriter->write( '$'.$aParamRef->getName()  ) ;
				if($aParamRef->isDefaultValueAvailable())
				{
					$aWriter->write( '=' . var_export($aParamRef->getDefaultValue(),true)  ) ;
				}
				$sCallParams.= '$'.$aParamRef->getName() ;
			}
			$aWriter->write( " )\r\n"  ) ;
			$aWriter->write( "\t{\r\n"  ) ;
			$aWriter->write( "\t\treturn parent::{$sMethodName}({$sCallParams}) ;\r\n"  ) ;
			$aWriter->write( "\t}\r\n"  ) ;
		}
	
		$aWriter->write( "}\r\n"  ) ;
		$aWriter->close() ;
		
		return $aClassFile->path() ;
	}
	
	
	public function serialize()
	{
		$arrData = array(
				'sParentClass' => $this->sParentClass ,
				'sNamespace' => $this->ns() ,
				'sFolderPath' => ($aFolder=$this->folder())? $aFolder->path(): null ,
		) ;
		return serialize($arrData) ;
	}
	
	public function unserialize($serialized)
	{
		$arrData = unserialize($serialized) ;
		
		$this->__construct(
				$arrData['sParentClass']
				, $arrData['sNamespace']
				, $arrData['sFolderPath']? self::findFolder($arrData['sFolderPath'],true): null
		) ;
	}
	
	private $sParentClass ;
}

