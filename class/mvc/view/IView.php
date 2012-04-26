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
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\mvc\controller\Controller;
use org\jecat\framework\message\IMessageQueueHolder;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\mvc\view\widget\IWidgetContainer;
use org\jecat\framework\pattern\composite\IContainer;

interface IView extends IContainer, IMessageQueueHolder, IWidgetContainer
{
	
	/**
	 * @return org\jecat\framework\util\IHashTable
	 */
	public function variables() ;
	
	public function setVariables(IHashTable $aVariables) ;

	public function render(IOutputStream $aDevice) ;
	
	public function show(IOutputStream $aDevice=null) ;
		
	public function enable($bEnalbe=true) ;
	
	public function isEnable() ;
	
	public function exchangeData($nWay=DataExchanger::MODEL_TO_WIDGET) ;
	
	/**
	 * @return org\jecat\framework\mvc\model\IModel
	 */
	public function model() ;
	
	public function setModel(IModel $aModel) ;
	
	/**
	 * @return org\jecat\framework\mvc\controller\IContainer
	 */
	public function controller() ;
	
	public function setController(Controller $aController=null) ;
    
    public function id() ;
    
}

