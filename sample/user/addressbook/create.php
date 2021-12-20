<?php
/**
 * @api {post} /user/adressbook/create Create
 * @apiGroup User\AddressBook
 * @apiName CreateAddress
 * @apiVersion 1.0.39
 * @apiDescription Creates a new address and stores it in the user's AddressBook
 * @apiPermission user
 *
 * @apiBody {Number} type=1 The type of address to be created (Default: 1) [1=Residential, 2=Commercial].
 * @apiBody {String} label The label of the address.
 * @apiBody {String} line1 Address line-1.
 * @apiBody {String} [line2] Address line-2.
 * @apiBody {String} street The name. of the street of the address
 * @apiBody {String} zip Postal/ZIP Code of the address.
 * @apiBody {String} country The name of the country where the address is located.
 * @apiBody {String} state The name of the state where the address is located.
 * @apiBody {String} city The name of the city where the address is located.
 *
 * @apiExample {http} HTTP
 * POST /api/user/adressbook/create HTTP/2
 * Host: mychhotafarm.indiosco.com
 * Accept: application/json
 * Content-Length: 170
 *
 * type=1&uname=amanda&password=12345&fname=Amanda&mname=&lname=Jones&sex=Female&dob=December%2020%2C%201999&mobile=%2B919876543210&email=amanda%40demo.indiosco.com&status=1
 * @apiExample {jquery} jQuery (Ajax)
 *	let settings = {
 *		"url": "https://mychhotafarm.indiosco.com/api/user/adressbook/create",
 *		"type": "POST",
 *		"timeout": 0,
 *		"headers": {
 *			"Accept": "application/json",
 *			"Content-Type": "application/x-www-form-urlencoded",
 *			"Authorization": "4B2BB00E-595A-4973-8D60-61B0F59B2899",
 *			"X-Requested-With": "5C349D9C-1355-4115-A7E2-33985860B41A"
 *		},
 *		"data": {
 *			"type": "1",
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
 *			HttpResponse<String> response = Unirest.post("https://mychhotafarm.indiosco.com/api/user/adressbook/create")
 *			.header("Accept", "application/json")
 *			.header("Content-Type", "application/x-www-form-urlencoded")
 *			.header("Authorization", "4B2BB00E-595A-4973-8D60-61B0F59B2899")
 *			.header("X-Requested-With", "5C349D9C-1355-4115-A7E2-33985860B41A")
 *			.field("type", "1")
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
 *		CURLOPT_URL => 'https://mychhotafarm.indiosco.com/api/user/adressbook/create',
 *		CURLOPT_RETURNTRANSFER => true,
 *		CURLOPT_ENCODING => '',
 *		CURLOPT_MAXREDIRS => 10,
 *		CURLOPT_TIMEOUT => 0,
 *		CURLOPT_FOLLOWLOCATION => true,
 *		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_1,
 *		CURLOPT_CUSTOMREQUEST => 'POST',
 *		CURLOPT_POSTFIELDS => 'type=1&uname=amanda&password=12345&fname=Amanda&mname=&lname=Jones&sex=Female&dob=December%2020%2C%201999&mobile=%2B919876543210&email=amanda%40demo.indiosco.com&status=1',
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
 * curl --location --request POST "https://mychhotafarm.indiosco.com/api/user/adressbook/create" --header "Accept: application/json" --header "Authorization: 4B2BB00E-595A-4973-8D60-61B0F59B289" --header "X-Requested-With: 5C349D9C-1355-4115-A7E2-33985860B41A" --header "Content-Type:application/x-www-form-urlencoded" --header "Cookie: PHPSESSID=cndfdtb1g7ok2aobrvkm77cng3" --data-urlencode "type=1" --data-urlencode "uname=amanda" --data-urlencode "password=12345" --data-urlencode "fname=Amanda" --data-urlencode "mname=" --data-urlencode "lname=Jones" --data-urlencode "sex=Female" --data-urlencode "dob=December 20, 1999" --data-urlencode "mobile=+919876543210" --data-urlencode "email=amanda@demo.indiosco.com" --data-urlencode "status=1"
 *
 * @apiUse MCFAPICreateResponse
 */

use MyChhotaFarm\API\MCFAPI;
use MyChhotaFarm\User\AddressBooks;

$access_control_allow_methods = 'POST';

require_once dirname(__DIR__, 4) . "/config/api.config.php";

$AddressBooks = new AddressBooks();

$label = post('label','Home');
$type = post('type',1);
$line1 = post('line1');
$line2 = post('line2');
$address = (!empty($line2)) ? "$line1, $line2" : $line1;
$street = post('street');
$country = post('country');
$state = post('state');
$city = post('city');
$zip = post('zip');

$MCFAPI->checkInputs(MCFAPI::MCFAPI_CHECK_INPUT_NOTEMPTY, ['Address Line-1', 'Street', 'Country', 'State', 'City'], $line1, $street, $country, $state, $city);

if($AddressBooks->createAddress($address, $street, $country, $state, $city, $zip, $label, $type)) {
	$MCFAPI->showResponse($MCFAPI->getResponse());
} else {
	$MCFAPI->generateResponse(500, 'addressbook.create_failed', true, $uname);
}
