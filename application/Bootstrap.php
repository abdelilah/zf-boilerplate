<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initConfig()
    {
        Zend_Registry::set('config', $this->getOptions());

        // Store config file as object is more handy
        $conf = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        Zend_Registry::set('configObj', $conf);
    }
    
    public function _initAutoloaderNamespaces()
    {
        require_once APPLICATION_PATH .
            '/../library/Doctrine/Common/ClassLoader.php';

        require_once APPLICATION_PATH .
            '/../library/Symfony/Component/Di/sfServiceContainerAutoloader.php';

        sfServiceContainerAutoloader::register();
        $autoloader = \Zend_Loader_Autoloader::getInstance();

        $fmmAutoloader = new \Doctrine\Common\ClassLoader('Bisna');
        $autoloader->pushAutoloader(array($fmmAutoloader, 'loadClass'), 'Bisna');
        
        $fmmAutoloader = new \Doctrine\Common\ClassLoader('App');
        $autoloader->pushAutoloader(array($fmmAutoloader, 'loadClass'), 'App');

        $fmmAutoloader = new \Doctrine\Common\ClassLoader('Boilerplate');
        $autoloader->pushAutoloader(array($fmmAutoloader, 'loadClass'), 'Boilerplate');

        $fmmAutoloader = new \Doctrine\Common\ClassLoader('Doctrine\DBAL\Migrations');
        $autoloader->pushAutoloader(array($fmmAutoloader, 'loadClass'), 'Doctrine\DBAL\Migrations');
    }

    public function _initModuleLayout()
    {
        $front = Zend_Controller_Front::getInstance();

        $front->registerPlugin(
            new Boilerplate_Controller_Plugin_ModuleLayout()
        );
        
        $front->setParam('prefixDefaultModule', true);
        $eh = new Zend_Controller_Plugin_ErrorHandler();
        $front = Zend_Controller_Front::getInstance()->registerPlugin($eh);
    }

    public function _initServices()
    {
        $sc = new sfServiceContainerBuilder();
        $loader = new sfServiceContainerLoaderFileXml($sc);
        $loader->load(APPLICATION_PATH . "/configs/services.xml");
        Zend_Registry::set('sc', $sc);
    }

    public function _initLocale()
    {
        $config = $this->getOptions();
        
        try{
            $locale = new Zend_Locale(Zend_Locale::BROWSER);
        } catch (Zend_Locale_Exception $e) {
            $locale = new Zend_Locale($config['resources']['locale']['default']);
        }

        Zend_Registry::set('Zend_Locale', $locale);

        $translator = new Zend_Translate(
            array(
                'adapter' => 'Csv',
                'content' => APPLICATION_PATH . '/../data/lang/',
                'scan' => Zend_Translate::LOCALE_DIRECTORY,
                'delimiter' => ',',
                'disableNotices' => true,
            )
        );

        if (!$translator->isAvailable($locale->getLanguage()))
            $translator->setLocale($config['resources']['locale']['default']);

        Zend_Registry::set('Zend_Translate', $translator);
        Zend_Form::setDefaultTranslator($translator);
    }

    public function _initElasticSearch()
    {
        $es = new Elastica_Client();
        Zend_Registry::set('es', $es);
    }

    protected function _initView()
    {
        // Initialize view
        $view = new Zend_View();
        $view->setLfiProtection(false);
        $view->addBasePath(APPLICATION_PATH . '/views');

        // Set base head title
        $view->headTitle(Zend_Registry::get('configObj')->app->name);
        $view->headTitle()->setSeparator(' - ');
        
        // Add base scripts and stylesheets
        $baseJs = Zend_Registry::get('configObj')->app->baseJs->toArray();
        $baseCss = Zend_Registry::get('configObj')->app->baseCss->toArray();

        foreach($baseJs as $file)
        {
            $url = Zend_Registry::get('configObj')->app->cdnUrl == '' ? $view->baseUrl($file) : Zend_Registry::get('configObj')->app->cdnUrl . $file;
            $view->headScript()->appendFile($url);
        }

        foreach($baseCss as $file)
        {
            $url = Zend_Registry::get('configObj')->app->cdnUrl == '' ? $view->baseUrl($file) : Zend_Registry::get('configObj')->app->cdnUrl . $file;
            $view->headLink()->appendStylesheet($url);
        }

        // Add it to the ViewRenderer
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);
        
        // Paginator
        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginator.phtml');

        return $view;
    }

    public function _initJson()
    {
        Zend_Json::$useBuiltinEncoderDecoder = true;
    }
}