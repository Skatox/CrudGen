<?php
/**
 * Class to include personal methods to make easier
 * the development of the CRUD plugin
 *
 */
class CrudPlugin extends Plugin
{
	/**
	 * Constructor
	 */
	public function __construct( $language )
	{
		parent::__construct( $language );
	}

	function get_hooks()
    {
        return NULL;
    }

    function get_actions()
    {
    	return NULL;
    }
    
	/**
	 * Builds an internal link array to simply code¡¡¡¡
	 */
	protected function build_link( $action, $extra_vars = array() )
	{
		global $misc;

		$urlvars = $misc->getRequestVars();

		return array (
			'href'=>array(
				'url' =>'plugin.php',
				'urlvars' => array_merge( $urlvars, array(
						'plugin' => $this->name,
						'action' => $action,
					), $extra_vars ),
			)
		);
	}

	/**
	 * Builds an external link array to simply code
	 */
	protected function build_nav_link( $url, $action, $content, $extra_vars = array() )
	{
		$content = html_entity_decode( $content ); //to support spanish accents
		$urlvars = array(
			'action' => $action,
			'server' => field( 'server' ),
			'subject' => field( 'subject' ),
			'database' => field( 'database' ),
			'schema' => field( 'schema' ),
		);

		return array(
			'attr' => array( 'href' => array( '
                    url' => $url,
					'urlvars' => array_merge( $urlvars, $extra_vars ) )
			),
			'content' => $content
		);
	}

	/**
	 * Builds a plugin link array to simply code
	 */
	protected function build_plugin_link( $action, $content )
	{
		return array(
			'attr' => array( 'href' => array(
					'url' => 'plugin.php',
					'urlvars' => array(
						'plugin' => $this->name,
						'action' => $action,
						'server' => field( 'server' ),
						'subject' => field( 'subject' ),
						'database' => field( 'database' ),
						'schema' => field( 'schema' ),
					)
				) ),
			'content' => $content
		);
	}

	/**
	 * Prints a table cell with a checkbox for selecting an operation
	 *
	 * @param unknown $operation  the operation for this cell
	 * @param unknown $rowClass   row's class name
	 * @param unknown $table_name name of the table where the field is
	 * @param unknown $field      name   of the column where the operation will be applied to
	 * @param unknown $selected   variable to see if the checkbox is selected or not
	 */
	protected function printOperationTableCell( $operation, $rowClass, $table_name, $field )
	{

		echo "<td class=\"{$rowClass}\" style=\"text-align: center;\">";
		echo "<input type=\"checkbox\" name=\"{$operation}[{$table_name}][]\" value=\"{$field}\"";
		if ( isset( $_SESSION[$operation] ) )
			if ( isset( $_SESSION[$operation][$table_name] ) )
			{
				if ( count( $_SESSION[$operation][$table_name] ) > 0 )
				{
					foreach ( $_SESSION[$operation][$table_name] as $operation )
					{
						if ( $operation == $field )
							echo ' checked="checked" ';
					}
				}
			}
		echo "/></td>";
	}

	/**
	 * Print comboxes depending on how many fields works with this page
	 *
	 * @param unknown $page           page to print its fields
	 * @param unknown $selected_field index of selected field
	 */
	protected function printOptionsField( Page $page, $selected_field )
	{
		$fields_count = count( $page->fields );

		for ( $i = 1; $i <= $fields_count; $i++ )
		{
			$selected = ( $selected_field == $i ) ? ' selected="selected"' : '';
			echo "<option value=\"{$i}\" {$selected}>{$i}</option>";
		}
	}
}
?>
