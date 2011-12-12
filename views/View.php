<?php
class View
{
    private $vars;
    private $_blockContents = array();
    private $_blockFilters = array();
    private $_blockLevel = array();

    public function __construct($parameters)
    {
        $this->vars = $parameters;
    }

    public function render($template)
    {
        ob_start();
        $this->_include($template);
        $content = ob_get_contents();
        ob_end_clean();
        
        foreach($this->_blockContents as $k => $v) {
            $v = Singleton::getInstance('FilterModule')->applyFilters($v, $this->_blockFilters[$k]);
            $content = str_replace("_lugit_template_prefix_##{$k}##__", $v, $content);
        }
        echo $content;
    }

    public function load($template)
    {
        $this->_include("./views/layouts/{$template}.phtml");
    }


    public function block($blockName, $callbackFilters = null)
    {
        if(isset($this->_blockLevel[$blockName]) && $this->_blockLevel[$blockName] == 1) {
            throw new TemplateException(array($blockName));
            return;
        }
        ob_start();
        $this->_blockFilters[$blockName] = $callbackFilters;
        $this->_blockLevel[$blockName] = 1;
    }

    public function endblock($blockName)
    {
        if(!isset($this->_blockLevel[$blockName]) || $this->_blockLevel[$blockName] == 0) {
            throw new TemplateException(array($blockName));
            return;
        }
        $isChildern = isset($this->_blockContents[$blockName]);

        $this->_blockContents[$blockName] = ob_get_contents();
        ob_end_clean();

        if(!$isChildern) {
            echo "_lugit_template_prefix_##{$blockName}##__";
        }
        $this->_blockLevel[$blockName] = 0;
    }

    public function __get($key)
    {
        $key = ucfirst(strtolower($key));
        if(class_exists($key . 'Helper')) {
            return Singleton::getInstance($key . 'Helper');
        } else {
            return null;
        }
    }

    private function _include($name)
    {
        if(!file_exists($name)) {
            throw new NotFoundException('Cannot find the view file: ' . $name);
        }

        foreach($this->vars as $key => $var) {
            $$key = $var;
        }

        include $name;
    }
}