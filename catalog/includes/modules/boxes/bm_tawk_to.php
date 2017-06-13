<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
class bm_tawk_to
{
    public $code = 'bm_tawk_to';
    // public $group = 'account';
    public $group;
    public $title;
    public $description;
    public $sort_order;
    public $enabled = false;

    function bm_tawk_to()
    {
        $this->code = get_class($this);
        $this->group = basename(dirname(__FILE__));

        $this->title = MODULE_BOXES_TAWK_TO_TITLE;
        $this->description = MODULE_BOXES_TAWK_TO_DESCRIPTION;
        if ( defined('MODULE_BOXES_TAWK_TO_STATUS') ) {
            $this->sort_order = MODULE_BOXES_TAWK_TO_SORT_ORDER;
            $this->enabled = (MODULE_BOXES_TAWK_TO_STATUS == 'True');
        }
    }

    function isEnabled()
    {
        return $this->enabled;
    }

    function check()
    {
        return defined('MODULE_BOXES_TAWK_TO_STATUS');
    }

    function install()
    {
        $query = "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable tawk.to Widget', 'MODULE_BOXES_TAWK_TO_STATUS', 'True', 'Do you want to enable the tawk.to chat module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())";
        tep_db_query($query);

        $query = "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Widget Script', 'MODULE_BOXES_TAWK_TO_WIDGET', '', 'The tawk.to widget script', '1', '0', 'bm_tawk_to_widget(\'\',',now())";
        tep_db_query($query);

        $query = "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Key', 'MODULE_BOXES_TAWK_TO_API_KEY', '', 'Your tawk.to API Key', '6', '0', now())";
        tep_db_query($query);

        $query = "insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_TAWK_TO_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())";
        tep_db_query($query);
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys()
    {
        return array(
                'MODULE_BOXES_TAWK_TO_STATUS',
                'MODULE_BOXES_TAWK_TO_WIDGET',
                'MODULE_BOXES_TAWK_TO_API_KEY',
                'MODULE_BOXES_TAWK_TO_SORT_ORDER'
            );
    }

    function execute()
    {
        global $PHP_SELF, $HTTP_GET_VARS;
        global $oscTemplate, $request_type;
        global $languages_id, $currencies, $currency, $customer_id;
        $blockGroup = 'footer_scripts';

        $query = 'SELECT * FROM '.TABLE_CONFIGURATION.' WHERE configuration_key="MODULE_BOXES_TAWK_TO_WIDGET" LIMIT 1';
        $result = tep_db_query($query);
        if ( tep_db_num_rows($result) === 1 ) {
            $widget = tep_db_fetch_array($result);
            $script .= $widget['configuration_value'];
            
            if (tep_session_is_registered('customer_id') && tep_session_is_registered('customer_first_name')) {
                $query = "select * from " . TABLE_CUSTOMERS . " where customers_id = '" . tep_db_input($customer_id) . "' limit 1";
                $query_result = tep_db_query($query);
                if (tep_db_num_rows($query_result)) {
                    $customer = tep_db_fetch_array($query_result);
                    
                    $api_string = 'Tawk_API.visitor = {
                        name  : "'.$customer['customers_firstname'].' '.$customer['customers_lastname'].'",
                        email : "'.$customer['customers_email_address'].'",
                    };
                    </script>';
                    $script = str_ireplace('</script>', $api_string, $script);
                }
            }
            
            $oscTemplate->addBlock($script, $blockGroup);
        }
    }
}

function bm_tawk_to_widget($text, $key_value, $key = '')
{
    global $PHP_SELF;
    // return tep_draw_textarea_field('configuration_value', false, 35, 5, '');
    // <textarea name=\'configuration[MODULE_BOXES_TAWK_TO_WIDGET]\' cols=\'35\' rows=\'5\'></textarea>
    // $output = '';
    // $output .= tep_draw_textarea_field('configuration[MODULE_BOXES_TAWK_TO_WIDGET]', false, 35, 5, '');

    // return $output;

    $output = '';
    $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');
    $output .= '<br /><textarea name="' . $name . '" cols="35" rows="5">'.$key_value.'</textarea>';
    return $output;
}
