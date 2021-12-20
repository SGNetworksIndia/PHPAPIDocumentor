<?php
/**
 * @api {put} /admin/accounts/user/update Update
 * @apiGroup Admin\User
 * @apiName UpdateUser
 * @apiVersion 1.0.39
 * @apiDescription Updates a user account identified by supplied Account ID (UID)
 * @apiPermission admin
 *
 * @apiBody {Number} uid The UID of the user to update.
 * @apiBody {String} uname The username of the account.
 * @apiBody {String} password The password of the account.
 * @apiBody {String} fname First name of the user.
 * @apiBody {String} [mname] Middle name of the user (if any).
 * @apiBody {String} lname Last name of the user.
 * @apiBody {String} sex Gender/Sex of the user.
 * @apiBody {String} dob Date of Birth of the user.
 * @apiBody {String} mobile User mobile number.
 * @apiBody {String} email User E-mail address.
 * @apiBody {Number} status=1 The initial status of the account (Default: 1) [0=Pending Activation, 1=Active, 2=Suspended].
 *
 * @apiExample {http} HTTP
 * PUT /api/admin/accounts/users/update HTTP/2
 * Host: mychhotafarm.indiosco.com
 * Accept: application/json
 * Content-Length: 170
 *
 * uid=10&uname=amanda&password=12345&fname=Amanda&mname=&lname=Jones&sex=Female&dob=December%2020%2C%201999&mobile=%2B919876543210&email=amanda%40demo.indiosco.com&status=1
 * @apiExample {jquery} jQuery (Ajax)
 *	let settings = {
 *		"url": "https://mychhotafarm.indiosco.com/api/admin/accounts/users/update",
 *		"type": "PUT",
 *		"timeout": 0,
 *		"headers": {
 *			"Accept": "application/json",
 *			"Content-Type": "application/x-www-form-urlencoded",
 *			"Authorization": "4B2BB00E-595A-4973-8D60-61B0F59B2899",
 *			"X-Requested-With": "5C349D9C-1355-4115-A7E2-33985860B41A"
 *		},
 *		"data": {
 *			"uid": "10",
 *			"uname": "amanda",
 *			"password": "12345",
 *			"fname": "Amanda",
 *			"mname": "",
 *			"lname": "Jones",
 *			"sex": "Female",
 *			"dob": "December 20, 1999",
 *			"mobile": "+919876543210",
 *			"email": "amanda@demo.indiosco.com",
 *			"status": "1"
 *		}
 *	};
 *
 *	$.ajax(settings).done(function(response) {
 *		console.log(response);
 *	});
 * @apiExample {java} Java
 *	import com.mashape.unirest.http.*;
 *	import java.io.*;
 *
 *	public class main {
 *		public static void main(String []args) throws Exception {
 *			Unirest.setTimeouts(0, 0);
 *			HttpResponse<String> response = Unirest.put("https://mychhotafarm.indiosco.com/api/admin/accounts/users/update")
 *			.header("Accept", "application/json")
 *			.header("Content-Type", "application/x-www-form-urlencoded")
 *			.header("Authorization", "4B2BB00E-595A-4973-8D60-61B0F59B2899")
 *			.header("X-Requested-With", "5C349D9C-1355-4115-A7E2-33985860B41A")
 *			.field("uid", "10")
 *			.field("uname", "amanda")
 *			.field("password", "12345")
 *			.field("fname", "Amanda")
 *			.field("mname", "")
 *			.field("lname", "Jones")
 *			.field("sex", "Female")
 *			.field("dob", "December 20, 1999")
 *			.field("mobile", "+919876543210")
 *			.field("email", "amanda@demo.indiosco.com")
 *			.field("status", "1")
 *			.asString();
 *
 *			System.out.println(response.getBody());
 *		}
 *	}
 * @apiExample {php} PHP (cURL)
 *	<?php
 *	$curl = curl_init();
 *
 *	curl_setopt_array($curl, array(
 *		CURLOPT_URL => 'https://mychhotafarm.indiosco.com/api/admin/accounts/users/update',
 *		CURLOPT_RETURNTRANSFER => true,
 *		CURLOPT_ENCODING => '',
 *		CURLOPT_MAXREDIRS => 10,
 *		CURLOPT_TIMEOUT => 0,
 *		CURLOPT_FOLLOWLOCATION => true,
 *		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_1,
 *		CURLOPT_CUSTOMREQUEST => 'PUT',
 *		CURLOPT_POSTFIELDS => 'uid=10&uname=amanda&password=12345&fname=Amanda&mname=&lname=Jones&sex=Female&dob=December%2020%2C%201999&mobile=%2B919876543210&email=amanda%40demo.indiosco.com&status=1',
 *		CURLOPT_HTTPHEADER => array(
 *			'Accept: application/json',
 *			'Content-Type: application/x-www-form-urlencoded',
 *			'Authorization: 4B2BB00E-595A-4973-8D60-61B0F59B289',
 *			'X-Requested-With: 5C349D9C-1355-4115-A7E2-33985860B41A',
 *			'Cookie: PHPSESSID=cndfdtb1g7ok2aobrvkm77cng3'
 *		),
 *	));
 *
 *	$response = curl_exec($curl);
 *
 *	curl_close($curl);
 *	echo $response;
 * @apiExample {curl} CURL
 * curl --location --request PUT "https://mychhotafarm.indiosco.com/api/admin/accounts/users/update" --header "Accept: application/json" --header "Authorization: 4B2BB00E-595A-4973-8D60-61B0F59B289" --header "X-Requested-With: 5C349D9C-1355-4115-A7E2-33985860B41A" --header "Content-Type:application/x-www-form-urlencoded" --header "Cookie: PHPSESSID=cndfdtb1g7ok2aobrvkm77cng3" --data-urlencode "type=1" --data-urlencode "uname=amanda" --data-urlencode "password=12345" --data-urlencode "fname=Amanda" --data-urlencode "mname=" --data-urlencode "lname=Jones" --data-urlencode "sex=Female" --data-urlencode "dob=December 20, 1999" --data-urlencode "mobile=+919876543210" --data-urlencode "email=amanda@demo.indiosco.com" --data-urlencode "status=1"
 *
 * @apiUse MCFAPICreateResponse
 */

use MyChhotaFarm\Accounts\MCFAccounts;
use MyChhotaFarm\Accounts\Users;
use MyChhotaFarm\API\MCFAPI;

$access_control_allow_methods = 'PUT';

require_once dirname(__DIR__, 5) . "/config/api.config.php";

$Users = new Users();

$uid = put('uid');
$uname = put('uname');
$password = put('password');
$fname = put('fname');
$mname = put('mname');
$lname = put('lname');
$sex = put('sex');
$dob = put('dob');
$mobile = put('mobile');
$email = put('email');
$status = put('status');

$MCFAPI->checkInputs(MCFAPI::MCFAPI_CHECK_INPUT_NOTEMPTY, ['Username', 'First name', 'Last name'], $uname, $fname, $lname); //101
$MCFAPI->checkInputs(MCFAPI::MCFAPI_CHECK_INPUT_NUMERIC, ['UID'], $uid); //101
$status = (!$Users->isValidAccountStatus($status)) ? MCFAccounts::MCF_ACCOUNT_STATUS_ACTIVE : $status;

if($Users->updateUser($uid, $uname, $fname, $lname, $password, $sex, $dob, $mname, $mobile, $email, $status)) {
	$MCFAPI->generateResponse(200, 'users.update_success'); //200 (or 204 if not returning any content in the body)
} else {
	$MCFAPI->generateResponse(500, 'users.update_failed', true, $uname); //102
}