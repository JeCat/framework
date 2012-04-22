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
namespace org\jecat\framework\setting;

use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\fs\File;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\fs\FSIterator;
use org\jecat\framework\fs\LocalFolderIterator;
use org\jecat\framework\lang\Object;
use org\jecat\framework\fs\FSO;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\setting\Setting;

class FsSetting extends Object implements ISetting, \Serializable
{
	/**
	 * 
	 * @param Folder $aRootFolder
	 */
	public function __construct($sSettingFile)
	{
		$this->sSettingFile = $sSettingFile ;
	}
	
	public function __destruct()
	{
		if( $this->bDataChanged )
		{
			$this->save() ;
		}
	}
	
	static public function createFromPath($sFolderPath) 
	{
		return new self( Folder::createFolder($sFolderPath) ) ;
	} 

	/**
	 * @return bool
	 */
	public function createKey($sPath)
	{
		$this->key($sPath) ;
		return true ;
	}

	/**
	 * @return bool
	 */
	public function hasKey($sPath)
	{
		return $this->key($sPath,false)!==null ;
	}
	
	/**
	 * @return \Iterator 
	 */
	public function keyIterator($sPath=null)
	{
		if($sPath===null)
		{
			$arrKey =& $this->arrRootKey ;
		}
		else
		{
			$arrKey =& $this->key($sPath,false) ;
		}
		return empty($arrKey['__children'])?
				new \EmptyIterator() :
				new \ArrayIterator(array_keys($arrKey['__children'])) ;
	}
	
	/**
	 * 删除一个键
	 * @param string $sPath 键路径
	 * @return boolen 删除成功返回true，失败返回false
	 */
	public function deleteKey($sPath)
	{
		$sPath = trim($sPath,'/ ') ;
		if(!$sPath)
		{
			$this->arrRootKey['__items'] = array() ;
			$this->arrRootKey['__children'] = array() ;
		}
		
		else
		{
			$arrKey =& $this->key($sPath,false) ;
			if($arrKey!==null)
			{
				$arrKey = null ;
			}
		}
		
		return true ;
	}
	

	/**
	 * 获得项的值
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @param mixed $defaultValue 默认值 ,如果项不存在就取默认值,并且以默认值新建项
	 */
	public function item($sPath,$sName='*',$defaultValue=null)
	{
		$arrKey =& $this->key($sPath,true,$bIsNewKey) ;
		if( $bIsNewKey or empty($arrKey['__items']) or !key_exists($sName,$arrKey['__items']) )
		{
			$arrKey['__items'][$sName] = $defaultValue ;
			$this->bDataChanged = true ;
		}
		return $arrKey['__items'][$sName] ;
	}
	
	/**
	 * 设置项的值
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @param ISetting
	 */
	public function setItem($sPath,$sName,$value)
	{
		$bIsNewKey = false ;
		$arrKey =& $this->key($sPath,true,$bIsNewKey) ;
		
		if( $bIsNewKey or !isset($arrKey['__items'][$sName]) or $arrKey['__items'][$sName]!==$value )
		{
			$this->bDataChanged = true ;
		}

		$arrKey['__items'][$sName] = $value ;
		
		return $this ;
	}
	
	/**
	 * 检查项是否存在
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @return boolen 如果项存在就返回true,如果不存在返回false
	 */
	public function hasItem($sPath,$sName)
	{
		return $arrKey=&$this->key($sPath,false) and key_exists($sName,$arrKey['__items']) ;
	}
	
	/**
	 * 删除项
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 */
	public function deleteItem($sPath,$sName)
	{
		$arrKey =& $this->key($sPath,false) ;
		if($arrKey!==null)
		{
			return $arrKey['__items'][$sName] ;
		}
	}
	
