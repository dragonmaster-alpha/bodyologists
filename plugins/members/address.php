<?php

/**
 * @author
 * Web Design Enterprise
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * @copyright 2002- date('Y') Web Design Enterprise Corp. All rights reserved.
 */

if (!defined('PLUGINS_FILE')) {
    echo _NO_ACCESS_DIV_TEXT;
    Header("Refresh: 5; url=index.php");
    exit();
}

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

if (!$members->is_user()) {
    $_SESSION['referer'] = $helper->format_url('index.php?'.$_SERVER['QUERY_STRING']);
    $helper->redirect('index.php?plugin=members');
}

# Check user payments
$members->is_paid();

$meta['name'] = _MEMBERS_ADDRESS_PAGE_TITLE;

switch ($_REQUEST['op']) {
    default:

        try {
            $meta['title'] = _MEMBERS_ADDRESS_PAGE_TITLE;

            $addresses = $members->get_addresses((int) $_SESSION['user_info']['id']);
        
            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT'].'/plugins/members/layout/addresses/layout.address.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } catch (Exception $e) {
            die('ERROR: '.__METHOD__.': '.$e->getMessage());
        }

    break;

    case 'add':

        if (isset($_REQUEST['honey']) && !empty($_REQUEST['honey'])) {
            break;
        }

        $meta['title'] = _MEMBERS_ADDRESS_PAGE_TITLE;

        if (isset($_REQUEST['id'])) {
            $id = (int) $_REQUEST['id'];
            $address = $members->get_address($id, $_SESSION['user_info']['id']);
        } else {
            $address = [
                'first_name' => $_SESSION['user_info']['first_name'],
                'last_name' => $_SESSION['user_info']['last_name'],
                'phone' => $_SESSION['user_info']['phone'],
                'country' => 'US'
            ];
        }

        if (!empty($_GET['return'])) {
            $_SESSION['redirect'] = $_GET['return'];
        }
        
        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/addresses/layout.address.add.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'save':

        try {
            if (isset($_REQUEST['honey']) && !empty($_REQUEST['honey'])) {
                break;
            }
            $data = $helper->filter($_POST, 1, 1);
            
            foreach ($data as $key => $val) {
                $_SESSION['address'][$key] = $val;
            }
            if (empty($data['first_name'])) {
                throw new Exception(_INCORRECT_FIRST_NAME);
            }
            if (empty($data['last_name'])) {
                throw new Exception(_INCORRECT_LAST_NAME);
            }
            if ($data['country'] == 'US' || $data['country'] == 'CA') {
                if (empty($data['address'])) {
                    throw new Exception(_INCORRECT_ADDRESS);
                }
                if (empty($data['city'])) {
                    throw new Exception(_INCORRECT_CITY);
                }
                if (empty($data['state'])) {
                    throw new Exception(_INCORRECT_STATE);
                }
                if (empty($data['zipcode'])) {
                    throw new Exception(_INCORRECT_ZIPCODE);
                }

                require_once 'app/formatters/class.addresses.php';
                $address_verification = new App\Formatters\Addresses($data['address'].' '.$data['city'].' '.$data['state'].' '.$data['zipcode']);

                if (!$address_verification->verity_address()) {
                    throw new Exception(_ADDRESS_VERIFICATION_FAIL);
                }
            } else {
                if (empty($data['address'])) {
                    throw new Exception(_INCORRECT_ADDRESS);
                }
                if (empty($data['city'])) {
                    throw new Exception(_INCORRECT_CITY);
                }
            }
            
            $data['owner'] = (int) $_SESSION['user_info']['id'];
        
            $members->save_address($data);

            unset($_SESSION['address']);

            if (isset($_SESSION['redirect'])) {
                $helper->redirect($_SESSION['redirect']);
            } else {
                $helper->redirect('index.php?plugin=members&amp;file=address');
            }
        } catch (Exception $e) {
            Kernel_Classes_Flash::set('error', $e->getMessage(), 'back');
        }

    break;

    case 'delete':

        try {
            if (empty($_REQUEST['id'])) {
                throw new Exception(_MEMBERS_ADDRESS_DELETION_ERROR, 1);
            }

            $id = (int) $_REQUEST['id'];
            $members->delete_address($id, $_SESSION['user_info']['id']);
            
            $helper->json_response(['answer' => 'done']);
        } catch (Exception $e) {
            $helper->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
        }

    break;
}
