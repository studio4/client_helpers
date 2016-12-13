<?php
/**
 * Indicia, the OPAL Online Recording Toolkit.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/gpl.html.
 *
 * @package Client
 * @subpackage PrebuiltForms
 * @author  Indicia Team
 * @license http://www.gnu.org/licenses/gpl.html GPL 3.0
 * @link  http://code.google.com/p/indicia/
 */

/**
 * Form for editing a scratchpad list (a list of pointers to entities in the database, e.g. a list of species or locations).
 * 
 * @package Client
 * @subpackage PrebuiltForms
 */
class iform_scratchpad_list_edit {
  
  /** 
   * Return the form metadata.
   * @return array The definition of the form.
   */
  public static function get_scratchpad_list_edit_definition() {
    return array(
      'title'=>'Enter a scratchpad list',
      'category' => 'Data entry forms',
      'description'=>'Form for creating or editing an existing scratchpad list. This allows creation of a list of ' .
          'pointers to entities in the database, e.g. a list of species or locations',
      'recommended' => true
    );
  }
  
  /**
   * Get the list of parameters for this form.
   * @return array List of parameters that this form requires.
   */
  public static function get_parameters() {   
    return array(
      array(
        'name'=>'entity',
        'caption'=>'Type of data to create a list for',
        'description'=>'Select the type of data the scratchpad list will contain. ' .
            'Currently only species or other taxa are supported.',
        'type'=>'select',
        'options'=>array(
          'taxa_taxon_list' => 'Species or other taxa',
        ),
        'required'=>true
      ),
      array(
        'name'=>'duplicates',
        'caption'=>'Duplicate handling',
        'description'=>'Select the type of data the scratchpad list will contain. ' .
          'Currently only species or other taxa are supported.',
        'type'=>'select',
        'options'=>array(
          'allow' => 'Allow duplicates',
          'highlight' => 'Allow duplicates but highlight them',
          'warn' => 'Allow duplicates but warn when they occur',
          'disallow' => 'Disallow duplicates',
        ),
        'default' => 'highlight',
        'required'=>true
      ),
      array(
        'name'=>'filters',
        'caption'=>'Filters for search query',
        'description'=>'Additional filters to apply to the search query, e.g. taxon_list_id=&lt;n&gt; to limit to a ' .
            'single list. Key=value pairs, one per line',
        'type'=>'textarea',
        'required'=>false
      ),
    );
  }
  
  /**
   * Return the generated form output.
   * @param array $args List of parameter values passed through to the form depending on how the form has been configured.
   * This array always contains a value for language.
   * @param object $nid The Drupal node object's ID.
   * @param array $response When this form is reloading after saving a submission, contains the response from the service call.
   * Note this does not apply when redirecting (in this case the details of the saved object are in the $_GET data).
   * @return Form HTML.
   */
  public static function get_form($args, $nid, $response=null) {
    data_entry_helper::add_resource('fancybox');
    data_entry_helper::add_resource('jquery_form');
    $conn = iform_get_connection_details($nid);
    data_entry_helper::get_read_auth($conn['website_id'], $conn['password']);
    $filters = data_entry_helper::explode_lines_key_value_pairs($args['filters']);
    $options = array(
      'entity' => $args['entity'],
      'extraParams' => array(),
      'duplicates' => $args['duplicates'],
      'filters' => $filters,
      'ajaxProxyUrl' => iform_ajaxproxy_url(null, 'scratchpad_list'),
      'websiteId' => $args['website_id']
    );
    $saveLabel = lang::get('Save');
    $r = data_entry_helper::text_input(array(
      'fieldname' => 'scratchpad_list:title',
      'label' => lang::get('List title')
    ));
    $r .= data_entry_helper::textarea(array(
      'fieldname' => 'scratchpad_list:description',
      'label' => lang::get('List description')
    ));
    global $indicia_templates;
    $indicia_templates['scratchpad_input'] = '<div contenteditable="true" id="{id}"></div>';
    $r .= data_entry_helper::apply_template('scratchpad_input', array(
      'id' => 'scratchpad-input',
      'label' => lang::get('Enter the list of items below')
    ));
    $r .= data_entry_helper::hidden_text(array(
      'id' => 'hidden-entries-list',
      'fieldname' => 'metaFields:entries'
    ));
    $r .= data_entry_helper::hidden_text(array(
      'fieldname' => 'scratchpad_list:entity',
      'default' => $args['entity']
    ));
    $r .= <<<HTML
<button id="scratchpad-check">Check</button>
<button id="scratchpad-remove-duplicates" disabled="disabled">Remove duplicates</button>
<input type="submit" id="scratchpad-save" disabled="disabled" value="$saveLabel" />
HTML;
    data_entry_helper::$javascript .= 'indiciaData.scratchpadSettings = ' . json_encode($options) . ";\n";
    return $r;
  }
  
  /**
   * Handles the construction of a submission array from a set of form values.
   * @param array $values Associative array of form data values. 
   * @param array $args iform parameters. 
   * @return array Submission structure.
   */
  public static function get_submission($values, $args) {
    $structure = array('model' => 'scratchpad_list', 'metaFields' => array('entries'));
    return data_entry_helper::build_submission($values, $structure);
  }
}
