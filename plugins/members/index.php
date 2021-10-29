<?php

use App\Breadcrumbs as Breadcrumbs;
use App\Config;
use App\File;
use App\Flash;
use App\Helper;
use App\Log as Log;
use App\ObjectType;
use App\Stats;
use App\Security\Captcha;
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


/** @var Helper $helper */

if (!defined('PLUGINS_FILE')) {
    echo _NO_ACCESS_DIV_TEXT;
    $helper->redirect('index.php');
}

require_once APP_DIR.'/class.object_type.php';

$plugin_name = basename(dirname(__FILE__));
$helper->get_plugin_lang($plugin_name);

# Class inclusion
require_once('app/class.validator.php');
if (class_exists('App\Validator')) {
    $validate = new App\Validator();
}
require_once('app/security/class.captcha.php');
if (class_exists('App\Security\Captcha')) {
    $captcha = new App\Security\Captcha();
}

require_once('plugins/pages/addons/gallery/classes/class.gallery.php');
$gallery = new Plugins\Pages\Addons\Gallery\Classes\Gallery;

if (class_exists('App\Social\Facebook\Handler')) {
    $facebook = new App\Social\Facebook\Handler([
        'appId' => _FACEBOOK_APP_ID,
        'secret' => _FACEBOOK_APP_KEY
    ]);
    $fb_user = $facebook->getUser();
}

# Members settings
$members_settings = $settings->get($plugin_name);

require_once 'app/class.config.php';
$config = new Config();

// Get User ID

if ($fb_user) {
    try {
        // Proceed knowing you have a logged in user who's authenticated.
        $user_profile = $facebook->api('/me');
        $member_info = $members->get_user_by_email($user_profile['email']);

        if (empty($member_info['id'])) {
            $members->insert_social_user($helper->filter($user_profile, 1, 1));
        } else {
            $_SESSION['user_info'] = $member_info;
        }
    } catch (FacebookApiException $e) {
        $fb_user = null;
    }
}

