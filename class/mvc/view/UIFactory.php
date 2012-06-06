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
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\fs\Folder;
use org\jecat\framework\ui\xhtml\parsers\ParserStateTag;
use org\jecat\framework\ui\xhtml\UIFactory as UIFactoryBase;

class UIFactory extends UIFactoryBase
{
	/**
	 * return SourceFileManager
	 */
	public function newInterpreterManager()
	{
		$aInterpreters = parent::newInterpreterManager() ;
		
		// for mvc
		ParserStateTag::singleton()->addTagNames(
				'views', 'view', 'widget', 'form', 'msgqueue', 'view:msgqueue', 'widget:msgqueue', 'resrc', 'link', 'css', 'script'
				, 'js', 'model:foreach', 'model:foreach:end', 'model:data', 'data', 'template', 'model:foreach:else'
				, 'bean' , 'menu' , 'item'
		) ;
		
		return $aInterpreters ;
	}
	
	/**
	 * return SourceFileManager
	 */
	public function createNodeCompiler()
	{
		$aNodeCompilers = parent::createNodeCompiler() ;
		
		$aNodeCompilers->addSubCompiler('views',__NAMESPACE__."\\uicompiler\\ViewCompiler") ;
		$aNodeCompilers->addSubCompiler('view',__NAMESPACE__."\\uicompiler\\ViewCompiler") ;
		$aNodeCompilers->addSubCompiler('widget',__NAMESPACE__."\\uicompiler\\WidgetCompiler") ;
		$aNodeCompilers->addSubCompiler('form',__NAMESPACE__."\\uicompiler\\FormCompiler") ;
		$aNodeCompilers->addSubCompiler('msgqueue',__NAMESPACE__."\\uicompiler\\MsgQueueCompiler") ;
		$aNodeCompilers->addSubCompiler('view:msgqueue',__NAMESPACE__."\\uicompiler\\ViewMsgQueueCompiler") ;
		$aNodeCompilers->addSubCompiler('widget:msgqueue',__NAMESPACE__."\\uicompiler\\WidgetMsgQueueCompiler") ;
		
		$aNodeCompilers->addSubCompiler('model:foreach',__NAMESPACE__."\\uicompiler\\ModelForeachCompiler") ;
		$aNodeCompilers->addSubCompiler('model:foreach:else',"org\\jecat\\framework\\ui\\xhtml\\compiler\\node\\LoopelseCompiler") ;
		$aNodeCompilers->addSubCompiler('model:foreach:end',"org\\jecat\\framework\\ui\\xhtml\\compiler\\node\\LoopEndCompiler") ;
		$aNodeCompilers->addSubCompiler('model:data',__NAMESPACE__."\\uicompiler\\ModelDataCompiler") ;
		$aNodeCompilers->addSubCompiler('data',__NAMESPACE__."\\uicompiler\\ModelDataCompiler") ;
		
		$aNodeCompilers->addSubCompiler('menu',__NAMESPACE__."\\uicompiler\\MenuCompiler") ;
		$aNodeCompilers->addSubCompiler('item',__NAMESPACE__."\\uicompiler\\MenuCompiler") ;
		
		$aNodeCompilers->addSubCompiler('input',__NAMESPACE__."\\uicompiler\\WidgetCompiler") ;
		$aNodeCompilers->addSubCompiler('textarea',__NAMESPACE__."\\uicompiler\\WidgetCompiler") ;
		$aNodeCompilers->addSubCompiler('select',__NAMESPACE__."\\uicompiler\\WidgetCompiler") ;
		
		return $aNodeCompilers ;
	}
	
	/**
	 * @return SourceFileManager
	 */
	public function newSourceFileManager()
	{
		$aSrcMgr = parent::newSourceFileManager() ;
		$aSrcMgr->addFolder(new Folder(\org\jecat\framework\PATH.'/template',Folder::CLEAN_PATH),null,'org\\jecat\\framework') ;
		
		return $aSrcMgr ;
	}
	
}

