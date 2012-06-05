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

namespace org\jecat\framework\lang ;

class Type
{
	/*const STRING = 1 ;
	const INT = 2 ;
	const FLOAT = 4 ;
	const NUMERIC = 6 ;
	const BOOL = 8 ;
	const NULL = 16 ;
	const RESOURCE = 32 ;
	const MAP = 64 ;
	const ARR = 64 ;
	const OBJECT = 128 ;*/
	
	static public $null = null ;

	const STRING = "string" ;
	const INT = "int" ;
	const FLOAT = "float" ;
	const NUMERIC = "numeric" ;
	const ARR = "array" ;
	const MAP = "array" ;
	const BOOL = "bool" ;
	const NULL = "null" ;
	const RESOURCE = "resource" ;
	const OBJECT = "object" ;
	const CALLBACK = "callback" ;
	
	static private $arrTypes = array(
			self::STRING ,
			self::INT ,
			self::FLOAT ,
			self::NUMERIC ,
			self::BOOL ,
			self::NULL ,
			self::RESOURCE ,
			self::ARR ,
			self::OBJECT ,
			self::CALLBACK ,
	) ;
	
	static public function check($Types,& $Variable)
	{
		if( is_string($Types) )
		{
			$Types = array($Types) ;
		}
		else if( !is_array($Types) )
		{
			throw new Exception('参数错误：$Types传入的数据类型无效：%s。',$Types) ;
		}
		
		$sVarType = self::reflectType($Variable) ;

		foreach($Types as $RequireType) 
		{
			// 基本类型
			if( in_array($sVarType,self::$arrTypes) )
			{
				if($sVarType===$RequireType)
				{
					return true ;
				}
			}
			
			// 
			else
			{
				if( is_a($Variable, $RequireType) )
				{
					return true ;
				}
			}
		}
				
		return false ;
	}
	
	static public function reflectType($Variable)
	{
		$sType = self::detectType($Variable) ;
		if( $sType==self::OBJECT )
		{
			return get_class($Variable) ;
		}
		else
		{
			return $sType ;
		}
	}
	
	static public function detectType($Variable)
	{
		if( is_string($Variable) )
		{
			return self::STRING ;
		}
		else if( is_int($Variable) )
		{
			return self::INT ;
		}
		else if( is_float($Variable) )
		{
			return self::FLOAT ;
		}
		else if( is_array($Variable) )
		{
			return self::ARR ;
		}
		else if( is_bool($Variable) )
		{
			return self::BOOL ;
		}
		else if( is_resource($Variable) )
		{
			return self::RESOURCE ;
		}
		else if( is_object($Variable) )
		{
			return self::OBJECT ;
		}
		else if( $Variable===null )
		{
			return self::NULL ;
		}
	}
	
	static public function hasImplements($sClassName,$sInterfaceName)
	{
		$aRefClass = new \ReflectionClass($sClassName) ;
		return $aRefClass->implementsInterface($sInterfaceName) ;
	}
	
	const toArray_normal = 1 ;
	const toArray_emptyForNull = 2 ;
	const toArray_ignoreNull = 3 ;
	
	static public function toArray(&$variable,$nFlag=self::toArray_emptyForNull)
	{
		if( !is_array($variable) )
		{
			if( $variable===null )
			{
				if( $nFlag===self::toArray_ignoreNull )
				{}
				else if( $nFlag===self::toArray_emptyForNull )
				{
					$variable = array() ;
				}
				else if( $nFlag===self::toArray_normal )
				{
					$variable = array($variable) ;
				}
			}
			else 
			{
				$variable = array($variable) ;
			}
			
		}

		return $variable ;
	}
	
	static public function reflectFunctionBody($fnCallback)
	{
		$aRefFunc = is_array($fnCallback)? new \ReflectionMethod($fnCallback[0],$fnCallback[1]): new \ReflectionFunction($fnCallback) ;
		
		// 源文
		$arrSourceLines = file($aRefFunc->getFileName()) ;
		$nStartLine = $aRefFunc->getStartLine()+1-1 ;
		$nEndLine = $aRefFunc->getEndLine() ;
		$nEndLine = $aRefFunc->getEndLine()-1-1 ;
		$arrFunctionLines = array_slice($arrSourceLines, $nStartLine, $nEndLine-$nStartLine+1) ;
		$sBodySource = implode('',$arrFunctionLines) ;
		$sBodySource = trim($sBodySource) ;
		$sBodySource = preg_replace('/(^\\{)/', '', $sBodySource) ;
		
		return $sBodySource ;
	}
}

