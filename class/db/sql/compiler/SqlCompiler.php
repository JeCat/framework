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
namespace org\jecat\framework\db\sql\compiler ;

use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Object;

class SqlCompiler extends Object
{
	public function __construct($bEmpty=false)
	{
		if(!$bEmpty)
		{
			$this->arrTokenCompilers['column'] 
			 = $this->arrTokenCompilers['table'] 
			 = new SqlNameCompiler() ;
		}
	}
	
	public function compile(array & $arrRawSql,array & $arrInFactors=null,array & $arrTokenTree=null,$sTreeType='subtree')
	{
		$sSql = '' ;
		if(!$arrTokenTree)
		{
			$arrTokenTree =& $arrRawSql ;
		}
		
		if( !empty($arrRawSql[$sTreeType]) )
		{
			// data factors
			if( !empty($arrRawSql['factors']) )
			{
				$arrFactors =& $arrRawSql['factors'] ;
			}
			else
			{
				$arrFactors =& $arrInFactors ;
			}
						
			foreach($arrRawSql[$sTreeType] as &$token)
			{
				if( is_string($token) or is_numeric($token) )
				{
					if( $arrFactors and array_key_exists($token,$arrFactors) )
					{
						$sSql.= " '" . addslashes($arrFactors[$token]) . "'" ;
					}
					else 
					{
						$sSql.= ' ' . $token ;
					}
				}
				else if( is_array($token) )
				{
					if( $this->arrTokenCompilers and !empty($this->arrTokenCompilers[$token['expr_type']]) )
					{
						$sSql.= ' ' . $this->arrTokenCompilers[$token['expr_type']]->compile($this,$arrTokenTree,$token,$arrFactors) ;
					}
					
					else if( !empty($token['subtree']) )
					{
						$sSqlSubtree = $this->compile($token,$arrFactors,$arrTokenTree) ;
						
						// 仅在 subtree 不为空的时候，处理 pretree
						if( $sSqlSubtree and !empty($token['pretree']) )
						{
							$sSql.= ' ' . $this->compile($token,$arrFactors,$arrTokenTree,'pretree') ;
						}
						
						$sSql.= ' ' . $sSqlSubtree ;
					}
				}
				else
				{
					throw new SqlCompileException($arrTokenTree,$token,"遇到类型无效的 sql token: %s",Type::detectType($token)) ;
				}
			}
		}
		
		return $sSql? substr($sSql,1): '' ;
	}
		
	public function registerTokenCompiler($sExprType,$aCompiler)
	{
		$this->arrTokenCompilers[$sExprType] = $aCompiler ;
	}
	
	private $arrTokenCompilers ;
}
