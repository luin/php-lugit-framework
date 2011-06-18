<?php
class FromHelper extends Singleton
{
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


    public function text($name, $properties, $defaultValue)
    {
        if ($defaultValue &&)
            return '<input type="text" name="' . $name . '"' . $this->_parseProperties($properties) . ' />';
    }


}