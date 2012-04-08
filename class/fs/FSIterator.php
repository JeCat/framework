<?php
namespace org\jecat\framework\fs ;

abstract class FSIterator implements \Iterator{
	// 内容相关设定
	const CONTAIN_DOT = 0x01 ; // 包含'.'和'..'两个目录
	const CONTAIN_FILE = 0x02 ; // 包含文件
	const CONTAIN_FOLDER = 0x04 ; // 包含目录
	
	// 返回值相关设定
	const RETURN_FSO = 0x08 ; // 返回 FSO 对象，否则，返回字符串
	const RETURN_ABSOLUTE_PATH = 0x10 ; // 返回绝对路径，否则，返回相对路径
	
	// 递归相关设定
	const RECURSIVE_SEARCH = 0x20 ; // 递归搜索，否则，只搜索当前目录下
	const RECURSIVE_BREADTH_FIRST = 0x40 ; // 按广度优先进行搜索，否则，按深度优先进行搜索
	
	// 常用组合
	const DIR_ALL = 0x05 ; // CONTAIN_DOT | CONTAIN_FOLDER
	const FILE_AND_FOLDER = 0x06 ; // CONTAIN_FILE | CONTAIN_FOLDER
	const FILE = 0x02 ; // CONTAIN_FILE
	const FOLDER = 0x04 ; // CONTAIN_FOLDER
	
	// 默认值
	const FLAG_DEFAULT = 0x36 ; // CONTAIN_FILE | CONTAIN_FOLDER | RETURN_ABSOLUTE_PATH | RECURSIVE_SEARCH
	
	public function __construct(Folder $aParentFolder , $nFlags){
		$this->nFlags=$nFlags;
		$this->aParentFolder = $aParentFolder;
	}
	
	/**
	 * @return FSO
	 */
	abstract public function getFSO();
	
	/**
	 * @return string
	 */
	abstract public function absolutePath();
	
	/**
	 * @return string
	 */
	abstract public function relativePath();
	
	/**
	 * @return boolean
	 */
	abstract public function isFolder();
	
	/**
	 * @return boolean
	 */
	abstract public function isFile();
	
	protected $nFlags = self::FLAG_DEFAULT ;
	protected $aParentFolder = null ;
}
