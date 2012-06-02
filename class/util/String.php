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
namespace org\jecat\framework\util ;

use org\jecat\framework\fs\File;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;
use org\jecat\framework\util\match\RegExp;

class String extends Object
{
	public function __construct($sText='')
	{
		$this->sText = $sText ;
	} 
	
	public function __toString()
	{
		return $this->sText ;
	}

	public function set($sText)
	{
		$this->sText = $sText ;
	}
	
	public function byte($nIdx)
	{
		return strlen($this->sText)>$nIdx? substr($this->sText,$nIdx,1): null ;
	}
	
	public function append($sText,$bTail=true)
	{
		if($bTail)
		{
			$this->sText.= $sText ;
		}
		else 
		{
			$this->sText = $sText.$this->sText ;
		}
	}
	
	public function insert($sText,$nPos=-1)
	{
		$this->sText = substr_replace($this->sText,$sText,$nPos,0) ;
	}

	/**
	 * 计算字符长度
	 * 
	 * @param $bByChar bool 为true按字符计算(中文占3个字节),为false按字节计算,默认false
	 * @return Inter
	 */
	public function length($bByChar = false)
	{
		if(!$bByChar){
			return strlen($this->sText) ;
		}else{
			$nBitLength=strlen($this->sText);
			$nLength = 0;
			for($i=0;$i<$nBitLength;$i++){
				if(ord($this->sText[$i])>127){
					$i+=2;
				}
				$nLength++;
			}
			return $nLength ;
		}
	}
	
	public function clear()
	{
		$this->sText = '' ;
	}
	
	public function loadFile(File $aFile)
	{
		$aReader = $aFile->openReader() ;
		$nBytes = $aReader->readInString($this) ;
		$aReader->close() ;
		
		return $nBytes ;
	}
	
	/**
	 * @return String
	 */
	static public function createFromFile(File $aFile)
	{
		$aString = new self() ;
		$aString->loadFile($aFile) ;
		return $aString ;
	} 
	
	public function match($sRegExp,$nLimit=-1)
	{
		$aRegExp = new RegExp($sRegExp) ;
		$aRegExp->match($this->sText,$nLimit) ;
	}
	
	/**
	 * 使用一个PHP原生函数处理 对象中的字符串资源，并返回结果。
	 * 这用这个对象方法，你可以不必通过 GetSouce() 取回全部的字符串资源。
	 *
	 * @access	public
	 * @return	mixed
	 */
	public function __call($sAccessFuncName,$arrArgs)
	{
		$sFuncName = strtolower($sAccessFuncName) ;
		$sFuncName = str_replace('_','',$sFuncName) ;
		
		if(!isset(self::$arrPHPOriFunctions[$sFuncName]))
		{
			$sFuncName = 'str'.$sFuncName ;
			if(!isset(self::$arrPHPOriFunctions[$sFuncName]))
			{
				throw new Exception('正在访问一个未知的字符串处理函数：%s',array($sAccessFuncName)) ;
			}
		}
		
		$arrFunc = self::$arrPHPOriFunctions[$sFuncName] ;

		// 整理参数表
		array_splice($arrArgs,$arrFunc['pos'],0,array($this->sText)) ;
		
		if( empty($arrFunc['modify']) )
		{
			return call_user_func_array($arrFunc['function'],$arrArgs) ;
		}
		else 
		{
			return $this->sText = call_user_func_array($arrFunc['function'],$arrArgs) ;
		}
	}
	
	private $sText = '' ;
	
