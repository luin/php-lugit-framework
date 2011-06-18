<?php
class ViewLoader
{
	private $vars;
	private $viewFilePath;
	public function __construct($parameters, $viewFilePath)
	{
		$this->vars = $parameters;
		$this->viewFilePath = $viewFilePath;
	}

	public function render()
	{
		if(!file_exists($this->viewFilePath)) {
			throw new exception('Cannot find the view file.');
		}
		include $this->viewFilePath;
	}

	public function __get($key)
	{
		if(class_exists($key . 'Helper')) {
			return Singleton::getInstance($key . 'Helper');
		} else {
			return null;
		}
	}

}