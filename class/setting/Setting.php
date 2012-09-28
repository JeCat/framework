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
namespace org\jecat\framework\setting;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

/**
 * @wiki /配置
 * 
 * === Key 和 Item ===
 * 系统的配置信息保存在 org\framework\setting\Setting 对像中。
 * 
 * 配置信息存储在[b]配置项（item）[/b]中，每个配置项保存一项数据。数据可以是基本数据类型（字符串、整数、布尔等），也可以是复合数据结构（数组、对象）
 * 
 * [b]配置键（key）[/b]负责维护[b]配置项（item）[/b]，一个key可以提供多个 item 和 多个下级 key。
 * 
 * 系统所需的配置信息依据自身的业务关系和职能分类，分别保存在各个不同的[b]配置键（key）[/b]里，这些[b]配置键[/b]以树状结构存放。
 * 
 * key 和 item 很像文件系统中的目录和文件：每个key可以拥有多个item和下级key；数据是保存在item中的；key负责组织收纳各个item和下级key。
 * 
 * === 访问配置信息 ===
 * 通过 org\framework\setting\Setting 类的单例对象访问所有的 key 和 item 。
 * 访问配置信息时需要像 Setting 对象提供 key路径 和 item名称，Setting对象返回保存在 item 中的数据。
 * 
 */
abstract class Setting extends Object implements ISetting
{	
	public function saveKey($sPath)
	{
		if (! $aKey = $this->key ( $sPath ))
		{
			return;
		}
		$aKey->save ();
	}
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath)
	{
		$aKey = $this->key ( $sPath );
		
		if (! $aKey)
		{
			return new \EmptyIterator ();
		}
		
		return $aKey->keyIterator ();
	}
	
	/**
	 * @return \Iterator 
	 */
	public function itemIterator($sPath)
	{
		$aKey = $this->key ( $sPath );
		
		if (! $aKey)
		{
			return new \EmptyIterator ();
		}
		
		return $aKey->itemIterator ();
	}
	
	public function item($sPath,$sName='*',$defaultValue=null)
	{
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		return $this->value(
			self::formatKey($sPath).'/'.self::formatKey($sName),
			$defaultValue
		);
		if (!$aKey=$this->key($sPath,$defaultValue!==null))
		{
			return null;
		}
		return $aKey->item($sName,$defaultValue) ;
	}
	
	public function setItem($sPath, $sName, $value)
	{
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		return $this->setValue(
			self::formatKey($sPath).'/'.self::formatKey($sName),
			$value
		);
		if (! $aKey = $this->key ( $sPath ))
		{
			if( !$aKey=$this->createKey($sPath) )
			{
				throw new Exception("无法保存配置建：%s",$sPath) ;
			}
		}
		$aKey->setItem ( $sName, $value );
	}
	
	public function hasItem($sPath, $sName)
	{
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		if (! $aKey = $this->key ( $sPath ))
		{
			return null;
		}
		return $aKey->hasItem ( $sName );
	}
	
	public function deleteItem($sPath, $sName)
	{
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		if (! $aKey = $this->key ( $sPath ))
		{
			return;
		}
		$aKey->deleteItem ( $sName );
	}
	
	public function deleteKey($sPath)
	{
		if( $aKey = $this->key($sPath,false) )
		{
			foreach($this->keyIterator($sPath) as $aSubKey)
			{
				$aSubKey->deleteKey() ;
			}
	
			$aKey->deleteKey() ;
		}
	}
	
	static protected function formatKey($sKey){
		// 去掉开头的'/'
		if( substr($sKey,0,1) === '/' ){
			$sKey = substr($sKey,1);
		}
		// 去掉结尾的'/'
		if( substr($sKey,-1,1) === '/' ){
			$sKey = substr($sKey,0,-1);
		}
		return $sKey ;
	}
	
	public function value($sKey,$defaultValue=null){
		$sFindMount = $this->findMount($sKey);
		
		if( null !== $sFindMount ){
			$sInMountPath = substr($sKey,strlen($sFindMount));
			return $this->getMountSettingByPath($sFindMount)->value($sInMountPath,$defaultValue);
		}
		return $this->valuePri($sKey,$defaultValue);
	}
	
	public function setValue($sKey,$value){
		$sFindMount = $this->findMount($sKey);
		
		if( null !== $sFindMount ){
			$sInMountPath = substr($sKey,strlen($sFindMount));
			return $this->getMountSettingByPath($sFindMount)->setValue($sInMountPath,$value);
		}
		return $this->setValuePri($sKey,$value);
	}
	
	public function hasValue($sKey){
		$sFindMount = $this->findMount($sKey);
		
		if( null !== $sFindMount ){
			$sInMountPath = substr($sKey,strlen($sFindMount));
			return $this->getMountSettingByPath($sFindMount)->hasValue($sInMountPath);
		}
		return $this->hasValuePri($sKey);
	}
	
	public function deleteValue($sKey){
		$sFindMount = $this->findMount($sKey);
		
		if( null !== $sFindMount ){
			$sInMountPath = substr($sKey,strlen($sFindMount));
			return $this->getMountSettingByPath($sFindMount)->deleteValue($sInMountPath);
		}
		return $this->deleteValuePri($sKey);
	}
	
	public function keyList($sPrefix){
		$sFindMount = $this->findMount($sPrefix);
		if( null !== $sFindMount ){
			$sInMountPath = substr($sKey,strlen($sFindMount));
			return $this->getMountSettingByPath($sFindMount)->keyList($sInMountPath);
		}
		return $this->keyListPri($sPrefix);
	}
	
	/**
	 * value() , setValue() , hasValue() , deleteValue() 首先处理挂载。
	 * 在将挂载全部处理完毕后，再调用
	 * valuePri() , setValuePri() , hasValuePri() , deleteValuePri()
	 * 子类在实现这四个函数时，不需要再考虑挂载问题。
	 **/
	abstract protected function valuePri($sKey,$defaultValue);
	abstract protected function setValuePri($sKey,$value);
	abstract protected function hasValuePri($sKey);
	abstract protected function deleteValuePri($sKey);
	
	public function mount(ISetting $aSubSetting , $sMountPath){
		if( isset( $this->arrMountMap[ $sMountPath ] ) ){
			throw new Exception(
				'Setting无法重复挂载：路径%s已经挂载了一个Setting',
				$sMountPath
			);
		}else{
			$this->arrMountMap[ $sMountPath ] = $aSubSetting ;
		}
	}
	
	private function getMountSettingByPath($sMountPath){
		return $this->arrMountMap[ $sMountPath ] ?:null;
	}
	
	/**
	 * @return string or null
	 * 目前挂载的Setting对象不超过10个，因此直接循环找一遍就行。
	 * 如果有一天，挂载超过100个甚至更多，可以考虑使用Trie树算法来提高效率。
	 */
	private function findMount($sMountPath){
		foreach($this->arrMountMap as $sPath => $aSubSetting){
			$nPathLength = strlen( $sPath );
			if( substr($sMountPath,0,$nPathLength) === $sPath ){
				return $sPath;
			}
		}
		return null;
	}
	
	private $arrMountMap = array();
}
