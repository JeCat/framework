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
namespace org\jecat\framework\db\sql\parser ;

use org\jecat\framework\lang\Object;

/**
 * @return Parser
 */
class BaseParserFactory extends Object
{	
	/**
	 * @return AbstractParser
	 */
	public function create($bShare=true,Dialect $aDialect=null,$sAction='statement')
	{
		if( $bShare and isset($this->arrShareParsers[$sAction]) )
		{
			return $this->arrShareParsers[$sAction] ;
		}
		
		if(!$aDialect)
		{
			$aDialect = Dialect::singleton() ;
		}
		
		switch($sAction)
		{
			case 'statement' :
				$aParser = self::createParserInstace('AbstractParser',$aDialect)
								->addChildState($this->create($bShare,$aDialect,'insert'))
								->addChildState($this->create($bShare,$aDialect,'replace'))
								->addChildState($this->create($bShare,$aDialect,'delete'))
								->addChildState($this->create($bShare,$aDialect,'update'))
								->addChildState($this->create($bShare,$aDialect,'select'))
								->addChildState($this->create($bShare,$aDialect,'create'))
								->addChildState($this->create($bShare,$aDialect,'drop'))
								->addChildState($this->create($bShare,$aDialect,'from'))
								->addChildState($this->create($bShare,$aDialect,'where')) 
								->addChildState($this->create($bShare,$aDialect,'group'))
								->addChildState($this->create($bShare,$aDialect,'order'))
								->addChildState($this->create($bShare,$aDialect,'limit'))
								->addChildState($this->create($bShare,$aDialect,'set'))
								->addChildState($this->create($bShare,$aDialect,'values'))
								->addChildState($this->create($bShare,$aDialect,'table-keyword')) ;
				break ;

			case 'select' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'select')
								->addChildState($this->create($bShare,$aDialect,'function'))
								->addChildState($this->create($bShare,$aDialect,'column')) ;
				break ;

				case 'replace' :
				case 'insert' :
					$aParser = self::createParserInstace('ClauseParser',$aDialect,'insert')
								->addChildState($this->create($bShare,$aDialect,'into'))
								->addChildState($this->create($bShare,$aDialect,'column')) 
								->addChildState($this->create($bShare,$aDialect,'values')) ;
					break ;

			case 'update' :
					$aParser = self::createParserInstace('ClauseParser',$aDialect,'update') ;
					break ;
					
			case 'delete' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'delete')
								->addChildState($this->create($bShare,$aDialect,'from')) ;
				break ;

			case 'into' :
					$aParser = self::createParserInstace('IntoParser',$aDialect)
								->addChildState($this->create($bShare,$aDialect,'table')) ;
					break ;
						
			case 'from' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'from') 
							->addChildState($this->create($bShare,$aDialect,'subquery') )
							->addChildState($this->create($bShare,$aDialect,'table'))
							->addChildState($this->create($bShare,$aDialect,'join'))  ;
				break ;
				
			case 'join' :
				$aParser = self::createParserInstace('TableJoinParser',$aDialect)
								->addChildState($this->create($bShare,$aDialect,'table'))
								->addChildState($this->create($bShare,$aDialect,'on'))
								->addChildState($this->create($bShare,$aDialect,'using')) ;
				break ;
				
			case 'using' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'using') 
								->addChildState($this->create($bShare,$aDialect,'column')) ;
				break ;
				
			case 'on' :
			case 'where' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,$sAction)
								->addChildState($this->create($bShare,$aDialect,'function'))
								->addChildState($this->create($bShare,$aDialect,'column')) ;
				break ;
				
			case 'group' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'group')
								->addChildState($this->create($bShare,$aDialect,'column')) ;
				break ;
				
			case 'order' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'order')
								->addChildState($this->create($bShare,$aDialect,'column')) ;
				break ;
				
			case 'limit' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'limit') ;
				break ;
				
			case 'set' :
				$aParser = self::createParserInstace('SetParser',$aDialect)
								->addChildState($this->create($bShare,$aDialect,'function'))
								->addChildState($this->create($bShare,$aDialect,'column')) ;
				break ;
				
			case 'values' :
				$aParser = self::createParserInstace('ValuesParser',$aDialect)
								->addChildState($this->create($bShare,$aDialect,'function'))
								->addChildState($this->create($bShare,$aDialect,'column'))
								->addChildState($this->create($bShare,$aDialect,'subquery')) ;
				break ;
				
			case 'column' :
				$aParser = self::createParserInstace('ColumnParser',$aDialect) ;
				break ;
				
			case 'table' :
				$aParser = self::createParserInstace('TableParser',$aDialect) ;
				break ;
				
			case 'table-keyword' :
				$aParser = self::createParserInstace('TableKeywordParser',$aDialect) ;
				break ;
				
			case 'subquery' :
				$aParser = self::createParserInstace('SubQueryParser',$aDialect) ;
				break ;

			case 'function' :
				$aParser = self::createParserInstace('FunctionParser',$aDialect) ;
				break ;

			case 'create' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'create')
								->addChildState($this->create($bShare,$aDialect,'table-keyword')) ;
				break ;
			case 'drop' :
				$aParser = self::createParserInstace('ClauseParser',$aDialect,'drop')
								->addChildState($this->create($bShare,$aDialect,'table-keyword')) ;
				break ;
		}
		
		if( $bShare )
		{
			$this->arrShareParsers[$sAction] = $aParser ;
		}
		
		return $aParser ;
	}
	
	/**
	 * @return AbstractParser
	 */
	public function createParserSelect($bShare=true,Dialect $aDialect=null)
	{
		return $this->create($bShare,$aDialect,'select') ;
	}
	/**
	 * @return AbstractParser
	 */
	public function createParserFrom($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'from') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserWhere($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'where') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserOrder($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'order') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserGroup($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'group') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserLimit($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'limit') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserOn($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'on') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserUsing($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'Using') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserColumn($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'column') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserTable($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'table') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserSubquery($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'suquery') ; }
	/**
	 * @return AbstractParser
	 */
	public function createParserJoin($bShare=true,Dialect $aDialect=null)
	{ return $this->create($bShare,$aDialect,'join') ; }
	
	
	/**
	 * @return AbstractParser
	 */
	static private function createParserInstace($sClass,Dialect $aDialect,$argvs=null,$sNamespace=__NAMESPACE__)
	{
		$sClass = $sNamespace.'\\'.$sClass ;
		$aParser = $sClass::createInstance($argvs) ;
		
		$aParser->setDialect($aDialect) ;
		
		return $aParser ;
	}
	
	private $arrShareParsers ;
}
