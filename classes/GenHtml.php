<?php
class GenHtml
{
    /**
     * Prints a html anchor link
     * @param $txt Text to show inside link
     * @param $class class of the link
     * @param $href hyperlink
     */
    static public function link($txt , $class, $href = '#')
    {
    	return "<a class=\\\"{$class}\\\" href=\\\"{$href}\\\">{$txt}</a>";
    }

    /**
     * Prints a html select 
     * @param $name select html name
     * @param $options an array with values for the combo box
     * @param $selected value of selected index
     * @return string with html code of the select
     */
    static public function select($name, $options, $selected) 
    {
        $code = "<select name=\\\"{$name}\\\">";
        
        foreach ($options as $key => $value) 
            $code .= "\n\t\t\t\t<option value=\\\"{$key}\\\" \";"
            . " if({$selected}=='{$key}') "
        	. "echo \" selected=\\\"selected\\\"\";"
            ." echo \" >{$value}</option>";

        $code .= "\n\t\t\t</select>";

        return $code;
    }

    /**
     * Generates a submit button
     * @param $name input html name
     * @param $value input's value
     * @param $id input's id
     * @return string html of the submit button
     */
    static public function submit($name, $value, $id = null)
    {
		$input_submit = "<input type=\\\"submit\\\" name=\\\"{$name}\\\" "
    		   . "value=\\\"{$value}\\\" ";

	   if(!empty($id))
	   		$input_submit .= " id=\\\"{$id}\\\" ";

    	$input_submit .=  "/>";

	   return $input_submit;
    }

    /**
     * Generates a hidden input
     * @param $name input html name
     * @param $value input's value
     * @param $id input's id
     * @return string html of the input hidden
     */
    static public function hidden($name, $value, $id = null)
    {
		$input_hidden = "<input type=\\\"hidden\\\" name=\\\"{$name}\\\" "
    		   . "value=\\\"{$value}\\\" ";

	   if(!empty($id))
	   		$input_hidden .= " id=\\\"{$id}\\\" ";

    	$input_hidden .=  "/>";

	   return $input_hidden;
    }
}
?>