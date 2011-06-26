<?php
namespace jc\mvc\view ;

use jc\ui\xhtml\UIFactory as UIFactoryBase;

class UIFactory extends UIFactoryBase
{
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
		$aNodeCompilers->addSubCompiler('resrc',__NAMESPACE__."\\uicompiler\\LoadResourceCompiler") ;
		
		return $aNodeCompilers ;
	}
	
	/**
	 * @return SourceFileManager
	 */
	public function newSourceFileManager()
	{
		$aSrcMgr = parent::newSourceFileManager() ;
		$aSrcMgr->addFolder(\jc\PATH.'src/template/') ;
		
		return $aSrcMgr ;
	}
	
}

?>