switch ($_REQUEST['op']) {
    default:

        if (!empty($_REQUEST['url'])) {
            unset($_REQUEST['op']);
            $item = $members->get_items_from_url($_REQUEST['url']);
            $id = (int) $item['id'];

            if (empty($item)) {
                Flash::set('error', 'The profile you are trying to see does not exists, please try another.', 'members');
            }

            // Track page view
            (new Stats())->track(ObjectType::PROFILE, $item['id']);

            # Meta Information
            $meta['name'] = $item['first_name'] . " " . $item['last_name'];
            $meta['title'] = $item['first_name'] . " " . $item['last_name'];
            $meta['keywords'] = $item['tags'];
            $meta['description'] = $item['display_name'].': '.$item['bio'];
            $meta['canonical'] = $item['url'];

//            $addresses = $members->get_addresses($id);
            $addresses = $item; // The address data is in the customer itself

            require_once('app/class.breadcrumbs.php');
            $breadcrumbs = new Breadcrumbs([$item['url'] => $item['title']]);
            
            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.member.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } else {
            $helper->redirect('search');
        }

    break;

    case 'dashboard':

        try {
            if (!$members->is_user()) {
                $meta['title'] = _MEMBERS_LOGIN_PAGE_TITLE;
                $meta['name'] = _MEMBERS_MAIN_MY_ACCOUNT;

                $fb_login_url = $facebook ?
                    $facebook->getLoginUrl(['scope' => 'user_status,user_religion_politics,user_photos,email,publish_stream,user_birthday,user_about_me']) :
                    null ;

                ob_start('ob_gzhandler');
                include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.login.phtml');
                $modcontent = ob_get_clean();
                include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
            } else {
                $meta['title'] = _MEMBERS_MAIN_MY_ACCOUNT;
                $meta['name'] = _MEMBERS_MAIN_MY_ACCOUNT;

                # Check user payments
                $members->is_paid();

                ob_start('ob_gzhandler');
                include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.main.phtml');
                $modcontent = ob_get_clean();
                include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
            }
        } catch (Exception $e) {
            die('ERROR ON LINE: '.__LINE__.': '.$e->getMessage());
        }

    break;
    
    case 'sign_in':

        try {
            $username = $_SESSION['sign_in']['username'] = $helper->filter($_POST['username'], 1, 1);
            $passwd = $_SESSION['sign_in']['passwd'] = $_POST['passwd'];
            $remember = (!empty($_POST['rememberMe']))    ? 1 : 0;
            $ip = $_SERVER["REMOTE_ADDR"];
            $time = time();

            if (($HTTP_X_FORWARDED_FOR) || ($HTTP_X_FORWARD)) {
                throw new Exception(_NO_PROXY_ACCEPTED, 1);
            }
    
            if ($members_settings['captcha_on_login']) {
                if (isset($_POST['captcha_code'])) {
                    if (!$captcha->validRequest($_POST['captcha_code'])) {
                        $captcha->destroy();
                        throw new Exception(_INCORRECT_VALIDATION_CODE, 1);
                    }
                }
            }


            $members->sign_in($username, $passwd, $remember);
            unset($_SESSION['sign_in']);
            # Redirect
            $helper->redirect("members/dashboard");
        } catch (Exception $e) {
            require_once('app/class.flash.php');
            $_SESSION['error']['message'] = $e->getMessage();
            App\Flash::set('error', $e->getMessage(), 'members');
        }
        
    break;

    case 'logout':

        $members->logout();
    
        # Redirection
        $helper->redirect("members");
        
    break;

    case 'forgot':
    
        if ($members->is_user()) {
            $helper->redirect("members");
        }
    
        $meta['title'] = _MEMBERS_FORGOTTEN_PAGE_TITLE;
        $meta['name'] = _MEMBERS_FORGOTTEN_PAGE_TITLE;

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.password.forgot.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;

    case 'recover':
    
        try {
            $data = $helper->filter($_POST, 1, 1);
            $item_info = $members->get_user_by_email($data['email']);

            if ($members_settings['captcha_on_forgot']) {
                $captcha = new Captcha;
                if (isset($_POST['captcha_code'])) {
                    if (!$captcha->validRequest($_POST['captcha_code'])) {
                        $captcha->destroy();
                        throw new Exception(_INCORRECT_VALIDATION_CODE, 1);
                    }
                }
            }

            if (empty($item_info['id'])) {
                throw new Exception(_MEMBERS_FORGOTTEN_PASS_ERROR, 1);
            }
            if ($item_info['active'] != 1) {
                throw new Exception(_MEMBERS_FORGOTTEN_PASS_NO_ACTIVE, 1);
            }
        
            $data = $item_info;

            if ($members->send_password($data)) {
                $helper->redirect("members/sent");
            }
        } catch (Exception $e) {
            App\Flash::set('error', $e->getMessage(), 'members/forgot');
        }
    
    break;
        
    case 'sent':
        
        $meta['title'] = _MEMBERS_FORGOTTEN_PAGE_TITLE;
        $meta['name'] = _MEMBERS_FORGOTTEN_PAGE_TITLE;

        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.password.forgot.sent.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        
    break;

    case 'reset':
    
        try {
            if ($members->is_user()) {
                $helper->redirect("members");
            }

            $data = $helper->filter($helper->decode_var($_GET['d']), 1, 1);
            $item_info = $members->get_user_by_email($data['email']);

            if (empty($item_info['id'])) {
                throw new Exception(_MEMBERS_FORGOTTEN_PASS_LINK_ERROR, 1);
            }

            if ($data['cc'] != $item_info['cc'] || $data['tt'] != $item_info['tt']) {
                throw new Exception(_MEMBERS_FORGOTTEN_PASS_LINK_ERROR, 1);
            }

            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.password.reset.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } catch (Exception $e) {
            App\Flash::set('error', $e->getMessage(), 'members');
        }
    break;
    
    case 'go_reset_password':
    
        try {
            if ($members->is_user()) {
                $helper->redirect("members");
            }
            
            $data = $helper->filter($_POST, 1, 1);

            # Check for errors
            if ((empty($data['email'])) || (empty($data['cc'])) || (empty($data['tt']))) {
                throw new Exception(_MEMBERS_PASSWDRESET_LINKS_ERROR, 1);
            }
            if (empty($data['passwd']) || $data['passwd'] != $data['passwd2']) {
                throw new Exception(_INCORRECT_PASSWORD_DONOT_MATCH, 1);
            }

            # Reset Password
            $members->reset_password($data['email'], $data['passwd']);

            # Automatically sign in user
            $members->sign_in($data['email'], $data['passwd']);
    
            # Redirection
            $helper->redirect("members");
        } catch (Exception $e) {
            App\Flash::set('error', $e->getMessage(), 'back');
        }
    
    break;
    
    case 'register':

        try {
            if ($members->is_user()) {
                $helper->redirect("members");
            }
        
            $meta['title'] = _MEMBERS_REGISTRATION_PAGE_TITLE;
            $meta['name'] = _MEMBERS_REGISTRATION_PAGE_TITLE;
            $country = (isset($_SESSION['register']['country'])) ? $_SESSION['register']['country']: 'US';
        
            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.register.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } catch (Exception $e) {
            die('ERROR ON LINE: '.__LINE__.': '.$e->getMessage());
        }

    break;

    case 'getlisted':
    
        try {
            if ($members->is_user()) {
                $helper->redirect("members");
            }
        
            $meta['title'] = _MEMBERS_PROFESSIONALS_REGISTRATION_PAGE_TITLE;
            $meta['name'] = _MEMBERS_PROFESSIONALS_REGISTRATION_PAGE_TITLE;
            $country = (isset($_SESSION['register']['country'])) ? $_SESSION['register']['country']: 'US';
        
            ob_start('ob_gzhandler');
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.register.getlisted.phtml');
            $modcontent = ob_get_clean();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');
        } catch (Exception $e) {
            die('ERROR ON LINE: '.__LINE__.': '.$e->getMessage());
        }

    break;

    case 'save':

        try {
            //  START TRANSACTION
            $members->begin_transaction();

            if (isset($_REQUEST['honey']) && !empty($_REQUEST['honey'])) {
                break;
            }
            unset($_SESSION['register']);

            $data = $helper->filter($_POST, 1, 1);

            $certifications = File::extractFromRequest($_FILES['files']);

            $is_professional = ((int) $data['professional'] === 1) && ($data['intention'] === 'get_listed');

            foreach ($data as $data_key => $data_value) {
                if ($data_key === 'phone' || $data_key === 'fax') {
                    $phone = $helper->phone($data_value);
                    $data[$data_key] = $phone;
                    $_SESSION['register'][$data_key] = $phone;
                } elseif ($data_key === 'gender' || $data_key === 'first_name' || $data_key === 'last_name' || $data_key === 'company') {
                    $data[$data_key] = ucwords($data_value);
                    $_SESSION['register'][$data_key] = ucwords($data_value);
                } elseif ($data_key === 'birthday') {
                    $data[$data_key] = date('Y-m-d', strtotime($data['birthday']));
                    $_SESSION['register']['birthday'] = $data[$data_key];
                } elseif ($data_key === 'address') {
                    $data['address'] = ucwords($data['address']);
                    $_SESSION['register'][$data_key] = ucwords($data_value);
                } elseif ($data_key === 'suite') {
                    $_SESSION['register'][$data_key] = $data_value;
                } elseif ($data_key === 'city') {
                    $_SESSION['register'][$data_key] = ucwords($data_value);
                } elseif ($data_key === 'state') {
                    $data['state'] = ucwords($data['state']);
                    $_SESSION['register'][$data_key] = ucwords($data_value);
                } elseif ($data_key === 'zipcode') {
                    $_SESSION['register'][$data_key] = $data_value;
                } elseif ($data_key === 'country') {
                    $_SESSION['register'][$data_key] = $data_value;
                } elseif ($data_key === 'extra') {
                    Helper::ucFirstRecursive($data_value);
                    $data['extra'] = $data_value;
                } else {
                    $_SESSION['register'][$data_key] = $data_value;
                }
            }

            # Validate data
            $validation_rules = [
                'passwd' => ['type' => 'string', 'msg' => _INCORRECT_PASSWORD, 'required' => true, 'trim' => true],
                'passwd2' => ['type' => 'set', 'msg' => _INCORRECT_PASSWORD_DONOT_MATCH, 'required' => true, 'compare' => [$_POST['passwd']]],
                'email' => ['type' => 'email', 'msg' => _INCORRECT_EMAIL_ADDRESS, 'required' => true, 'trim' => true],
                'category' => ['type' => 'string', 'msg' => _INCORRECT_CATEGORY_REQUIRED, 'required' => true, 'trim' => true],
            ];

            if ($members_settings['ask_name']) {
                $validation_rules[]['first_name'] = ['type' => 'name', 'msg' => _INCORRECT_BILLING_ADDRESS, 'required' => true, 'trim' => true];
                $validation_rules[]['last_name'] = ['type' => 'name', 'msg' => _INCORRECT_BILLING_CITY, 'required' => true, 'trim' => true];
            }
            if ($members_settings['ask_phone']) {
                $validation_rules[]['phone'] = ['type' => 'numeric', 'msg' => _INCORRECT_PHONE, 'required' => true, 'min' => 4, 'max' => 12, 'trim' => true];
            }
            if ($members_settings['ask_gender']) {
                $validation_rules[]['gender'] = ['type' => 'string', 'msg' => _INCORRECT_GENDER, 'required' => true, 'min' => 4, 'max' => 6, 'trim' => true];
            }
            if ($members_settings['ask_birthday']) {
                $validation_rules[]['birthday'] = ['type' => 'date', 'msg' => _INCORRECT_BDAY, 'required' => true];
            }
            if ($members_settings['ask_adults']) {
                $validation_rules[]['birthday'] = ['type' => 'age', 'msg' => _INCORRECT_NO_AGE, 'required' => true];
            }
            if ($members_settings['ask_agree']) {
                $validation_rules[]['agree'] = ['type' => 'bool', 'msg' => _INCORRECT_AGREE, 'required' => true, 'trim' => true];
            }

            $validate->add_source($data);
            $validate->add_rules($validation_rules);
            $validate->run();

            if (!empty($validate->errors)) {
                throw new Exception(reset($validate->errors), 1);
            }

            if ($members->check_user_from_email($data['email'])) {
                throw new Exception(_MEMBERS_REGISTRATION_ERROR_DUPLICATED_EMAIL, 1);
            }

            # Check if captcha was needed and filled
            if ($members_settings['captcha_on_registration']) {
                $captcha = new Captcha;
                if (isset($_POST['captcha_code'])) {
                    if (!$captcha->validRequest($_POST['captcha_code'])) {
                        $captcha->destroy();
                        throw new Exception(_INCORRECT_VALIDATION_CODE, 1);
                    }
                }
            }

            require_once('app/security/class.encrypt.php');
            $data['pwd'] = App\Security\Encrypt::encryptPasswd($_POST['passwd']);
            $data['cc'] = ($members_settings['validation_required']) ? substr(sha1($email.$time.$data['pwd']), 0, 16)     : '';
            $data['tt'] = ($members_settings['validation_required']) ? substr(sha1($time.$helper->site_domain()), 0, 9)    : '';
            $data['active'] = ($members_settings['validation_required']) ? 0 : 1;
            $data['plan'] = 0;
            $data['next_payment'] = '';
            $data['last_login'] = '';
            $data['featured'] = 0;
            $data['notes'] = '';
            $data['alive'] = 0;
            $data['approved'] = $is_professional ? 0 : 1; // If professional, by default needs approval

            $id = $members->insert($data);

            // Handle uploaded files
            if (!empty($certifications)) {

                File::storeCollection($certifications, 'get_listed');
                $saved = [];

                foreach ($certifications as $key => $file) {
                    $customer_file = [
                        'belongs' => 'member',
                        'owner' => $id,
                        'bid' => md5((string) $id),
                        'media' => $file['extension'],
                        'file_id' => $key + 1,
                        'file' => $file['name'],
                        'title' => '',
                        'description' => json_encode($file,JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR),
                        'link' => '',
                        'active' => 1,
                        'date' => date('Y-m-d'),
                    ];
                    $file_id = $members->sql_insert('customers_files', $customer_file);
                    $customer_file['id'] = $file_id;
                    unset($file_id);

                    $saved[] = $customer_file;
                }
                $certifications = $saved; // re-assign saved files with IDs
                unset($saved);
            }

            # Create user directory
            if ($members_settings['allow_avatar']) {
                $user_dir = $_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/avatar/'.md5((string) $id);

                if (!is_dir($user_dir)) {
                    mkdir($user_dir, 0777, true);
                    copy($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads/avatar/silhouette.jpg', $user_dir.'/silhouette.jpg');
                    copy($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads/avatar/thumb-silhouette.jpg', $user_dir.'/thumb-silhouette.jpg');
                    copy($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/uploads/avatar/small-silhouette.jpg', $user_dir.'/small-silhouette.jpg');
                }

                $members->save_empty_avatar($id);
            }

            // IF (USER IS PROFESSIONAL)
            //    Send email to admin for approval
            //    Send email to professional to wait for the approval
            // ELSE
            //    IF (ADVICE_ADMIN) Send email to admin
            //    IF (VALIDATION_REQUIRED) Send activation email with code

            $name = (string) $data['first_name'];
            $email = (string) $data['email'];

            #  USER IS A PROFESSIONAL
            if ($is_professional) {
//                $addr = $data['addresses'][0];

                // Prepare profile data
                $profile = [
                    'Name: ' => $data['first_name'].' '.$data['last_name'],
                    'Email: ' => $data['email'],
                    'Birth date: ' => $data['birthday'],
                    'Phone: ' => $data['phone'],
                    'Category: ' => $data['category'],
                    'License: ' => $data['license'],
                    'Company: ' => $data['company'] ?? 'N/A',
                    'Website: ' => $data['website'] ?? 'N/A',
                    'Address: ' => $data['address'].' '.$data['suite'],
                    'Location: ' => '('.$data['zipcode'].') '.$data['city'].', '.$data['state'].' - '.$data['country'],
                ];

                // Create access link for each uploaded file
                $files = [];
                foreach ($certifications as $file) {
                    $code = $members->getFileModerationCode($file['id'], $file['file']);
                    $file['access_link'] = $config('siteurl')."members/?op=view_file&access={$code}";
                    $file['name'] = $file['file'];

                    $files[] = $file;
                }
                $certifications = $files; // re-assign with activation links
                unset($files);

                $access = $members->getAccountModerationCode($id, $data['email']);
                sendAdminApprovalEmail($name, $profile, $certifications, $access);
                sendProfessionalApprovalPendingEmail($name, $email);

            } else {
                # Email administrator if needed
                if ($members_settings['advice_admin']) {
                    sendAdminAdviceEmail();
                }

                # Send Email to client
                if ($members_settings['validation_required']) {
                    $activation_code = $members->getActivationCode($id, $data['email']);
                    sendValidationEmail($name, $email, $activation_code);

                } else {
                    sendRegistrationEmail($name, $email);
                }
            }

            //  COMMIT TRANSACTION
            $members->commit();

            # Redirection
            $to = $members_settings['charge_membership'] ? 'members&file=payments' : 'members/dashboard';

            if ($is_professional) {
                App\Flash::set('error', _MEMBERS_LOGIN_PAGE_APPROVAL_PENDING, 'members/dashboard');
            } elseif (!$members_settings['validation_required']) {
                $members->sign_in($_SESSION['register']['email'], $_POST['passwd']);
            } else {
                App\Flash::set('error', _MEMBERS_LOGIN_PAGE_ACCOUNT_NOT_ACTIVE, 'members/dashboard');
            }

            $helper->redirect($to);

        } catch (Exception $e) {

            // ROLLBACK TRANSACTION
            $members->roll_back();

            require_once('app/class.flash.php');
            $url = ((int) $_POST['grouped'] === 5) ? 'members/getlisted' : 'members/register';

            App\Flash::set('error', $e->getMessage(), $url);
        }

    break;

    case 'moderate_account':
        $url = 'members/dashboard';
        $action = strtolower($_GET['result']);

        if (!in_array($action, ['approve', 'reject'])) {
            App\Flash::set('error', 'Invalid action.', $url);
        }

        try {
            [$id, $email] = $members->parseAccountModerationCode($_GET['access']);
        } catch (Exception $exception) {
            App\Flash::set('error', $exception->getMessage(), $url);
        }

        $status = [
            'approve' => [
                'value' => 1, 
                'type' => 'success', 
                'message' => 'The account was successfully approved.'
            ],
            'reject' => [
                'value' => -1, 
                'type' => 'error', 
                'message' => 'The account has been rejected',
            ],
        ];
        $user = $members->get_user_by_email($email);

        if ($members->setAccountApprovalStatus($id, $status[$action]['value'])) {

            sendModerationResultEmail($user['first_name'], $email, $action);

            App\Flash::set($status[$action]['type'], $status[$action]['message'], $url);

        } else {
            App\Flash::set('error', 'There was an error setting the new status', $url);
        }
    
        break;

    case 'view_file':
        $url = 'members/dashboard';

        try {
            [$id, $name] = $members->parseFileModerationCode($_GET['access']);

            $file = $members->sql_get_one('customers_files','*',['id'=>$id, 'file' => $name]);

            if (!$file) {
                throw new Exception('Unknown file');
            }

            $members->downloadUserFile($file);

        } catch (Exception $exception) {
            App\Flash::set('error', $exception->getMessage(), $url);
        }

        break;
    
    case 'registration_done':

        $meta['title'] = _MEMBERS_REGISTRATION_PAGE_TITLE;
        $meta['name'] = _MEMBERS_REGISTRATION_PAGE_TITLE;

        if (!empty($_GET['validation']) && $_GET['validation'] === 'done') {
            $text = _MEMBERS_REGISTRATION_DONE;
        } else {
            $text = ($members_settings['validation_required']) ? _MEMBERS_REGISTRATION_TO_VALIDATE : _MEMBERS_REGISTRATION_DONE;
        }
    
        ob_start('ob_gzhandler');
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/layout.registration.done.phtml');
        $modcontent = ob_get_clean();
        include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout.php');

    break;
    
    case 'validate_email':

        if (!isset($_GET['code'])) {
            App\Flash::set('error', 'The link used looks to be broken or missing. Please try again or contact support.', "members/register");
        }

        try {
            [$id, $email] = $members->parseActivationCode($_GET['code']);
        } catch (Exception $exception) {
            App\Flash::set('error', $exception->getMessage(), "/");
        }

        $exists = $members->get_validation_data($id);
        $user_data = $members->get_user_by_email($email);

        if (!$user_data || $user_data['email'] !== $email) {
            App\Flash::set('error', 'We are sorry but there is an issue with your validation, please try to register again.', "members/register");

        } else {
            $members->manage_activation($id);

            try {
                $members->auto_sign_in($user_data['email'], $user_data['pwd']);
            } catch (Exception $exception) {
                App\Flash::set('error', $exception->getMessage(), 'members/dashboard');
            }

            if ($members_settings['charge_membership']) {
                App\Flash::set('success', 'Your email has been validated.', "members/payments");
            } else {
                App\Flash::set('success', 'Your email has been validated.', "members");
            }
        }

    break;
    
    case 'check_username':

        $username = $helper->filter($_GET['username'], 1, 1);

        if (!empty($email)) {
            if ($members->check_user_from_username($username)) {
                echo  '<span class="label label-warning">'._MEMBERS_REGISTRATION_ERROR_DUPLICATED_USERNAME.'</span>';
            }
        }
        
    break;

    case 'check_email':

        $email = $helper->filter($_GET['email'], 1, 1);

        if (!empty($email)) {
            if ($members->check_user_from_email($email)) {
                echo  '<span class="label label-warning">'._MEMBERS_REGISTRATION_ERROR_DUPLICATED_EMAIL.'</span>';
            }
        }

    break;

    case 'contact':

        if (!empty($_GET['id'])) {
            unset($_POST['token'], $_POST['honey']);

            $data = $helper->filter($_POST, 1, 1);
            $item = $members->get_items_info($_GET['id']);

            # Validation
            require_once('app/class.validator.php');
            $validate = new Validator();
            $validation_rules = [
                'email' => ['type' => 'email', 'msg' => _INCORRECT_EMAIL_ADDRESS, 'required' => true, 'trim' => true],
                'name' => ['type' => 'name', 'msg' => _INCORRECT_NAME_NAME, 'required' => true, 'trim' => true],
                'phone' => ['type' => 'phone', 'msg' => _INCORRECT_PHONE, 'required' => true, 'trim' => true],
                'message' => ['type' => 'string', 'msg' => _ERROR_INCORRECT_MESSAGE, 'required' => true, 'trim' => true]
            ];

            $captcha = new Captcha;
            if (isset($_POST['captcha_code'])) {
                if (!$captcha->validRequest($_POST['captcha_code'])) {
                    $captcha->destroy();
                    Flash::set('error', _INCORRECT_VALIDATION_CODE, 'back');
                }
            }

            $validate->add_source($_POST);
            $validate->add_rules($validation_rules);
            $validate->run();

            if (!empty($validate->errors)) {
                foreach ($validate->errors as $error) {
                    Flash::set('error', $error, 'back');
                }
            }

            ob_start();
            include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout/emails/layout.contact.emails.phtml');
            $mssg_body = ob_get_clean();

            $helper->send_emails('New Contact from Bodyologists.com', $mssg_body, $data['email'], $data['name'], $item['email'], $item['full_name']);

            Flash::set('success', 'Your message was sent.', 'back');
        } else {
            Flash::set('error', 'You need to select a professional to proceed', 'members');
        }

    break;

    case 'delete_image':

        $id = (int) $_GET['id'];
        $item_info = $gallery->get_item_info($id);
        
        if ((int) $item_info['owner'] == (int) $_SESSION['user_info']['id']) {
            $gallery->delete_image($id);

            foreach (glob($_SERVER["DOCUMENT_ROOT"]._SITE_PATH.'/uploads/'.$item_info['belongs'].'/'.$item_info['bid'].'/*'.$item_info['image']) as $file) {
                @unlink($file);
            }
            
            new Log("[user: ".$_SESSION['user_info']['full_name']."] Deletion request for 'uploads/".$item_info['belongs']."/".$item_info['bid']."/".$item_info['image']."' has been executed");
            $helper->json_response(['answer' => 'done', 'message' => 'uploads/'.$item_info['belongs'].'/'.$item_info['bid'].'/'.$item_info['image']]);
        }

    break;

    case 'set_availability':

        $members->set_availability();
        $o = (int) $_GET['o'];
        $offset = ($o > 0) ? (($o/60)*-1) : (($o*-1)/60);
        $available = $offset + 2; // ADD 2 hours
        $available = ($available <= 0) ? $available : '+'.$available;
        $time = date('g:i A', strtotime($available.' hours'));

        Flash::set('success', 'Your availability has been set until '.$time, 'back');
    break;
}

function loadEmailTemplate(string $template_name, array $args = []): string
{
    global $config; // Needed for the templates to pull in config values

    extract($args, EXTR_OVERWRITE);

    ob_start('App\Router::mod_rewrite');
    include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/plugins/members/layout/emails/'.$template_name);

    return ob_get_clean();
}

/**
 * @param string $name
 * @param array $profile
 * @param array $files
 */
function sendAdminApprovalEmail(string $name, array $profile, array $files, string $access): void
{
    global $config;

    $url = $config('siteurl')."members/?access={$access}&op=moderate_account&result=";
    $args = [
        'first_name' => $name,
        'profile' => $profile,
        'certifications' => $files,
        'links' => ['approve' => $url.'approve', 'reject' => $url.'reject'],
    ];
    $email_subject = "New professional wants to get listed at {$config('sitename')}";
    $email_body = loadEmailTemplate('layout.professional_approval.email.phtml', $args);

    (new App\Helper())->send_emails(
        $email_subject,
        $email_body,
        '',
        '',
        $config('membersemail'),
        $config('sitename')
);
}

/**
 * @param string $name
 * @param string $email
 */
function sendProfessionalApprovalPendingEmail(string $name, string $email): void
{
    global $config;

    $email_subject = "Your request to get listed at {$config('sitename')}";
    $email_body = loadEmailTemplate('layout.approval_pending.email.phtml', ['name' => $name]);

    (new App\Helper())->send_emails($email_subject, $email_body, '', '', $email, $name);
}


/**
 *
 */
function sendAdminAdviceEmail(): void
{
    global $config;

    $email_subject = "New client registration at {$config('sitename')}";
    $email_body = loadEmailTemplate('layout.advice.admin.phtml');

    (new App\Helper())->send_emails(
        $email_subject,
        $email_body,
        '',
        '',
        $config('membersemail'),
        $config('sitename')
    );
}

/**
 * @param string $name
 * @param string $email
 * @param string $activation_code
 */
function sendValidationEmail(string $name, string $email, string $activation_code): void
{
    global $config;

    $activation_link = $config('siteurl')."members/validate_email?code={$activation_code}"; //expected in template
    $email_subject = str_replace(
        ['#name', '#sitename'],
        [$name, $config('sitename')],
        _MEMBERS_EMAIL_VALIDATION_SUBJECT
    );
    $args = [
        'name' => $name,
        'activation_link' => $activation_link,
    ];
    $email_body = loadEmailTemplate('layout.validation.email.phtml', $args);

    (new App\Helper())->send_emails($email_subject, $email_body, '', '', $email, $name);
}

/**
 * @param string $name
 * @param string $email
 */
function sendRegistrationEmail(string $name, string $email): void
{
    global $config;

    $email_subject = str_replace(
        ['#name', '#sitename'],
        [$name, $config('sitename')],
        _MEMBERS_EMAIL_REGISTRATION_SUBJECT
    );
    $email_body = loadEmailTemplate('layout.registration.email.phtml');

    (new App\Helper())->send_emails($email_subject, $email_body, '', '', $email, $name);
}

/**
 * @param string $name
 * @param string $email
 * @param string $result
 */
function sendModerationResultEmail(string $name, string $email, string $result): void
{
    global $config;

    $args = ['name' => $name, 'status' => $result];
    $email_subject = "Update on your request to get listed at {$config('sitename')}";
    $email_body = loadEmailTemplate('layout.approval_result.email.phtml', $args);

    (new App\Helper())->send_emails(
        $email_subject, $email_body,
        '',
        '',
        $email,
        $name,
        $config('membersemail'),
        $config('sitename')
    );
}