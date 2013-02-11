<?php

class App_Controller extends Zend_Controller_Action {
	
	/**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em = null;

    /**
     * @var \sfServiceContainer
     */
    protected $_sc = null;
    /**
     * @var \App\Service\RandomQuote
     * @InjectService RandomQuote
     */
    protected $_randomQuote = null;


    public function init()
    {
        $this->_em = Zend_Registry::get('em');
    }


	protected function addJs($file, $useCdn = true)
	{
		if($useCdn === true)
			$url = Zend_Registry::get('configObj')->app->cdnUrl == '' ? $view->baseUrl($file) : Zend_Registry::get('configObj')->app->cdnUrl . $file;

		$this->view->headScript()->appendFile($url);
	}

	protected function addCss($file, $useCdn = true)
	{
		if($useCdn === true)
			$url = Zend_Registry::get('configObj')->app->cdnUrl == '' ? $view->baseUrl($file) : Zend_Registry::get('configObj')->app->cdnUrl . $file;

		$this->view->headLink()->appendStylesheet($url);
	}

}