<?php

use App\Http\Controllers\rolesControllerAPI;
use App\Http\Controllers\permission_roleControllerAPI;
use App\Http\Controllers\permissionsControllerAPI;
use App\Http\Controllers\SessionsControllerAPI;
use App\Http\Controllers\SessionsCasesControllerAPI;
use App\Http\Controllers\doctorsControllerAPI;
use App\Http\Controllers\statusControllerAPI;
use App\Http\Controllers\billsControllerAPI;
use App\Http\Controllers\CasesControllerAPI;
use App\Http\Controllers\patientsControllerAPI;
use App\Http\Controllers\UserControllerAPI;
use App\Http\Controllers\PatientsAccountsControllerAPI;
use App\Http\Controllers\UsersControllerAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
/*
|--------------------------------------------------------------------------
| Users endpoints
|--------------------------------------------------------------------------
 */
// Route::name('users.')->prefix('users')->group(function () {
//     Route::get('/', [UsersControllerAPI::class, 'index'])->name('index');
//     Route::post('/login', [UsersControllerAPI::class, 'Login'])->name('Login');
//     Route::post('/', [UsersControllerAPI::class, 'store'])->name('create');
//     Route::get('/{users}', [UsersControllerAPI::class, 'show'])->name('show');
//     Route::patch('/{users}', [UsersControllerAPI::class, 'update'])->name('update');
//     Route::delete('/{users}', [UsersControllerAPI::class, 'destroy'])->name('destroy');
// });

/*
|--------------------------------------------------------------------------
| User endpoints
|--------------------------------------------------------------------------
 */
