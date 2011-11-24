<?php
namespace jc\mvc\view\widget ;

use jc\bean\BeanFactory;
use jc\bean\IBean;
use jc\resrc\HtmlResourcePool;
use jc\util\StopFilterSignal;
use jc\message\Message;
use jc\message\IMessageQueue;
use jc\message\MessageQueue;
use jc\util\HashTable;
use jc\ui\UI;
use jc\io\IOutputStream;
use jc\mvc\view\IView;
use jc\lang\Exception;
use jc\util\IHashTable;
use jc\lang\Object ;

class Widget extends Object implements IViewWidget, IBean
{	
	public function __construct($sId=null,$sTemplateName=null,$sTitle=null,IView $aView=null)
	{
		parent::__construct() ;
		
		$this->setId($sId) ;
		$this->setTitle($sTitle?$sTitle:$sId) ;
		$this->setTemplateName($sTemplateName) ;
		
		// 消息队列过滤器
		$this->messageQueue()->filters()->add(function ($aMsg,$aWidget){
			if($aMsg->poster()!=$aWidget)
			{
				StopFilterSignal::stop() ;
			}
			
			return array($aMsg) ;
		},$this) ;
		
		if($aView)
		{
			$aView->addWidget($this) ;
		}
	}

	
    /**
     * properties:
     * 	id				string
     *  title			string
     *  template		string
     *   
     * @see jc\bean\IBean::build()
     */
	public function build(array & $arrConfig,$sNamespace='*')
	{
		if( !empty($arrConfig['id']) )
		{
			$this->setId($arrConfig['id']) ;
		}
		if( !empty($arrConfig['title']) )
		{
			$this->setTitle($arrConfig['title']) ;
		}
		if( !empty($arrConfig['template']) )
		{
			$this->setTemplateName($arrConfig['template']) ;
		}
		
    	$this->arrBeanConfig = $arrConfig ;
    }
    
	public function beanConfig()
	{
		return $this->arrBeanConfig ;
	}

	public function title()
	{
		return $this->sTitle ;
	}
	
	public function setTitle($sTitle)
	{
		$this->sTitle = $sTitle ;
	}
	
	/**
	 * @return IView
	 */
	public function view()
	{
		return $this->aView ;
	}

	public function setView(IView $aView)
	{
		$this->aView = $aView ;
	}

	public function id($bAutoId=true)
	{
	    if( $this -> sId === null and $bAutoId){
	        $this -> sId = strtr(get_class($this),'\\','.').self::$nAutoIncreaseId++;
	    }
		return $this->sId ;
	}

	public function setId($sId)
	{
		$this->sId = $sId ;
	}

	public function templateName()
	{
		return $this->sTemplateName ;
	}

	public function setTemplateName($sTemplateName)
	{
		$this->sTemplateName = $sTemplateName ;
	}

	public function display(UI $aUI,IHashTable $aVariables=null,IOutputStream $aDevice=null)
	{
		$sTemplateName = $this->templateName() ;
		if(!$sTemplateName)
		{
			throw new Exception("显示UI控件时遇到错误，UI控件尚未设置模板文件",$this->id()) ;
		}
		
		if(!$aVariables)
		{
			$aVariables = new HashTable() ;
		}

		$oldWidget=$aVariables->get('theWidget');
		$aVariables->set('theWidget',$this);
		$aUI->display($sTemplateName,$aVariables,$aDevice) ;	
		$aVariables->set('theWidget',$oldWidget);
	}

	/**
	 * @return IMessageQueue
	 */
	public function messageQueue()
	{
		if( !$this->aMsgQueue )
		{
			$this->aMsgQueue = new MessageQueue() ;
		}
		
		return $this->aMsgQueue ;
	}
	
	public function setMessageQueue(IMessageQueue $aMsgQueue)
	{
		$this->aMsgQueue = $aMsgQueue ;
	}

	public function createMessage($sType,$sMessage,$arrMessageArgs=null,$aPoster=null)
	{
		return $this->messageQueue()->create($sType,$sMessage,$arrMessageArgs,$aPoster) ;
	}
	
	public function setAttribute($sName,$sValue)
	{
		$this->arrAttributes[$sName] = $sValue ;
	}
	public function attribute($sName,$sValue)
	{
		return isset($this->arrAttributes[$sName])? $this->arrAttributes[$sName]: $sValue ;
	}
	public function attributeNameIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator(array_keys($this->arrAttributes)) ;
	}
	public function removeAttribute($sName)
	{
		unset($this->arrAttributes[$sName]) ;
	}
	
	public function displayInputAttributes()
	{
		$sRet = '' ;
		foreach($this->arrAttributes as $sName=>$sValue)
		{
			$sRet.= ' ' . $sName . '="' . addcslashes($sValue,'"\\') . '"' ;
		}
		return $sRet ;
	}
	
	static private $nAutoIncreaseId=0;
	
	private $aView ;

	private $sId ;
	
	private $sTemplateName ;
	
	private $aMsgQueue ;

	private $sTitle ;
	
	private $arrAttributes = array() ;
	
    private $arrBeanConfig ;
}

?>