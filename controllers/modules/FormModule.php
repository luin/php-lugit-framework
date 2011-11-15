<?php
class FormModule extends Singleton implements ArrayAccess
{
    private $_elements;
    private $_currentName;
    private $_filters = array();
    private $_validators = array();
    protected $keepData = true;

    protected function offsetExists($id)
    {
        return isset($this->_elements[$id]);
    }


    protected function offsetGet($id)
    {
        return $this->_elements[$id];
    }


    protected function offsetSet($id, $element)
    {
        $this->_elements[$id] = $element;
    }


    protected function offsetUnset($id)
    {
        unset($this->_elements[$id]);
    }


    private function _parseProperties($properties)
    {
        $result = '';
        if (!empty($properties) && is_array($properties)) {
            foreach ($properties as $key => $value) {
                $result .= " $key=\"$value\"";
            }
        }
        return $result;
    }


    private function _getRequest($name)
    {
        return Singleton::getInstance('ResponseModule')->request->filter('htmlspecialchars')->$name;
    }


    public function text($name, $properties = null)
    {
        if (is_string($properties)) {
            $properties = array('value'=>$properties);
        }
        if ($this->keepData && isset($this->_getRequest($name))) {
            $properties['value'] = $this->_getRequest($name);
        }
        $this->$elements[$name] = '<input type="text" name="' . $name . '"' . $this->_parseProperties($properties) . ' />';
        $this->_currentName = $name;
        return $this;
    }


    public function addFilter()
    {
        $filters = func_get_args();

        foreach ($filters as $filter) {
            $this->_filters[$this->_currentName][] = $filter;
        }

        return $this;
    }
    
    public function addValidator()
    {
        $validators = func_get_args();

        foreach ($validators as $validator) {
            $this->_validators[$this->_currentName][] = $validator;
        }

        return $this;
    }


}