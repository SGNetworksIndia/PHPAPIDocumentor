/**
 * @apiDefine MCFMetadata
 *
 * @apiHeader {String} Accept=application/json The expecting response content type to accept.
 * @apiHeader {String} Content-Type=application/x-www-form-urlencoded The content type used to send the request.
 * @apiHeader {String} Authorization:4B2BB00E-595A-4973-8D60-61B0F59B289 The authorization token used to authenticate (this token will be generated after login and can be retrieved by successful login to the API endpoint: /auth/login) [may not be requred to access some of the API end-points].
 * @apiHeader {String} X-Requested-With:5C349D9C-1355-4115-A7E2-33985860B41A The GUID of the client from where the request is sent (contact the authorized developer to get the GUID).
 * @apiHeaderExample {Header} Example
 *		"Accept: application/json",
 *		"Authorization: 4B2BB00E-595A-4973-8D60-61B0F59B289",
 *		"X-Requested-With: 5C349D9C-1355-4115-A7E2-33985860B41A"
 */


/**
 * @apiDefine MCFAPIGenericError
 * @apiErrorExample {json} MCFAPIGenericError
 * HTTP/2 500 Internal Server Error
 * {
 *     "API Name": "MyChhotaFarm",
 *     "API Version": "1.0.39 [build 40]",
 *     "Build Date": "December 02, 2021 02:48:26 PM",
 *     "Documentation": "https://mychhotafarm.itpldemos.com/api/docs/",
 *     "Developed By": "Indiosco Technologies Private Limited",
 *     "response": {
 *         "error": true,
 *         "code": 500,
 *         "msg": "Failed to create the user <b>amanda</b>.",
 *         "result": []
 *     },
 *     "client": {
 *         "ip": "127.0.0.1"
 *     }
 * }
 */

/**
 * @apiDefine MCFAPIGenericSuccess
 *
 * @apiSuccessExample {json} MCFAPIGenericSuccess
 * HTTP/2 200 OK
 * {
 *     "API Name": "MyChhotaFarm",
 *     "API Version": "1.0.39 [build 40]",
 *     "Build Date": "December 02, 2021 02:48:26 PM",
 *     "Documentation": "https://mychhotafarm.itpldemos.com/api/docs/",
 *     "Developed By": "Indiosco Technologies Private Limited",
 *     "response": {
 *         "error": false,
 *         "code": 201,
 *         "msg": "User Created!",
 *         "result": []
 *     },
 *     "client": {
 *         "ip": "127.0.0.1"
 *     }
 * }
 */

/**
 * @apiDefine MCFAPICreateSuccess
 *
 * @apiSuccessExample {json} MCFAPICreateSuccess
 * HTTP/2 201 Created
 * {
 *     "API Name": "MyChhotaFarm",
 *     "API Version": "1.0.39 [build 40]",
 *     "Build Date": "December 02, 2021 02:48:26 PM",
 *     "Documentation": "https://mychhotafarm.itpldemos.com/api/docs/",
 *     "Developed By": "Indiosco Technologies Private Limited",
 *     "response": {
 *         "error": false,
 *         "code": 201,
 *         "msg": "User Created!",
 *         "result": []
 *     },
 *     "client": {
 *         "ip": "127.0.0.1"
 *     }
 * }
 */


/**
 * @apiDefine MCFAPIGenericResponse
 *
 * @apiUse MCFAPIGenericSuccess
 * @apiUse MCFAPIGenericError
 * @apiUse MCFMetadata
 */

/**
 * @apiDefine MCFAPICreateResponse
 *
 * @apiUse MCFAPICreateSuccess
 * @apiUse MCFAPIGenericError
 * @apiUse MCFMetadata
 */


/**
 * @apiDefine ServerError
 * @apiError ServerError There was a problem processing your request
 * @apiErrorExample ServerError
 *   HTTP/1.1 500 Not Found
 *   "Internal Server Error"
 *
 * @apiUse MCFMetadata
 */

