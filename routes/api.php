<?php

use App\Http\Resources\PatientsResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Laravue\Faker;
use \App\Laravue\JsonResponse;
use \App\Laravue\Acl;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\MedicineController;
use App\Http\Controllers\Api\DiagnosticsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::
        namespace('Api')->group(function () {
            Route::post('auth/login', 'AuthController@login');
            Route::group(['middleware' => 'auth:sanctum'], function () {
                // Auth routes
                Route::get('auth/user', 'AuthController@user');
                Route::post('auth/logout', 'AuthController@logout');

                Route::get('/user', function (Request $request) {
                    return new UserResource($request->user());
                });

                /* Route::get('/patients', function (Request $request) {
                    return new PatientsResource($request->patients());
                });
                 */
                Route::get('/patients', [PatientController::class, 'index']);
                Route::get('/medicines', [MedicineController::class, 'index']);
                Route::get('/diagnostics', [DiagnosticsController::class, 'index']);
                Route::get('/apt_list', [PatientController::class, 'appointmentList']);

                // Api resource routes
                Route::apiResource('roles', 'RoleController')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
                Route::apiResource('users', 'UserController')->middleware('permission:' . Acl::PERMISSION_USER_MANAGE);
                Route::apiResource('permissions', 'PermissionController')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);

                // Custom routes
                Route::put('users/{user}', 'UserController@update');
                Route::get('users/{user}/permissions', 'UserController@permissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
                Route::put('users/{user}/permissions', 'UserController@updatePermissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
                Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);

                Route::get('find-patient/{kw}', 'PatientController@findPatient');
                Route::post('save-appointment', 'PatientController@saveAppointment');
                Route::post('update-appointment', 'PatientController@updateAppointment');
                Route::get('get-appointment/{id}', 'PatientController@getAppointmentDetails');
                Route::get('find-medicine/{kw}', 'MedicineController@findMedicine');
                Route::get('find-procedure/{kw}', 'AncillaryController@findprocedure');
                Route::get('find-services/{kw}', 'ServicesController@findservices');
                Route::get('printpdf/{id}', 'PatientController@printpdf');
                Route::get('printpdf2/{id}', 'PatientController@printpdf2');
                Route::get('printmedcert/{id}', 'PatientController@printmedcert');
                Route::get('printreferral/{id}', 'PatientController@printreferral');
                Route::get('printform/{id}', 'PatientController@printform');
                Route::get('printriskstrat/{id}', 'PatientController@printriskstrat');
                Route::get('printrequest/{id}/{type}', 'PatientController@printrequest');
                Route::get('printclearance/{id}', 'PatientController@printclearance');
                Route::get('printfittowork/{id}', 'PatientController@printfittowork');
                Route::post('add-patients', 'PatientController@storePatient');
                Route::get('get-patient/{id}', 'PatientController@getPatient');
                Route::post('update-patients', 'PatientController@updatePatient');
                Route::get('get-patient-past-consult/{id}', 'PatientController@getpastConsultationList');
                Route::delete('remove-meds/{id}', 'PatientController@deleteMed');
                Route::post('add-meds', 'PatientController@addMed');
                Route::patch('update-meds/{id}', 'PatientController@updateMed');

// PE Templates Routes
Route::middleware('auth:api')->group(function () {
    Route::get('pe-templates', 'PeTemplateController@index');
    Route::get('pe-templates/type/{type}', 'PeTemplateController@getByType');
    Route::post('pe-templates', 'PeTemplateController@store');
    Route::put('pe-templates/{id}', 'PeTemplateController@update');
    Route::delete('pe-templates/{id}', 'PeTemplateController@destroy');
    Route::patch('pe-templates/{id}/toggle-status', 'PeTemplateController@toggleStatus');
});
                Route::delete('remove-diagnostic/{id}', 'PatientController@deleteDiagnostic');
                Route::post('add-diagnostic', 'PatientController@addDiagnostic');
                Route::delete('remove-service/{id}', 'PatientController@deleteService');
                Route::post('add-service', 'PatientController@addService');
                Route::get('get-appointment-meds/{id}', 'PatientController@getAppointmentMedicine');
                Route::get('get-appointment-diagnostics/{id}', 'PatientController@getAppointmentDiagnostics');
                Route::get('get-appointment-service/{id}', 'PatientController@getAppointmentService');
                Route::get('done-consult/{id}', 'PatientController@doneConsult');
                Route::get('import-medicine/{id}/{appid}', 'PatientController@ImportLastPrescription');

                #Medicine
                Route::post('store-meds', 'MedicineController@store');
                Route::patch('update-meds', 'MedicineController@update');
                Route::get('get-meds/{id}', 'MedicineController@edit');
                Route::delete('delete-meds/{id}', 'MedicineController@delete');

                #diagnostic
                Route::post('store-diagnostics', 'DiagnosticsController@store');
                Route::patch('update-diagnostics', 'DiagnosticsController@update');
                Route::get('get-diagnostics/{id}', 'DiagnosticsController@edit');
                Route::delete('delete-diagnostics/{id}', 'DiagnosticsController@delete');

                Route::patch('update-profile', 'ProfileController@update');
                Route::get('remove-signature/{id}', 'ProfileController@removeSignature');
                Route::get('get-profile', 'ProfileController@getProfile');
                Route::get('get-patient-attachments/{id}', 'PatientController@getAttachments');
                Route::post('add-patient-attachments', 'PatientController@addpatientAttachments');
                Route::delete('delete-patient-attachments/{id}', 'PatientController@deleteAttachment');
                Route::get('dashboard', 'PatientController@dashboard');
                Route::get('printchart/{id}', 'PatientController@printchart');
                Route::patch('cancel-appointment', 'PatientController@cancelAppointment');
                Route::get('get-all-services', 'ServicesController@getAllServices');
                Route::patch('update-bp', 'PatientController@updateBP');
                Route::get('get-all-diagnostics', 'DiagnosticsController@getAllDiagnostics');
                Route::post('reorder', 'PatientController@reorderAppointment');
                Route::patch('set-active/{id}', 'PatientController@setActive');
                Route::post('api_saveAppointment', 'PatientController@api_saveAppointment');
                Route::post('api_updateAppointment', 'PatientController@api_updateAppointment');
                Route::post('api_addMed', 'PatientController@api_addMed');
                Route::post('api_addDiagnostic', 'PatientController@api_addDiagnostic');
                Route::get('delete-patient/{id}', 'PatientController@deletePatient');
                Route::post('add-problem', 'PatientController@addProblem');
                Route::get('getPatient-additional-checkList/{id}', 'PatientController@getPatientAdditionalCheckList');
                Route::delete('delete-patientProblem/{id}', 'PatientController@deletePatientProblem');
                Route::post('add-adolecense', 'PatientController@addAdolecense');
                Route::get('getPatient-adolecense/{id}', 'PatientController@getPatientAdolecense');
                Route::delete('delete-adolecense/{id}', 'PatientController@deleteAdolecense');
                Route::post('add-vaccination', 'PatientController@addVaccination');
                Route::get('getPatient-vaccinations/{id}', 'PatientController@getPatientVaccinations');
                Route::delete('delete-vaccination/{id}', 'PatientController@deleteVaccination');

                Route::post('add-growthdev', 'PatientController@addGrowthDev');
                Route::get('getPatient-growthdevs/{id}', 'PatientController@getPatientGrowthDevs');
                Route::delete('delete-growthdev/{id}', 'PatientController@deleteGrowthDev');
                Route::get('printpdf3/{id}', 'PatientController@printpdf3');
                Route::get('get-appointment-meds-blank/{id}', 'PatientController@getAppointmentMedicineBlank');
                Route::post('add-meds-blank', 'PatientController@addMed_blank');
                /* Route::get('generateImage','PatientController@generateImage');
                Route::get('generateAtt','PatientController@generateAtt'); */

                
                #Services
                Route::get('services', 'ServicesController@index');
                Route::post('service', 'ServicesController@store');
                Route::patch('service', 'ServicesController@update');
                Route::delete('service/{id}', 'ServicesController@delete');
                
                Route::get('email-prescription/{id}', 'PatientController@emailPrescription');


                Route::get('/test-broadcast', function () {
                    event(new \App\Events\NewUserAdded(\App\Laravue\Models\User::first()));
                    return 'Broadcasted!';
                });
            });
        });

