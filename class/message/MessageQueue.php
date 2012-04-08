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
namespace org\jecat\framework\message ;

use org\jecat\framework\lang\Assert;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\ui\xhtml\UIFactory;
use org\jecat\framework\ui\UI;
use org\jecat\framework\util\FilterMangeger;
use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\lang\Object;

class MessageQueue extends Object implements IMessageQueue
{
	public function add(Message $aMsg , $bIgnoreFilters=true)
	{
		if( $this->arrMsgQueue and in_array($aMsg, $this->arrMsgQueue) )
		{
			return ;
		}
		
		if( !$bIgnoreFilters and $this->aFilterManager )
		{
			list($aMsg)=$this->aFilterManager->handle($aMsg) ;
			if(!$aMsg)
			{
				return ;
			}
		}
		
		$this->arrMsgQueue[] = $aMsg ;
		
		return $aMsg ;
	}
	
	public function create($sType,$sMessage,$arrMessageArgs=null)
	{
		return $this->add(new Message($sType,$sMessage,$arrMessageArgs)) ;
	}
	
	public function iterator()
	{
		$aIterator = $this->arrMsgQueue? new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrMsgQueue): new \EmptyIterator() ;
		
		// for child container's children
		if($this->arrChildren)
		{
			foreach($this->arrChildren as $aChild)
			{
				if( $aChild instanceof IMessageQueue )
				{
					$aQueue = $aChild ;
				}
				else if( $aChild instanceof IMessageQueueHolder )
				{
					$aQueue = $aChild->messageQueue(false) ;
				}
				else 
				{
					Assert::wrong("未知的错误") ;
				}
				
				if($aQueue)
				{
					if(empty($aMergedIterator))
					{
						$aMergedIterator = new \AppendIterator() ;
						$aMergedIterator->append($aIterator) ;
					}
					$aMergedIterator->append($aQueue->iterator()) ;
				}
			}
		}
		
		// return merged iterators
		if(!empty($aMergedIterator))
		{
			return $aMergedIterator ;
		}
		// only self iterator
		else
		{
			return $aIterator ;
		}
	}
	
	public function count()
	{
		$nCount = $this->arrMsgQueue? count($this->arrMsgQueue): 0 ;
		
		if($this->arrChildren)
		{
			foreach($this->arrChildren as $aChild)
			{
				if( $aChild instanceof IMessageQueue )
				{
					$nCount+= $aChild->count() ;
				}
				else if( $aChild instanceof IMessageQueueHolder )
				{
					if( $aQueue = $aChild->messageQueue(false) )
					{
						$nCount+= $aQueue->count() ;
					}
				}
				else 
				{
					Assert::wrong("未知的错误") ;
				}
				
			}
		}
		
		return $nCount ;
	}
	
	/**
	 * @return org\jecat\framework\util\IFilterMangeger
	 */
	public function filters()
	{
		if(!$this->aFilterManager)
		{
			$this->aFilterManager = new FilterMangeger() ;
		}
		
		return $this->aFilterManager ;
	}
	
	public function setFilters(IFilterMangeger $aFilterManager)
	{
		$this->aFilterManager = $aFilterManager ;
	}
	
	public function display(UI $aUI=null,IOutputStream $aDevice=null,$sTemplate=null,$bSubTemplate=false)
	{
		if( !$sTemplate )
		{
			$sTemplate = 'org.jecat.framework:MsgQueue.template.html' ;
		}
		
		if( !$aUI )
		{
			$aUI = UIFactory::singleton()->create() ;
		}
		
		if( !$bSubTemplate )
		{
			$aUI->display($sTemplate,array('aMsgQueue'=>$this),$aDevice) ;
		}
		else
		{
			call_user_func_array($sTemplate,array(
					new HashTable(array('aMsgQueue'=>$this))
					, $aDevice
			)) ;
		}
	}
	
	public function addChild(IMessageQueue $aMessageQueue)
	{
		if( !$this->arrChildren or !in_array($aMessageQueue,$this->arrChildren,true) )
		{
			$this->arrChildren[] = $aMessageQueue ;
		}
	}
	public function removeChild(IMessageQueue $aMessageQueue)
	{
		if( $this->arrChildren and ($pos=array_pop($aMessageQueue,$this->arrChildren,true))!==false )
		{
			unset($this->arrChildren[$pos]) ;
		}
	}
	
	public function addChildHolder(IMessageQueueHolder $aQueueHolder)
	{
		if( !$this->arrChildren or !in_array($aQueueHolder,$this->arrChildren,true) )
		{
			$this->arrChildren[] = $aQueueHolder ;
		}
	}
	public function removeChildHolder(IMessageQueueHolder $aMessageQueue)
	{
		if( $this->arrChildren and ($pos=array_pop($aMessageQueue,$this->arrChildren,true))!==false )
		{
			unset($this->arrChildren[$pos]) ;
		}
	}
	
	private $arrMsgQueue ;
	
	private $aFilterManager ;

	private $arrChildren ;
}

