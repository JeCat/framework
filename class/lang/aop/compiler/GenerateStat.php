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

namespace org\jecat\framework\lang\aop\compiler ;

use org\jecat\framework\lang\aop\Advice;
use org\jecat\framework\lang\compile\object\TokenPool;
use org\jecat\framework\lang\compile\object\Token;

class GenerateStat 
{
	public function __construct(TokenPool $aTokenPool,Token $aToken=null,array &$arrAdvices=array(),array &$arrAspects=array())
	{
		$this->aTokenPool = $aTokenPool ;
		$this->aExecutePoint = $aToken ;
		$this->arrAdvices =& $arrAdvices ;
		$this->arrAspects =& $arrAspects ;
		
		// 在 TokenPool 中记录 GenerateStat
		$arrAopWeavedStats = $aTokenPool->properties()->get('arrAopWeavedStats')?: array() ;
		$arrAopWeavedStats[] = $this ;
		$aTokenPool->properties()->set('arrAopWeavedStats',$arrAopWeavedStats) ;
	}
	
	public function addAdvice(Advice $aAdvice)
	{
		if( !in_array($aAdvice,$this->arrAdvices,true) )
		{
			$this->arrAdvices[] = $aAdvice ;
		}
	}
	public function addAdvices(\Iterator $aAdviceIter)
	{
		foreach($aAdviceIter as $aAdvice)
		{
			$this->addAdvice($aAdvice) ;
		}
	}
	
	/**
	 * @var	org\jecat\framework\lang\compile\object\Token
	 */
	public $aExecutePoint ;
	
	public $arrAdvices ;
	
	/**
	 * @var	org\jecat\framework\lang\compile\object\TokenPool
	 */
	public $aTokenPool ;
	
	/**
	 * @var	org\jecat\framework\lang\compile\object\FunctionDefine
	 */
	public $aAdvicesDispatchFunc ;
	
	/**
	 * advice 函数定义 的参数表
	 */
	public $sAdviceDefineArgvsLit = '' ;
	/**
	 * advice 函数调用 的参数表
	 */
	public $sAdviceCallArgvsLit = '' ;
	public $sOriginCallArgvsLit = '' ;
	
	public $sOriginJointCode = '' ;

	public $sOriginJointMethodName = '' ;
	
}
