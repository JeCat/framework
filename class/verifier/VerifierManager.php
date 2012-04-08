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
namespace org\jecat\framework\verifier ;

use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Object;

class VerifierManager extends Object
{
	
	public function __construct($bLogic = true){
		$this->setLogic($bLogic);
	}
	
	public function add(IVerifier $aVerifier, $sExceptionWords=null, $callback=null, $arrCallbackArgvs=array()) 
	{
		if( in_array($aVerifier,$this->arrVerifiers) )
		{
			return $this ;
		}
		
		$this->arrVerifiers[] = $aVerifier ;
		
		$nIdx = array_search($aVerifier, $this->arrVerifiers) ;
		$this->arrVerifierOthers[$nIdx] = array(
					$sExceptionWords, $callback, $arrCallbackArgvs
		) ;
		
		// 连续操作
		return $this ;
	}
	
	public function setLogic($bLogic){
		$this->bLogic = (bool)$bLogic;
		return $this ;
	}
	
	public function logic(){
		return $this->bLogic;
	}
	
	public function remove(IVerifier $aVerifier)
	{
		$nIdx = array_search($aVerifier, $this->arrVerifiers) ;
		if( $nIdx===false )
		{
			return ;
		}
		
		unset($this->arrVerifiers[$nIdx]) ;
		unset($this->arrVerifierOthers[$nIdx]) ;
	}
	
	public function clear()
	{
		$this->arrVerifiers = array() ;
		$this->arrVerifierOthers = array() ;
	}
	
	public function count()
	{
		return count($this->arrVerifiers) ;
	}
	
	public function iterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrVerifiers) ;
	}
	
	public function verify($value,$bThrowExcetion=false)
	{		
		$aVerifyFailed = new VerifyFailed(''); 
		foreach($this->arrVerifiers as $nIdx=>$aVerifier)
		{
			try{
				
				$aVerifier->verify( $value, true ) ;
				
			} catch (VerifyFailed $e) {
				
				// 通过回调函数报告错误
				if( $this->arrVerifierOthers[$nIdx][1] )
				{
					call_user_func_array(
							$this->arrVerifierOthers[$nIdx][1]
							, array_merge(
									array( $value, $aVerifier, $e, $this->arrVerifierOthers[$nIdx][0] )
									, Type::toArray($this->arrVerifierOthers[$nIdx][2],Type::toArray_normal)
							)
					) ;
				}
				
				// 抛出异常
				else if($bThrowExcetion)
				{
					if( $this->arrVerifierOthers[$nIdx][0] )
					{
						throw new VerifyFailed($this->arrVerifierOthers[$nIdx][0],null,$e) ;
					}
					else 
					{
						throw $e ;
					}
				}
				return false ;
			}
		}
		
		return true;
	}
	
	private $arrVerifiers = array() ; 
	private $arrVerifierOthers = array() ; 
	private $bLogic = true; //true校验器之间为and关系, false校验器之间为or关系
}

