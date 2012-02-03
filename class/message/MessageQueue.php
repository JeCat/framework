<?php
namespace org\jecat\framework\message ;

use org\jecat\framework\lang\Assert;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\util\HashTable;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\ui\xhtml\UIFactory;
use org\jecat\framework\io\OutputStream;
use org\jecat\framework\ui\UI;
use org\jecat\framework\util\FilterMangeger;
use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\lang\Object;
use org\jecat\framework\pattern\composite\Container;

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

?>