// Fake APIs
Route::get('/table/list', function () {
    $rowsNumber = mt_rand(20, 30);
    $data = [];
    for ($rowIndex = 0; $rowIndex < $rowsNumber; $rowIndex++) {
        $row = [
            'author' => Faker::randomString(mt_rand(5, 10)) . 'xxxx',
            'display_time' => Faker::randomDateTime()->format('Y-m-d H:i:s'),
            'id' => mt_rand(100000, 100000000),
            'pageviews' => mt_rand(100, 10000),
            'status' => Faker::randomInArray(['deleted', 'published', 'draft']),
            'title' => Faker::randomString(mt_rand(20, 50)),
        ];

        $data[] = $row;
    }

    return response()->json(new JsonResponse(['items' => $data]));
});

Route::get('/orders', function () {
    $rowsNumber = 8;
    $data = [];
    for ($rowIndex = 0; $rowIndex < $rowsNumber; $rowIndex++) {
        $row = [
            'order_no' => 'LARAVUE' . mt_rand(1000000, 9999999),
            'price' => mt_rand(10000, 999999),
            'status' => Faker::randomInArray(['success', 'pending']),
        ];

        $data[] = $row;
    }

    return response()->json(new JsonResponse(['items' => $data]));
});

Route::get('/articles', function () {
    $rowsNumber = 10;
    $data = [];
    for ($rowIndex = 0; $rowIndex < $rowsNumber; $rowIndex++) {
        $row = [
            'id' => mt_rand(100, 10000),
            'display_time' => Faker::randomDateTime()->format('Y-m-d H:i:s'),
            'title' => Faker::randomString(mt_rand(20, 50)),
            'author' => Faker::randomString(mt_rand(5, 10)) . 'xxxx',
            'comment_disabled' => Faker::randomBoolean(),
            'content' => Faker::randomString(mt_rand(100, 300)),
            'content_short' => Faker::randomString(mt_rand(30, 50)),
            'status' => Faker::randomInArray(['deleted', 'published', 'draft']),
            'forecast' => mt_rand(100, 9999) / 100,
            'image_uri' => 'https://via.placeholder.com/400x300',
            'importance' => mt_rand(1, 3),
            'pageviews' => mt_rand(10000, 999999),
            'reviewer' => Faker::randomString(mt_rand(5, 10)),
            'timestamp' => Faker::randomDateTime()->getTimestamp(),
            'type' => Faker::randomInArray(['US', 'VI', 'JA']),

        ];

        $data[] = $row;
    }

    return response()->json(new JsonResponse(['items' => $data, 'total' => mt_rand(1000, 10000)]));
});

