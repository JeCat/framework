<?php
namespace org\jecat\framework\mvc\view\widget ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\util\StopFilterSignal;
use org\jecat\framework\message\Message;
use org\jecat\framework\message\IMessageQueue;
use org\jecat\framework\message\MessageQueue;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\ui\UI;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\lang\Object ;

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
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
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

	public function setView(IView $aView=null)
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
	
	public function setAttribute($sName,$value)
	{
		$sName = strtolower($sName) ;
		$this->arrAttributes[$sName] = $value ;
	}
	public function attribute($sName,$default=null)
	{
		if(!$this->arrAttributes)
		{
			return $default ;
		}
		$sName = strtolower($sName) ;
		return isset($this->arrAttributes[$sName])? $this->arrAttributes[$sName]: $default ;
	}
    public function attributeBool($sName,$bValue=true)
    {
        $value=$this->attribute($sName,null);
        
        if($value === null)
        {
            return $bValue? true: false;
        }
        
        $value = strtolower($value) ;
        
        if($value === 'false' || $value === '0' || $value === 0 || $value === 'no' || $value === 'off' ){
            return false;
        }
        else
       {
            return true;
        }
	}
	public function attributeNameIterator()
	{
		return $this->arrAttributes? new \org\jecat\framework\pattern\iterate\ArrayIterator(array_keys($this->arrAttributes)): new \EmptyIterator() ;
	}
	public function removeAttribute($sName)
	{
		unset($this->arrAttributes[$sName]) ;
	}
	public function clearAttribute()
	{
		$this->arrAttributes = null ;
	}
	
	public function displayInputAttributes()
	{
		if(!$this->arrAttributes)
		{
			return '' ;
		}
		
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
	
	private $arrAttributes ;
	
    private $arrBeanConfig ;
}

?>
