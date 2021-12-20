<?php
/**
 * @api {post} /admin/accounts/user/create Create
 * @apiGroup Admin\User
 * @apiName CreateUser
 * @apiVersion 1.0.39
 * @apiDescription Creates a new user account if few pre-defined conditions are met
 * @apiPermission admin
 *
 * @apiBody {Number} type=1:1 The type of account to be created (Default: 1) [1=Buyer, 2=Merchant, 3=Delivery Partner, 4=Administrator].
 * @apiBody {String} uname:amanda The username of the account.
 * @apiBody {String} password:12345 The password of the account.
 * @apiBody {String} fname:Amanda First name of the user.
 * @apiBody {String} [mname] Middle name of the user (if any).
 * @apiBody {String} lname:Jones Last name of the user.
 * @apiBody {String} sex:Female Gender/Sex of the user.
 * @apiBody {String} dob:"December 20, 2020" Date of Birth of the user.
 * @apiBody {String} mobile:"+919876543210" User mobile number.
 * @apiBody {String} email:"amanda@demo.indiosco.com" User E-mail address.
 * @apiBody {Number} status=1:1 The initial status of the account (Default: 1) [0=Pending Activation, 1=Active, 2=Suspended].
 *
 * @apiExamples [http:HTTP;jq:jQuery (Ajax);java:Java;php_curl:PHP (cURL);curl:CURL]
 * @apiUse MCFAPICreateResponse
 */

use MyChhotaFarm\Accounts\MCFAccounts;
use MyChhotaFarm\Accounts\Users;
use MyChhotaFarm\API\MCFAPI;

$access_control_allow_methods = 'POST';

require_once dirname(__DIR__, 5) . "/config/api.config.php";

$Users = new Users();

$type = post('type');
$uname = post('uname');
$password = post('password');
$fname = post('fname');
$mname = post('mname');
$lname = post('lname');
$sex = post('sex');
$dob = post('dob');
$mobile = post('mobile');
$email = post('email');
$status = post('status');

$MCFAPI->checkInputs(MCFAPI::MCFAPI_CHECK_INPUT_NOTEMPTY, ['Username', 'Password', 'First name', 'Last name'], $uname, $password, $fname, $lname); //101
if(!$Users->isValidAccountType($type))
	$MCFAPI->generateResponse(400, 'common.arg_invalid', true, 'Account type');
$status = (!$Users->isValidAccountStatus($status)) ? MCFAccounts::MCF_ACCOUNT_STATUS_ACTIVE : $status;

if($Users->createUser($type, $uname, $password, $fname, $lname, $sex, $dob, $mname, $mobile, $email, $status)) {
	$MCFAPI->generateResponse(201, 'users.create_success', false); //200
} else {
	$MCFAPI->generateResponse(500, 'users.create_failed', true, $uname); //102
}