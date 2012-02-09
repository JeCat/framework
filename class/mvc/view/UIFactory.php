<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\fs\FileSystem;

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
				, 'js', 'model:foreach', 'model:foreach:end', 'model:data', 'data', 'template'
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
		$aNodeCompilers->addSubCompiler('model:foreach:end',"org\\jecat\\framework\\ui\\xhtml\\compiler\\node\\DoubleEndCompiler") ;
		$aNodeCompilers->addSubCompiler('model:data',__NAMESPACE__."\\uicompiler\\ModelDataCompiler") ;
		$aNodeCompilers->addSubCompiler('data',__NAMESPACE__."\\uicompiler\\ModelDataCompiler") ;
		
		return $aNodeCompilers ;
	}
	
	/**
	 * @return SourceFileManager
	 */
	public function newSourceFileManager()
	{
		$aSrcMgr = parent::newSourceFileManager() ;
		$aSrcMgr->addFolder(FileSystem::singleton()->findFolder('/framework/template/'),null,'org\\jecat\\framework') ;
		
		return $aSrcMgr ;
	}
	
}

?>