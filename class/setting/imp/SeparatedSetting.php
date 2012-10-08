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

use org\jecat\framework\setting\ISetting;
use org\jecat\framework\setting\Setting;

class SeparatedSetting implements ISetting{
	public function __construct(Setting $aRealSetting,$sSeparatePath){
		$this->aRealSetting = $aRealSetting;
		$this->sSeparatePath = Setting::formatKey($sSeparatePath);
	}
	
	static public function create(Setting $aRealSetting,$sSeparatePath){
		return new self(
			$aRealSetting,$sSeparatePath
		);
	}
	
	public function value($sKey,$defaultValue=null){
		return $this->aRealSetting->value(
			$this->sSeparatePath.'/'.Setting::formatKey($sKey),
			$defaultValue
		);
	}
	
	public function setValue($sKey,$value){
		return $this->aRealSetting->setValue(
			$this->sSeparatePath.'/'.Setting::formatKey($sKey),
			$value
		);
	}
	
	public function hasValue($sKey){
		return $this->aRealSetting->hasValue(
			$this->sSeparatePath.'/'.Setting::formatKey($sKey)
		);
	}
	
	public function deleteValue($sKey){
		return $this->aRealSetting->deleteValue(
			$this->sSeparatePath.'/'.Setting::formatKey($sKey)
		);
	}
	
	public function keyList($sKey){
		return $this->aRealSetting->keyList(
			$this->sSeparatePath.'/'.Setting::formatKey($sKey)
		);
	}
	
	public function separate($sPath){
		return self::create(
			$this->aRealSetting,
			$this->sSeparatePath.'/'.Setting::formatKey($sPath)
		);
	}
	
	public function deleteKey($sKey){
		return $this->aRealSetting->deleteKey(
			$this->sSeparatePath.'/'.Setting::formatKey($sKey)
		);
	}
	
	public function key($sKey,$bAutoCreate=false){
		return $this->aRealSetting->key(
			$this->sSeparatePath.'/'.Setting::formatKey($sKey),
			$bAutoCreate
		);
	}
	
	public function item($sPath,$sName='*',$defaultValue=null)
	{
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		return $this->value(
			Setting::formatKey($sPath).'/'.Setting::formatKey($sName),
			$defaultValue
		);
	}
	
	public function setItem($sPath, $sName, $value)
	{
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		return $this->setValue(
			Setting::formatKey($sPath).'/'.Setting::formatKey($sName),
			$value
		);
	}
	
	public function mount(ISetting $aSubSetting , $sMountPath){
		return $this->aRealSetting->mount(
			$aSubSetting ,
			$this->sSeparatePath.'/'.Setting::formatKey($sMountPath)
		);
	}
	
	private $sSeparatePath = '';
}
