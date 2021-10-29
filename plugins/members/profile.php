<?php

use App\Flash as Flash;
use App\Security\Encrypt;
use App\Validator as Validator;

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

require_once('app/class.flash.php');

if (!defined('PLUGINS_FILE')) {
    echo _NO_ACCESS_DIV_TEXT;
    Header("Refresh: 5; url=index.php");
    exit();
}

# Class inclusion
require_once('app/class.validator.php');
$validate = new Validator;

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

if (!$members->is_user()) {
    $_SESSION['referer'] = $helper->format_url('index.php?'.$_SERVER['QUERY_STRING']);
    $helper->redirect('index.php?plugin=members');
}

# Check user payments
$members->is_paid();

switch ($_REQUEST['op'] ?? null) {
    default:

        $meta['title'] = _EDITING_PROFILE;
        $meta['name'] = _EDITING_PROFILE;

        ob_start();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/profile/layout.profile.edit.phtml');
        $modcontent = ob_get_clean();
        include("layout.php");

    break;

    case 'save':
        try {
            $data = $helper->filter($_POST, 1, 1);
            $data['pwd'] = $_POST['pwd'];
            $data['id'] = (int) $_SESSION['user_info']['id'];

            # Validate data
            $validation_rules = [
                'email' => ['type' => 'email', 'msg' => _INCORRECT_EMAIL_ADDRESS, 'required' => true, 'trim' => true],
                'first_name' => ['type' => 'name', 'msg' => _INCORRECT_FIRST_NAME, 'required' => true, 'min' => 1, 'max' => 64, 'trim' => true],
                'last_name' => ['type' => 'name', 'msg' => _INCORRECT_LAST_NAME, 'required' => true, 'min' => 1, 'max' => 64, 'trim' => true],
                'pwd' => ['type' => 'string', 'msg' => _PASSWORD_WRONG_LENGTH, 'required' => true, 'min' => 4, 'max' => 15, 'trim' => true]
            ];

            if (!Encrypt::valPasswd($_POST['pwd'], $_SESSION['user_info']['pwd'])) {
                throw new Exception(_MEMBERS_LOGIN_PAGE_USERORPASSWD_INCORRECT, 1);
            }

            if(isset($data['is_new_password']) && $data['is_new_password']){
                $data['pwd'] = $_POST['new_pwd'];
                $validation_rules['new_pwd'] = ['type' => 'string', 'msg' => _PASSWORD_WRONG_LENGTH, 'required' => true, 'min' => 4, 'max' => 15, 'trim' => true];
                $validation_rules['pwd2'] = ['type' => 'string', 'msg' => _PASSWORD_WRONG_LENGTH, 'required' => true, 'min' => 4, 'max' => 15, 'trim' => true];
                $validation_rules['pwd'] = ['type' => 'set', 'msg' => _PASSWORDS_DONOT_MATCH, 'required' => true, 'trim' => true, 'compare' => [$_POST['pwd2']]];
            }

            if ($members_settings['ask_phone']) {
                $validation_rules[]['phone'] = ['type' => 'phone', 'msg' => _INCORRECT_PHONE, 'required' => true, 'min' => 4, 'max' => 12, 'trim' => true];
            }
            if ($members_settings['ask_gender']) {
                $validation_rules[]['gender'] = ['type' => 'set', 'msg' => _MEMBERS_REGISTRATION_ERROR_INCORRECT_GENDER, 'required' => true, 'trim' => true, 'compare' => ['male', 'female']];
            }
            if ($members_settings['ask_birthday']) {
                $validation_rules[]['birthday'] = ['type' => 'date', 'msg' => _MEMBERS_REGISTRATION_ERROR_INCORRECT_DOB, 'required' => true, 'trim' => true];
            }

            $validate->add_source($data);
            $validate->add_rules($validation_rules);
            $validate->run();

            if (!empty($validate->errors)) {
                throw new Exception(reset($validate->errors), 1);
            }

            if ($members->check_user_from_email($data['email']) > 0) {
                throw new Exception(_MEMBERS_REGISTRATION_ERROR_DUPLICATED_EMAIL, 1);
            }

            if (is_array($data['insurance'])) {
                $data['insurance'] = implode(', ', $data['insurance']);
            }

            if (is_array($data['languages'])) {
                $data['languages'] = implode(', ', $data['languages']);
            }

            $members->update_newsletters_info($data);

            $members->update($data);

            $helper->redirect('members/profile');
        } catch (Exception $e) {
            Flash::set('error', $e->getMessage(), 'members/profile');
        }

    break;
}