Route::get('articles/{id}', function ($id) {
    $article = [
        'id' => $id,
        'display_time' => Faker::randomDateTime()->format('Y-m-d H:i:s'),
        'title' => Faker::randomString(mt_rand(20, 50)),
        'author' => Faker::randomString(mt_rand(5, 10)),
        'comment_disabled' => Faker::randomBoolean(),
        'content' => Faker::randomString(mt_rand(100, 300)),
        'content_short' => Faker::randomString(mt_rand(30, 50)),
        'status' => Faker::randomInArray(['deleted', 'published', 'draft']),
        'forecast' => mt_rand(100, 9999) / 100,
        'image_uri' => 'https://via.placeholder.com/400x300',
        'importance' => mt_rand(1, 3),
        'pageviews' => mt_rand(10000, 999999),
        'reviewer' => Faker::randomString(mt_rand(5, 10)),
        'timestamp' => Faker::randomDateTime()->getTimestamp(),
        'type' => Faker::randomInArray(['US', 'VI', 'JA']),

    ];

    return response()->json(new JsonResponse($article));
});

Route::get('articles/{id}/pageviews', function ($id) {
    $pageviews = [
        'PC' => mt_rand(10000, 999999),
        'Mobile' => mt_rand(10000, 999999),
        'iOS' => mt_rand(10000, 999999),
        'android' => mt_rand(10000, 999999),
    ];
    $data = [];
    foreach ($pageviews as $device => $pageview) {
        $data[] = [
            'key' => $device,
            'pv' => $pageview,
        ];
    }

    return response()->json(new JsonResponse(['pvData' => $data]));
});

