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

require_once('mainfile.php');
global $modcontent, $meta;

$ASKAPACHE_S_C = [
    '400' => [
        'Bad Request',
        'Your browser sent a request that this server could not understand.'
    ],
    '401' => [
        'Authorization Required',
        'This server could not verify that you are authorized to access the document requested. Either you supplied the wrong credentials (e.g., bad password), or your browser doesn\'t understand how to supply the credentials required.'
    ],
    '402' => [
        'Payment Required',
        'INTERROR'
    ],
    '403' => [
        'Forbidden',
        'You don\'t have permission to access THEREQUESTURI on this server.'
    ],
    '404' => [
        'Page Not Found',
        'We could not find <acronym title="THEREQUESTURI">that uri </acronym> on our server, though it is most certainly not your fault.'
    ],
    '405' => [
        'Method Not Allowed',
        'The requested method THEREQMETH is not allowed for the URL THEREQUESTURI.'
    ],
    '406' => [
        'Not Acceptable',
        'An appropriate representation of the requested resource THEREQUESTURI could not be found on this server.'
    ],
    '407' => [
        'Proxy Authentication Required',
        'This server could not verify that you are authorized to access the document requested. Either you supplied the wrong credentials (e.g., bad password), or your browser doesn\'t understand how to supply the credentials required.'
    ],
    '408' => [
        'Request Time-out',
        'Server timeout waiting for the HTTP request from the client.'
    ],
    '409' => [
        'Conflict',
        'INTERROR'
    ],
    '410' => [
        'Gone',
        'The requested resourceTHEREQUESTURIis no longer available on this server and there is no forwarding address. Please remove all references to this resource.'
    ],
    '411' => [
        'Length Required',
        'A request of the requested method GET requires a valid Content-length.'
    ],
    '412' => [
        'Precondition Failed',
        'The precondition on the request for the URL THEREQUESTURI evaluated to false.'
    ],
    '413' => [
        'Request Entity Too Large',
        'The requested resource THEREQUESTURI does not allow request data with GET requests, or the amount of data provided in the request exceeds the capacity limit.'
    ],
    '414' => [
        'Request-URI Too Large',
        'The requested URL\'s length exceeds the capacity limit for this server.'
    ],
    '415' => [
        'Unsupported Media Type',
        'The supplied request data is not in a format acceptable for processing by this resource.'
    ],
    '416' => [
        'Requested Range Not Satisfiable',
        ''
    ],
    '417' => [
        'Expectation Failed',
        'The expectation given in the Expect request-header field could not be met by this server. The client sent <code>Expect:</code>'
    ],
    '422' => [
        'Unprocessable Entity',
        'The server understands the media type of the request entity, but was unable to process the contained instructions.'
    ],
    '423' => [
        'Locked',
        'The requested resource is currently locked. The lock must be released or proper identification given before the method can be applied.'
    ],
    '424' => [
        'Failed Dependency',
        'The method could not be performed on the resource because the requested action depended on another action and that other action failed.'
    ],
    '425' => [
        'No code',
        'INTERROR'
    ],
    '426' => [
        'Upgrade Required',
        'The requested resource can only be retrieved using SSL. The server is willing to upgrade the current connection to SSL, but your client doesn\'t support it. Either upgrade your client, or try requesting the page using https://'
    ],
    '500' => [
        'Internal Server Error',
        'INTERROR'
    ],
    '501' => [
        'Method Not Implemented',
        'GET to THEREQUESTURI not supported.'
    ],
    '502' => [
        'Bad Gateway',
        'The proxy server received an invalid response from an upstream server.'
    ],
    '503' => [
        'Service Temporarily Unavailable',
        'The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later.'
    ],
    '504' => [
        'Gateway Time-out',
        'The proxy server did not receive a timely response from the upstream server.'
    ],
    '505' => [
        'HTTP Version Not Supported',
        'INTERROR'
    ],
    '506' => [
        'Variant Also Negotiates',
        'A variant for the requested resource <code>THEREQUESTURI</code> is itself a negotiable resource. This indicates a configuration error.'
    ],
    '507' => [
        'Insufficient Storage',
        'The method could not be performed on the resource because the server is unable to store the representation needed to successfully complete the request. There is insufficient free space left in your storage allocation.'
    ],
    '510' => [
        'Not Extended',
        'A mandatory extension policy in the request is not accepted by the server for this resource.'
    ]
];

$meta['title'] = 'ERROR - '.$ASKAPACHE_S_C[$_SERVER['REDIRECT_STATUS']][0];

ob_start();
?>
    <div class="row-fluid">
        <div style="margin: 20px 0;">
            <h1><?=$_SERVER['REDIRECT_STATUS']?> - <?=$ASKAPACHE_S_C[$_SERVER['REDIRECT_STATUS']][0]?></h1>
        </div>
        <p class="well">
            <a href="javascript:history.go(-1);"><span class="icon-link tipS" style="font-size: 25px; position: relative; top: 5px; padding-right: 10px;" title="Go back..."></span></a> <?=$ASKAPACHE_S_C[$_SERVER['REDIRECT_STATUS']][1]?>
        </p>
    </div>
<?php
$modcontent = ob_get_contents();
ob_end_clean();

include("layout.php");
