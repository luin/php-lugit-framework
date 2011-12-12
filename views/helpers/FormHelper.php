<?php
class FormHelper extends Singleton
{
    private $labelList = array(); 
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


    /**
     * 获得控件值.
     * 
     * @access private
     * @param mixed $name
     * @return void
     */
    private function _getValue($name, $defaultValue)
    {
        if(! $value = 
            Singleton::getInstance('ResponseModule')->request->filter('htmlspecialchars')->$name) {
            $value = $defaultValue;
        }
        $value = $value ? " value=\"$value\"" : '';
    }

    public function input($name, $properties = array(), $type, $defaultValue = null)
    {
        if(isset($this->$labelList[$name])) $properties['id'] = $name;
        return '<input type="' . $type . '" name="' . $name . '"' . 
            $this->_parseProperties($properties) . 
            $this->_getValue($name, $defaultValue) . 
            ' />';
    }

    public function text($name, $properties = array(), $defaultValue = null)
    {
        return $this->input($name, $properties, 'text', $defaultValue);
    }

    public function password($name, $properties = array(), $defaultValue = null)
    {
        return $this->input($name, $properties, 'password', $defaultValue);
    }

    public function button($value)
    {
        return '<button type="submit">' . $value . '</button>';
    }

    public function label($value, $for, $properties = null)
    {
        $this->$labelList[$for] = true;
        return '<label for="'. $for . '"' . $this->_parseProperties($properties) . '>' . $value . '</label>';
    }


}