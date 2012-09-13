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
namespace org\jecat\framework\setting\imp;

use org\jecat\framework\setting\Setting;
use org\jecat\framework\lang\Exception;

class SaeMemcacheSetting extends Setting{
	public function __construct(){
		if( null === self::$aSaeMemcache ){
			if( defined('SAE_TMP_PATH') ){
				self::$aSaeMemcache = memcache_init();
			}else{
				throw new Exception(
					'当前不是sae环境，无法创建`%s`的对象',
					__CLASS__
				);
			}
		}
	}
	
	public function valuePri($sKey,$defaultValue=null){
		$aMemValue = memcache_get( self::$aSaeMemcache , $sKey );
		if( false === $aMemValue ){
			return $defaultValue;
		}else{
			return $aMemValue;
		}
	}
	
	public function setValuePri($sKey,$value){
		memcache_set( self::$aSaeMemcache , $sKey , $value );
	}
	
	public function hasValuePri($sKey){
		$aMemValue = memcache_get( self::$aSaeMemcache , $sKey );
		if( false === $aMemValue ){
			return false;
		}else{
			return true;
		}
	}
	
	public function deleteValuePri($sKey){
		memcache_delete( self::$aSaeMemcache , $sKey );
	}
	
	public function separate($sPath){
		return SeparatedSetting::create(
			$this,
			self::formatKey($sPath)
		);
	}
	
	static private $aSaeMemcache = null ;
}
