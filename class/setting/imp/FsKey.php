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
namespace org\jecat\framework\setting\imp ;

use org\jecat\framework\fs\Folder;
use org\jecat\framework\setting\Key;

class FsKey extends Key implements \Serializable
{
	const itemFilename = 'items.php' ;
	
	public function __construct(Folder $aFolder)
	{
		$this->aKeyFolder = $aFolder ;
		$this->readItemFile();
	}
	
	static public function createKey(Folder $aFolder)
	{
		$sFlyweightKey = $aFolder->path();
		if( !$aKey=FsKey::flyweight($sFlyweightKey,false) )
		{
			$aKey = new FsKey($aFolder) ;
			FsKey::setFlyweight($aKey,$sFlyweightKey) ;
		}
		
		return $aKey ;
	}
	
	public function name()
	{
		return $this->folder()->name() ;
	}
	
	public function keyIterator()
	{
		return new FsKeyIterator( $this ) ;
	}
	
	public function save()
	{
		if( $aItemFile = $this->aKeyFolder->findFile(self::itemFilename,Folder::FIND_AUTO_CREATE)){
			$aWriter = $aItemFile->openWriter() ;
			$aWriter->write(
				"<?php\r\nreturn ".var_export($this->arrItems,true)." ;"
			) ;
			$aWriter->close() ;
		
			$this->bDataChanged = false ;
		}else{
			throw new Exception('create file failed : %s',$this->aKeyFolder->path().'/'.self::itemFilename);
		}
	}

	public function serialize ()
	{		
		return $this->folder()->path() ;
	}

	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$this->aKeyFolder = Folder::singleton()->findFolder($serialized,Folder::FIND_AUTO_CREATE) ;
	}
	
	/**
	 * 这不是 IKey 接口中的方法
	 * @return Folder
	 */
	public function folder()
	{
		return $this->aKeyFolder ;
	}
	
	public function deleteKey()
	{
		$this->arrItems = array() ;
		
		if( $aFolder = $this->folder() )
		{
			FsKey::setFlyweight(null,$aFolder->path()) ;
			
			$aFolder->delete(true,true) ;
			$this->bDataChanged = false ;
		}
	}
	
	/**
	 * 这不是 IKey 接口中的方法
	 */
	private function readItemFile(){
		if( $aItemFile = $this->folder()->findFile(self::itemFilename)){
			$this->arrItems = $aItemFile->includeFile(false,false) ;
			if(!is_array($this->arrItems))
			{
				$this->arrItems = array() ;
				$this->bDataChanged = true ;
			}
		}
	}
	
	/**
	 * @var org\jecat\framework\fs\Folder
	 */
	private $aKeyFolder ;
}