	/**
	 * Description
	 * 
	 * @access	private
	 * @static
	 * @var		array
	 */
	static private $arrPHPOriFunctions = array(
			'addcslashes' => array( 'function'=>'addcslashes', 'pos'=>0 ) 
			, 'addslashes' => array( 'function'=>'addslashes', 'pos'=>0 ) 
			, 'bin2hex' => array( 'function'=>'bin2hex', 'pos'=>0 ) 
			, 'chr' => array( 'function'=>'chr', 'pos'=>0 ) 
			, 'chunksplit' => array( 'function'=>'chunk_split', 'pos'=>0 ) 
			, 'convertcyrstring' => array( 'function'=>'convert_cyr_string', 'pos'=>0 ) 
			, 'convertuudecode' => array( 'function'=>'convert_uudecode', 'pos'=>0 ) 
			, 'convertuuencode' => array( 'function'=>'convert_uuencode', 'pos'=>0 ) 
			, 'countchars' => array( 'function'=>'count_chars', 'pos'=>0 ) 
			, 'crc32' => array( 'function'=>'crc32', 'pos'=>0 ) 
			, 'crypt' => array( 'function'=>'crypt', 'pos'=>0 ) 
			, 'explode' => array( 'function'=>'explode', 'pos'=>0 ) 
			, 'hebrev' => array( 'function'=>'hebrev', 'pos'=>0 ) 
			, 'hebrevc' => array( 'function'=>'hebrevc', 'pos'=>0 ) 
			, 'htmlentitydecode' => array( 'function'=>'html_entity_decode', 'pos'=>0 ) 
			, 'htmlentities' => array( 'function'=>'htmlentities', 'pos'=>0 ) 
			, 'htmlspecialcharsdecode' => array( 'function'=>'htmlspecialchars_decode', 'pos'=>0 ) 
			, 'htmlspecialchars' => array( 'function'=>'htmlspecialchars', 'pos'=>0 ) 
			, 'implode' => array( 'function'=>'implode', 'pos'=>0 ) 
			, 'levenshtein' => array( 'function'=>'levenshtein', 'pos'=>0 ) 
			, 'ltrim' => array( 'function'=>'ltrim', 'pos'=>0 ) 
			, 'md5' => array( 'function'=>'md5', 'pos'=>0 ) 
			, 'metaphone' => array( 'function'=>'metaphone', 'pos'=>0 ) 
			, 'nl2br' => array( 'function'=>'nl2br', 'pos'=>0 ) 
			, 'ord' => array( 'function'=>'ord', 'pos'=>0 ) 
			, 'parsestr' => array( 'function'=>'parse_str', 'pos'=>0 ) 
			, 'quotedprintabledecode' => array( 'function'=>'quoted_printable_decode', 'pos'=>0 ) 
			, 'quotemeta' => array( 'function'=>'quotemeta', 'pos'=>0 ) 
			, 'rtrim' => array( 'function'=>'rtrim', 'pos'=>0 ) 
			, 'sha1' => array( 'function'=>'sha1', 'pos'=>0 ) 
			, 'similartext' => array( 'function'=>'similar_text', 'pos'=>0 ) 
			, 'soundex' => array( 'function'=>'soundex', 'pos'=>0 ) 
			, 'sscanf' => array( 'function'=>'sscanf', 'pos'=>0 ) 
			, 'strgetcsv' => array( 'function'=>'str_getcsv', 'pos'=>0 ) 
			, 'strireplace' => array( 'function'=>'str_ireplace', 'pos'=>3, 'modify'=>1 ) 
			, 'strpad' => array( 'function'=>'str_pad', 'pos'=>0 ) 
			, 'strreplace' => array( 'function'=>'str_replace', 'pos'=>3, 'modify'=>1 ) 
			, 'strrot13' => array( 'function'=>'str_rot13', 'pos'=>0 ) 
			, 'strshuffle' => array( 'function'=>'str_shuffle', 'pos'=>0 ) 
			, 'strsplit' => array( 'function'=>'str_split', 'pos'=>0 ) 
			, 'strwordcount' => array( 'function'=>'str_word_count', 'pos'=>0 ) 
			, 'strcasecmp' => array( 'function'=>'strcasecmp', 'pos'=>0 ) 
			, 'strchr' => array( 'function'=>'strchr', 'pos'=>0 ) 
			, 'strcmp' => array( 'function'=>'strcmp', 'pos'=>0 ) 
			, 'strcoll' => array( 'function'=>'strcoll', 'pos'=>0 ) 
			, 'strcspn' => array( 'function'=>'strcspn', 'pos'=>0 ) 
			, 'striptags' => array( 'function'=>'strip_tags', 'pos'=>0 ) 
			, 'stripcslashes' => array( 'function'=>'stripcslashes', 'pos'=>0 ) 
			, 'stripos' => array( 'function'=>'stripos', 'pos'=>0 ) 
			, 'stripslashes' => array( 'function'=>'stripslashes', 'pos'=>0 ) 
			, 'stristr' => array( 'function'=>'stristr', 'pos'=>0 ) 
			, 'strlen' => array( 'function'=>'strlen', 'pos'=>0 ) 
			, 'strnatcasecmp' => array( 'function'=>'strnatcasecmp', 'pos'=>0 ) 
			, 'strnatcmp' => array( 'function'=>'strnatcmp', 'pos'=>0 ) 
			, 'strncasecmp' => array( 'function'=>'strncasecmp', 'pos'=>0 ) 
			, 'strncmp' => array( 'function'=>'strncmp', 'pos'=>0 ) 
			, 'strpbrk' => array( 'function'=>'strpbrk', 'pos'=>0 ) 
			, 'strpos' => array( 'function'=>'strpos', 'pos'=>0 ) 
			, 'strrchr' => array( 'function'=>'strrchr', 'pos'=>0 ) 
			, 'strrev' => array( 'function'=>'strrev', 'pos'=>0 ) 
			, 'strripos' => array( 'function'=>'strripos', 'pos'=>0 ) 
			, 'strrpos' => array( 'function'=>'strrpos', 'pos'=>0 ) 
			, 'strspn' => array( 'function'=>'strspn', 'pos'=>0 ) 
			, 'strstr' => array( 'function'=>'strstr', 'pos'=>0 ) 
			, 'strtok' => array( 'function'=>'strtok', 'pos'=>0 ) 
			, 'strtolower' => array( 'function'=>'strtolower', 'pos'=>0, 'modify'=>1 ) 
			, 'strtoupper' => array( 'function'=>'strtoupper', 'pos'=>0, 'modify'=>1 ) 
			, 'strtr' => array( 'function'=>'strtr', 'pos'=>0 ) 
			, 'substrcompare' => array( 'function'=>'substr_compare', 'pos'=>0 ) 
			, 'substrcount' => array( 'function'=>'substr_count', 'pos'=>0 ) 
			, 'substrreplace' => array( 'function'=>'substr_replace', 'pos'=>0, 'modify'=>1 ) 
			, 'substr' => array( 'function'=>'substr', 'pos'=>0 ) 
			, 'trim' => array( 'function'=>'trim', 'pos'=>0, 'modify'=>1 ) 
			, 'ucfirst' => array( 'function'=>'ucfirst', 'pos'=>0, 'modify'=>1 ) 
			, 'ucwords' => array( 'function'=>'ucwords', 'pos'=>0, 'modify'=>1 ) 
			, 'wordwrap' => array( 'function'=>'wordwrap', 'pos'=>0, 'modify'=>1 ) 
			
			// 一些别名
			, 'ireplace' => array( 'function'=>'str_ireplace', 'pos'=>3, 'modify'=>1 ) 
			, 'replace' => array( 'function'=>'str_replace', 'pos'=>3, 'modify'=>1 ) 
	 ) ;
}