	/**
	 * 获得项的名字迭代器
	 * @param string $sPath 键路径
	 * @return \Iterator
	 */
	public function itemIterator($sPath)
	{
		$arrKey =& $this->key($sPath,false) ;
		if( $arrKey!==null and isset($arrKey['__items']) )
		{
			return new \ArrayIterator(array_keys($arrKey['__items'])) ;
		}
		
		return new \EmptyIterator() ;
	}
	
	
	public function serialize ()
	{
		// 只保存属性 sSettingFile
		return $this->sSettingFile ;
	}

	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$this->sSettingFile =& $serialized ;
	}

	
	private function & key($sKeyPath,$bAutoCreate=true,& $bIsNewKey=false)
	{
		if(!$this->bDataLoaded)
		{
			$this->load() ;
		}
		
		$bIsNewKey = false ;
		
		$arrKey =& $this->arrRootKey ;
		foreach(explode('/',$sKeyPath) as $sKeyName)
		{
			if( empty($sKeyName) )
			{
				continue ;
			}
			if( !isset($arrKey['__children'][$sKeyName]) )
			{
				if($bAutoCreate)
				{
					$arrKey['__children'][$sKeyName] = array() ;
					$bIsNewKey = true ;
				}
				else
				{
					return self::$null ;
				}
			}
			$arrKey =& $arrKey['__children'][$sKeyName] ;
		}
		return $arrKey ;
	}
	
	private function load()
	{
		if(is_file($this->sSettingFile))
		{
			$arrRootKey = include $this->sSettingFile ;
			
			if(!is_array($arrRootKey))
			{
				throw new Exception("保存在文件中的不是有效的 FsSetting 数据：%s",$this->sSettingFile) ;
			}
			
			$this->arrRootKey =& $arrRootKey ;
		}
		
		$this->bDataLoaded = true ;
	}
	
	public function save()
	{
		$aWriter = File::createInstance($this->sSettingFile)->openWriter() ;
		$aWriter->write("<?php \r\n") ;
		$aWriter->write("// writen by JeCat class ".__CLASS__."\r\n") ;
		
		$aWriter->write("return ") ;
		$this->saveKey($this->arrRootKey,$aWriter,0,'') ;
		$aWriter->write(" ;") ;
		
		$aWriter->close() ;
	}
	
	private function saveKey(& $arrKey,IOutputStream $aWriter,$nIndent,$sKeyPath)
	{
		if( empty($arrKey['__items']) and empty($arrKey['__children']) )
		{
			return ;
		}
		
		$sIndent = str_repeat("\t",$nIndent) ;
		
		$aWriter->write("array (\r\n") ;
		
		
		// 写入 items
		if( !empty($arrKey['__items']) )
		{
			$aWriter->write("\r\n") ;
			$aWriter->write("{$sIndent}\t'__items' => array(\r\n") ;
			foreach($arrKey['__items'] as $itemName=>&$item)
			{
				$sNameSrc = var_export($itemName,1) ;
				
				$sItemSrc = var_export($item,1) ;
				$sItemSrc = str_replace("\r\n","\n",$sItemSrc) ;
				$sItemSrc = str_replace("\r","\n",$sItemSrc) ;
				$arrItemSrcLines = explode("\n",$sItemSrc) ;
				foreach($arrItemSrcLines as $nIdx=>&$sLine)
				{
					if($nIdx>0)
					{
						$sLine = $sIndent."\t\t\t" .$sLine ;
					}
				}
				$sItemSrc = implode("\r\n",$arrItemSrcLines) ;
				
				$aWriter->write("{$sIndent}\t\t{$sNameSrc} => {$sItemSrc} ,\r\n") ;
			}
			$aWriter->write("{$sIndent}\t) ,\r\n") ;
		}

		// 写入下级 key
		if( !empty($arrKey['__children']) )
		{
			$aWriter->write("\r\n") ;
			$aWriter->write("{$sIndent}\t'__children' => array(\r\n") ;
			foreach($arrKey['__children'] as $keyName=>&$arrSubKey)
			{
				$sKeySrc = var_export($keyName,1) ;
				$aWriter->write("\r\n") ;
				$aWriter->write("{$sIndent}\t\t// key : ".($sKeyPath.'/'.$keyName)." ---------\r\n") ;
				$aWriter->write("{$sIndent}\t\t{$sKeySrc} => ") ;
				$this->saveKey($arrSubKey, $aWriter, $nIndent+2, $sKeyPath.'/'.$keyName) ;
				$aWriter->write(",\r\n") ;
			}
			$aWriter->write("{$sIndent}\t) ,\r\n") ;
		}

		$aWriter->write("{$sIndent})") ;
	}
	

	/**
	 * @param string $sKeyPath 键路径
	 * @return ISetting
	 */
	public function separate($sKeyPath)
	{
		return new self( dirname($this->sSettingFile) . '/' . trim($sKeyPath,'/').'.php' ) ;
	}
	
	private $sSettingFile ;
	private $arrRootKey ;
	private $bDataChanged = false ;
	private $bDataLoaded = false ;
	
	static private $null = null ;
}