/**
 * @wiki /Jecat/命名规则(Jecat命名法)
 * 
 * Jecat及其衍生项目基本遵循匈牙利命名法,只是有些不同（JeCat 是一个PHP框架，而不是C++）.
 * JeCat 到 version 0.6.1 为止，尝试过多种命名风格。目前所采用的风格主要考虑了3个方面的标准：
 * 1.针对 PHP 语言特性
 * 2.语义清晰
 * 3.书写简便
 * == 变量命名 ==
 * 变量名用1-3个小写字母开始，表示变量类型。随后的单词采用首字母大写的“驼峰”风格，单词之间不需要分隔符号。
 * 例如：$sUserName ， 开头的小写 "s"是 string 的简写，代表字符串，它表示：“这是一个字符串变量，保存在这里的是字符串；用户名是一个字符串”。随后的两个单词"user","name" 都是首字母大写，其余字母小写；两个单词拼接在一起不需要分隔符。
 * 大小写错落的书写风格已经能够很清晰地区分出不同单词，使用下划线来分隔单词是一个拼写上的小小的负担。
 * 由于PHP是一门弱类型的编程语言，这个程序的调试带来一些了麻烦，所以我们在变量名的开头用1-3个小写字母来表示变量类型，并且不允许在程序运行中改变变量的类型（除非有充足的理由），也就时说，你使用了一个字符串变量，直到这个字符串遇到PHP的垃圾回收器时，它都必须是一个字符串。
 * 变量类型简写：
 * {|
 * !类型
 * !简写
 * !例子
 * |-- -- 
 * |数值
 * |n
 * |$nNumber = 123 ;
 * |-- -- 
 * |整数
 * |i
 * |$iNumber = 123 ;
 * |-- -- 
 * |浮点
 * |f
 * |$fPrice = 12.05 ;
 * |-- --
 * |布尔
 * |b
 * |$bSuccess = true ;
 * |-- --
 * |数组
 * |arr
 * |$arrMsgQueue = array( 'hello world', 'hello jecat' ) ;
 * |-- --
 * |字符串
 * |s
 * |$sName = 'aleechou' ;
 * |-- --
 * |哈希表
 * |map
 * |$mapPlayScores = array( 'alee'=100, 'sara'=101 ) ;
 * |-- --
 * |对象
 * |a
 * |$aCar = new Car() ;
 * |-- --
 * |函数
 * |fn
 * |$fnCallback = function(){ ... ... } ;
 * |-- -- 
 * |不确定类型
 * |省略类型简写
 * |
 * |}
 * 她们看上去很“优雅”，对吧？如果你不同意也没有关系，等到有一天你看习惯了，就会觉得既自然又亲切了 ^_^ 。 关键的问题在于：正真的优雅，来自于整个系统的一致性，这是你需要遵循许多约定的主要原因。
 * 
 * ==函数(方法)命名==
 * 函数名采用小写字母开头的“驼峰命名法”：如果只有一个单词，则函数名称全体字母都是小写；从第二个单词开始首字母大写。这样可以在书写时轻微减少大小写的切换次数。
 * 例如：$aObject->messageQueue() , $aMessageQueue->message() ;
 * 
 * ==Getter==
 * 类的 getter 方法不需要 "get" 前缀，直接用属性名称即可。例如：$aObject->messageQueue()
 * 
 * ==Setter==
 * 但是类的 setter 方法需要一个"set"前缀，set + 属性名称。例如：例如：$aObject->setMessageQueue()
 * getter() 方法和 setter()方法是不对称的，但是很简洁，可以在书写时有效地减少 输入 和 大小写切换，并且也保持了语义的清晰，并不影响代码的可读性。
 * 
 * ==类命名==
 * 类的命名只有一条规则：所有单词均为首字母大写。
 * 每个类都在一个单独的文件里定义，类文件的文件名是：<类名> + ".php"。 
 * 
 * ==常量==
 * 常量名称通常来说是全部大写字母书写，单词之间使用下划线("_")分隔。
 * 但是，使用频繁的类常量(class const)，可以用函数的命名方法，从第二个单词开始首字母大写。
 * 
 * ==与流行的“匈牙利命名法”有何不同？==
 * 类属性不需要 "m_" 开头
 * 在VC中通常是这样： void * m_strSomeString ;
 * 而在 JeCat 中是:  private $sSomeString ;
 * 因为 PHP 不能省略 "$this"， 所以也无需通过"m_"来区分对象属性和函数的局部变量——所有对象属性都要在"$this->"后面。
 * VC函数名的每个单词都是首字母大写；JeCat 的函数名从第二个单词开始首字母大写。
 */

