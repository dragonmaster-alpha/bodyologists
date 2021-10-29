<?php

use App\Flash as Flash;
use App\Security\Captcha;
use App\Validator as Validator;
use App\Config;

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

require_once('mainfile.php');

$config = new Config();

try {
    $email = $_POST['email'] ?? null;
    $name = (!empty($_POST['name'])) ?
        $_POST['name'] :
        $_POST['first_name'].' '.$_POST['last_name'];

    $subject = (!empty($_POST['subject'])) ?
        str_replace('_', ' ', $_POST['subject']) :
        'Contact email from '.ucwords($config('domain'));

    unset($_POST['csrf']);

    if (!empty($_POST['method'])) {
        $method = $_POST['method'];
        unset($_POST['method']);
    }

    // HONEYPOT CATCH
    if (!empty($_POST['honey'])) {
        throw new Exception('Extra field seemed to have been filled', 1);
    }

    foreach ($_POST as $key => $value) {
        $_SESSION['contact'][$key] = $value;
    }

    if (isset($_POST['source'])) {
        $receiver = ($_POST['source'] === 'support') ? $config('supportemail') : null ;
        unset($_POST['source']);
    }

    # Validation
    require_once('app/class.validator.php');
    $validate = new Validator();
    $validation_rules = [
        'email' => ['type' => 'email', 'msg' => _INCORRECT_EMAIL_ADDRESS, 'required' => true, 'trim' => true]
    ];

    $captcha = new Captcha;
    if (isset($_POST['captcha_code'])) {
        if (!$captcha->validRequest($_POST['captcha_code'])) {
            $captcha->destroy();
            throw new Exception(_INCORRECT_VALIDATION_CODE, 1);
        }
    }
    
    $validate->add_source($_POST);
    $validate->add_rules($validation_rules);
    $validate->run();

    if (!empty($validate->errors)) {
        throw new Exception(reset($validate->errors), 1);
    }

    ob_start();
    include($_SERVER['DOCUMENT_ROOT']._SITE_PATH.'/layout/emails/layout.contact.emails.phtml');
    $mssg_body = ob_get_clean();

    $helper->send_emails($subject, $mssg_body, $email, $name, $receiver, 'Bodyologists', $email, $name);

    unset($_SESSION['contact'], $_POST['email'], $_POST['name'], $_POST['first_name'], $_POST['last_name'], $_POST['subject']);
    
    if (!empty($method)) {
        $header->json_response(['answer' => 'success', 'clear' => true, 'message' => _EMAIL_SUCCESSFULLY_SENT]);
    } else {
        $reload = (!empty($_POST['thanks'])) ? $_POST['thanks'] : "thanks";
        $helper->redirect($reload);
    }
} catch (Exception $e) {
    if (!empty($method)) {
        $header->json_response(['answer' => 'error', 'message' => $e->getMessage()]);
    } else {
        require_once('app/class.flash.php');
        Flash::set('error', $e->getMessage(), 'back');
    }
}
