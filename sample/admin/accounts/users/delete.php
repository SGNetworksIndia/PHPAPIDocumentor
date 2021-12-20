<?php
/**
 * @api {delete} /admin/accounts/user/delete Delete
 * @apiGroup Admin\User
 * @apiName DeleteUser
 * @apiVersion 1.0.39
 * @apiDescription Delete a user account identified by supplied Account ID (UID)
 * @apiPermission admin
 *
 * @apiBody {Number} oid The UID of the user to delete.
 *
 * @apiExample {http} HTTP
 * DELETE /api/admin/accounts/users/create HTTP/2
 * Host: mychhotafarm.indiosco.com
 * Accept: application/json
 * Content-Length: 170
 *
 * oid=10
 * @apiExample {jquery} jQuery (Ajax)
 *	let settings = {
 *		"url": "https://mychhotafarm.indiosco.com/api/admin/accounts/users/create",
 *		"type": "DELETE",
 *		"timeout": 0,
 *		"headers": {
 *			"Accept": "application/json",
 *			"Content-Type": "application/x-www-form-urlencoded",
 *			"Authorization": "4B2BB00E-595A-4973-8D60-61B0F59B2899",
 *			"X-Requested-With": "5C349D9C-1355-4115-A7E2-33985860B41A"
 *		},
 *		"data": {
 *			"oid": "10"
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
 *			HttpResponse<String> response = Unirest.delete("https://mychhotafarm.indiosco.com/api/admin/accounts/users/delete")
 *			.header("Accept", "application/json")
 *			.header("Content-Type", "application/x-www-form-urlencoded")
 *			.header("Authorization", "4B2BB00E-595A-4973-8D60-61B0F59B2899")
 *			.header("X-Requested-With", "5C349D9C-1355-4115-A7E2-33985860B41A")
 *			.field("oid", "10")
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
 *		CURLOPT_URL => 'https://mychhotafarm.indiosco.com/api/admin/accounts/users/delete',
 *		CURLOPT_RETURNTRANSFER => true,
 *		CURLOPT_ENCODING => '',
 *		CURLOPT_MAXREDIRS => 10,
 *		CURLOPT_TIMEOUT => 0,
 *		CURLOPT_FOLLOWLOCATION => true,
 *		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_1,
 *		CURLOPT_CUSTOMREQUEST => 'DELETE',
 *		CURLOPT_POSTFIELDS => 'oid=10',
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
 * curl --location --request DELETE "https://mychhotafarm.indiosco.com/api/admin/accounts/users/delete" --header "Accept: application/json" --header "Authorization: 4B2BB00E-595A-4973-8D60-61B0F59B289" --header "X-Requested-With: 5C349D9C-1355-4115-A7E2-33985860B41A" --header "Content-Type:application/x-www-form-urlencoded" --data-urlencode "oid=10"
 *
 * @apiUse MCFAPIGenericResponse
 */

use MyChhotaFarm\Accounts\Users;

$access_control_allow_methods = 'DELETE';

require_once dirname(__DIR__, 5) . "/config/api.config.php";

$Users = new Users();

$uid = delete('oid');

if(!is_numeric($uid))
	$MCFAPI->generateResponse(400, 'common.arg_invalid', true, 'UID'); //101

if($Users->deleteUser($uid)) {
	$MCFAPI->generateResponse(200, 'users.delete_success'); //200
} else {
	$MCFAPI->generateResponse(500, 'users.delete_failed'); //102
}