Route::name('users.')->prefix('users')->middleware('api')->group(function () {
    Route::get('/', [UserControllerAPI::class, 'index'])->name('index');
    Route::get('/getInfo', [UserControllerAPI::class, 'getInfo'])->name('getInfo');
   // Route::post('/', [UserControllerAPI::class, 'store'])->name('create');
    Route::post('/login', [UserControllerAPI::class, 'Login'])->name('Login');
    Route::post('/UpdateInfo', [UserControllerAPI::class, 'UpdateInfo'])->name('UpdateInfo');
    Route::post('/DeleteImage', [UserControllerAPI::class, 'DeleteImage'])->name('DeleteImage');

    
    //getByUserId

    
    Route::get('/{user}', [UserControllerAPI::class, 'show'])->name('show');
    Route::patch('/{user}', [UserControllerAPI::class, 'update'])->name('update');
    Route::delete('/{user}', [UserControllerAPI::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| patients endpoints
|--------------------------------------------------------------------------

'middleware' => 'api',
 */

Route::name('patients.')->prefix('patients')->group(function () {
    Route::get('/', [patientsControllerAPI::class, 'index'])->name('index');
    Route::get('search/{search}', [patientsControllerAPI::class, 'search'])->name('search');
    Route::post('/', [patientsControllerAPI::class, 'store'])->name('create');
    Route::get('getByUserId', [patientsControllerAPI::class, 'getByUserId'])->name('getByUserId');
    Route::get('patientsAccounsts', [patientsControllerAPI::class, 'patientsAccounsts'])->name('patientsAccounsts');
    Route::get('/{patients}', [patientsControllerAPI::class, 'show'])->name('show');
    Route::patch('/{patients}', [patientsControllerAPI::class, 'update'])->name('update');
    Route::delete('/{patients}', [patientsControllerAPI::class, 'destroy'])->name('destroy');

});


Route::name('patientsAccounsts.')->prefix('patientsAccounsts')->group(function () {
 
    Route::get('/', [PatientsAccountsControllerAPI::class, 'patientsAccounsts'])->name('patientsAccounsts');

    Route::get('/dash', [PatientsAccountsControllerAPI::class, 'patientsAccounstsdash'])->name('patientsAccounstsdash');

   Route::get('/export', [PatientsAccountsControllerAPI::class, 'export'])->name('export');
    Route::post('/search', [PatientsAccountsControllerAPI::class, 'search'])->name('search');
});

/*
|--------------------------------------------------------------------------
| Cases endpoints
|--------------------------------------------------------------------------
 */

Route::post('/cases/uploude_image', [CasesControllerAPI::class, 'uploude_image'])->name('uploude_image');
Route::name('cases.')->prefix('cases')->middleware('CheckRole:doctor')->group(function () {
    
  

    

    //Usercases CaseCategories
    Route::get('/', [CasesControllerAPI::class, 'index'])->name('index');
    Route::get('/getDashbourdCounts', [CasesControllerAPI::class, 'getDashbourdCounts'])->name('getDashbourdCounts');
    Route::post('/', [CasesControllerAPI::class, 'store'])->name('store');
    // Route::post('/uploude_image', [CasesControllerAPI::class, 'uploude_image'])->name('uploude_image');
    Route::get('/search', [CasesControllerAPI::class, 'Search'])->name('Search');
    Route::get('/CaseCategories', [CasesControllerAPI::class, 'getCaseCategories'])->name('getCaseCategories');
    Route::get('/getCaseCategoriesCounts', [CasesControllerAPI::class, 'getCaseCategoriesCounts'])->name('getCaseCategoriesCounts');
    Route::get('/UserCases', [CasesControllerAPI::class, 'UserCases'])->name('UserCases');
    Route::get('/patientCases/{patient_id}', [CasesControllerAPI::class, 'patientCases'])->name('patientCases');
    Route::get('/{cases}', [CasesControllerAPI::class, 'show'])->name('show');
    Route::patch('/{cases}', [CasesControllerAPI::class, 'update'])->name('update');
    Route::delete('/{cases}', [CasesControllerAPI::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| bills endpoints
|--------------------------------------------------------------------------
 */
Route::name('bills.')->middleware('api')->prefix('bills')->group(function () {
    Route::get('/', [billsControllerAPI::class, 'index'])->name('index');
   
    Route::post('/', [billsControllerAPI::class, 'store'])->name('create');
    Route::get('/{bills}', [billsControllerAPI::class, 'show'])->name('show');
    Route::patch('/{bills}', [billsControllerAPI::class, 'update'])->name('update');
    Route::delete('/{bills}', [billsControllerAPI::class, 'destroy'])->name('destroy');
   


    
});

/*
|--------------------------------------------------------------------------
| status endpoints
|--------------------------------------------------------------------------
 */
Route::name('statuses.')->prefix('statuses')->group(function () {
    Route::get('/', [statusControllerAPI::class, 'index'])->name('index');
    Route::post('/', [statusControllerAPI::class, 'store'])->name('create');
    Route::get('/{status}', [statusControllerAPI::class, 'show'])->name('show');
    Route::patch('/{status}', [statusControllerAPI::class, 'update'])->name('update');
    Route::delete('/{status}', [statusControllerAPI::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| doctors endpoints
|--------------------------------------------------------------------------
 */
Route::name('doctors.')->prefix('doctors')->group(function () {
    Route::get('/', [doctorsControllerAPI::class, 'index'])->name('index');
    Route::post('/', [doctorsControllerAPI::class, 'store'])->name('create');
    Route::get('/{doctors}', [doctorsControllerAPI::class, 'show'])->name('show');
    Route::patch('/{doctors}', [doctorsControllerAPI::class, 'update'])->name('update');
    Route::delete('/{doctors}', [doctorsControllerAPI::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| SessionsCases endpoints
|--------------------------------------------------------------------------
 */
Route::name('sessions-cases.')->prefix('sessions-cases')->group(function () {
    Route::get('/', [SessionsCasesControllerAPI::class, 'index'])->name('index');
    Route::post('/', [SessionsCasesControllerAPI::class, 'store'])->name('create');
    Route::get('/{sessionsCases}', [SessionsCasesControllerAPI::class, 'show'])->name('show');
    Route::patch('/{sessionsCases}', [SessionsCasesControllerAPI::class, 'update'])->name('update');
    Route::delete('/{sessionsCases}', [SessionsCasesControllerAPI::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Sessions endpoints
|--------------------------------------------------------------------------
 */
Route::name('sessions.')->prefix('sessions')->group(function () {
    Route::get('/', [SessionsControllerAPI::class, 'index'])->name('index');
    Route::post('/', [SessionsControllerAPI::class, 'store'])->name('create');
    Route::get('/{sessions}', [SessionsControllerAPI::class, 'show'])->name('show');
    Route::patch('/{sessions}', [SessionsControllerAPI::class, 'update'])->name('update');
    Route::delete('/{sessions}', [SessionsControllerAPI::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| permissions endpoints
|--------------------------------------------------------------------------
 */
Route::name('permissions.')->prefix('permissions')->group(function () {
    Route::get('/', [permissionsControllerAPI::class, 'index'])->name('index');
    Route::post('/', [permissionsControllerAPI::class, 'store'])->name('create');
    Route::get('/{permissions}', [permissionsControllerAPI::class, 'show'])->name('show');
    Route::patch('/{permissions}', [permissionsControllerAPI::class, 'update'])->name('update');
    Route::delete('/{permissions}', [permissionsControllerAPI::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| permission_role endpoints
|--------------------------------------------------------------------------
 */
Route::name('permission_roles.')->prefix('permission_roles')->group(function () {
    Route::get('/', [permission_roleControllerAPI::class, 'index'])->name('index');
    Route::post('/', [permission_roleControllerAPI::class, 'store'])->name('create');
    Route::get('/{permission_role}', [permission_roleControllerAPI::class, 'show'])->name('show');
    Route::patch('/{permission_role}', [permission_roleControllerAPI::class, 'update'])->name('update');
    Route::delete('/{permission_role}', [permission_roleControllerAPI::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| roles endpoints
|--------------------------------------------------------------------------
 */
Route::name('roles.')->prefix('roles')->group(function () {
    Route::get('/', [rolesControllerAPI::class, 'index'])->name('index');
    Route::post('/', [rolesControllerAPI::class, 'store'])->name('create');
    Route::get('/{roles}', [rolesControllerAPI::class, 'show'])->name('show');
    Route::patch('/{roles}', [rolesControllerAPI::class, 'update'])->name('update');
    Route::delete('/{roles}', [rolesControllerAPI::class, 'destroy'])->name('destroy');
});

