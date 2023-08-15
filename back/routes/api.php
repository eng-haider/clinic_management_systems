<?php



    // Route::group(['prefix' => '/testts'], function ($app) {
       
        // });

        

Route::get('offerchange', 'API\OfferController@ChangeOffersStatus');


$subjects = [
    'operators', 'info', 'client', 'owner',
];

Route::prefix('/v2')->group(function () {
Route::apiResources(['Categories' => 'API\CategoryController']);

});




Route::get('sendPhoneNotification/{id}', 'API\NotificationController@sendPhoneNotification');
foreach ($subjects as $subject) {
    Route::prefix('v2/'.$subject)->group(function () {
        Route::apiResources(['Categories' => 'API\CategoryController']);
        Route::apiResources(['Provinces' => 'API\ProvinceController']);
        Route::apiResources(['Countries' => 'API\CountryController']);
        
        Route::apiResources(['LastCategory' => 'API\LastCategoryController']);
        Route::apiResources(['SubCategories' => 'API\SubCategoryController']);
        Route::apiResources(['Address' => 'API\AddressController']);
        Route::get('getAllAdvTypes', 'API\AdvertisingTypeController@index');


        Route::get('getSubCategoryWithCategory', 'API\CategoryController@getSubCategoryWithCategory');

        Route::get('subCategoryCat_id/{id}', 'API\SubCategoryController@GetByCategoryId');
        Route::get('lastCategorySubCat_id/{id}', 'API\LastCategoryController@GetBySubCategoryId');

        //Items
        Route::get('/search', 'API\ItemController@search'); //Seach

        // Route::group(['middleware' => ['OwnerSubscriptionsPackagesMiddleware']], function () {
            Route::get('/search', 'API\ItemController@search'); //Seach
        // });

        Route::get('getByCatId/{id}', 'API\ItemController@getByCatId'); //Get By Cat_id

    });
}
;
//Category
Route::group(['prefix' => 'v2/Category', 'middleware' => ['CheckRole:admin']], function ($app) {
Route::delete('delete_image/{id}', 'API\CategoryController@DeleteImage');

});


//SubCategory
Route::group(['prefix' => 'v2/SubCategory', 'middleware' => ['CheckRole:admin']], function ($app) {
    Route::delete('delete_image/{id}', 'API\SubCategoryController@DeleteImage');
    });


    //LastCategory
Route::group(['prefix' => 'v2/LastCategory', 'middleware' => ['CheckRole:admin']], function ($app) {
    Route::delete('delete_image/{id}', 'API\LastCategoryController@DeleteImage');
    });


Route::group(['prefix' => 'v2/Offers'], function ($app) {
    Route::post('/store', 'API\OfferController@store');
    Route::get('/show/{id}', 'API\OfferController@show');
    Route::post('/update/{id}', 'API\OfferController@update');
    
    Route::group(['prefix' => 'owner', 'middleware' => ['CheckRole:owner']], function ($app) {
        Route::delete('delete/{id}', 'API\OfferController@destroy');
        Route::get('Offers', 'API\OfferController@getOwnerOffers'); //Get  Reservertions Between Date
    });
  

});



Route::post('/contactus', 'API\ContactUsController@store');
Route::middleware('auth:api', 'scopes:owner')->group(function () {
    Route::get('test', function () {
        return "all world its ok";
    });
});

// api/v2/Items/{}
// http://127.0.0.1:8005/apiv/2/Items/

// Route::get('PayByZainCash', 'API\BillsController@ZainCashPay');
Route::get('decodeZainCashToken/{token}', 'API\BillsController@decodeZainCashToken');
Route::get('ModifyTablePaymenytMethod', 'API\BillsController@ModifyTablePaymenytMethod');

Route::group(['prefix' => 'Payment'], function ($app) {
Route::group(['prefix' => 'ZainCash'], function ($app) {
    Route::group(['middleware' => ['CheckRole:client']], function ($app) {
          Route::get('Pay/{res_id}', 'API\BillsController@ZainCashPay');
    });

});


Route::group(['prefix' => 'Tasdid'], function ($app) {

    Route::group(['middleware' => ['CheckRole:client']], function ($app) {
          Route::post('Pay', 'API\BillsController@TasdidPay');
    });

});
});

Route::group(['prefix' => 'auth/v2'], function ($app) {
    Route::post('logout', 'API\UserController@logout');
    
    // Route::group(['middleware' => ['throttle:20,1']], function () {
   
    Route::post('login', 'API\UserController@login');
    Route::post('loginOwner', 'API\UserController@loginOwner');
    Route::post('loginOwnerSmart', 'API\UserController@loginOwnerSmart');
    
    Route::post('loginOpertion', 'API\UserController@loginOpertion');
    Route::post('signUpClient', 'API\UserController@signUpClient');
    Route::post('signUpOthers', 'API\UserController@signUpOthers');
// });
    Route::post('OperationLoginOtp', 'API\UserController@OperationLoginOtp');
    Route::get('accountActivate/{otpNumber}', 'API\UserController@accountActivate');
    Route::get('/refresh', 'API\UserController@refresh');

    Route::group(['middleware' => ['CheckRole:client']], function ($app) {
        Route::post('updateInfo', 'API\UserController@update');
    });




    
  
    // Route::group(['prefix' => 'forgotPassword','middleware' => ['throttle:16,1']], function () {
        Route::post('checkPhone', 'API\UserController@forgotPassword');
        Route::post('changePassword', 'API\UserController@changePassword');
    // });
});



Route::group(['prefix' => 'v2/items'], function ($app) {

    Route::get('/', 'API\ItemController@index');
	 Route::get('/OwnerItem', 'API\ItemController@OwnerItem');


     Route::get('/HomeItems', 'API\ItemController@HomeItems');
	
    Route::get('show/{id}', 'API\ItemController@show');
    Route::post('/storex', 'API\ItemController@store');
    Route::post('/UploudeImage/{item_id}', 'API\ItemController@UploudeImage');

    
    Route::post('/update/{id}', 'API\ItemController@update');
    Route::post('delete_image/{id}', 'API\ItemController@DeleteImage');
    Route::DELETE('delete/{id}', 'API\ItemController@destroy');
	Route::get('getAllItemsTitle', 'API\ItemController@getAllItemsTitle');
    Route::get('/', 'API\ItemController@index');
    Route::get('show/{item}', 'API\ItemController@show');
    Route::post('/store', 'API\ItemController@store');
    Route::post('/update/{id}', 'API\ItemController@update');
    Route::get('/search', 'API\ItemController@search'); //Seach
    //Route::get('owner', 'API\ItemController@getByOwnerId');
  
    Route::delete('deleteTimeOpen/{id}', 'API\ItemController@destroyTimeOpen');
  
    Route::group(['prefix' => 'owner', 'middleware' => ['CheckRole:owner']], function ($app) {
        Route::get('get', 'API\ItemController@getByOwnerId');
        Route::get('OwnerItemsById/{id}', 'API\ItemController@OwnerItemshow');
        Route::get('GetDeletedItems', 'API\ItemController@GetOwnerDeletedItems');
    
        Route::get('return_delete/{id}', 'API\ItemController@return_delete');
        Route::post('/UpdateDayOpenByItemId/{item_id}', 'API\ItemController@UpdateDayOpenByItemId');
       
        

    });


});


Route::group(['prefix' => 'v2/Favorites'], function ($app) {



        Route::post('setItemFavorite', 'API\FavoritesController@storeFavoriteItem')->middleware(['CheckRole:client']);;
        Route::get('getItemFavorite', 'API\FavoritesController@GetUserFavorties')->middleware(['CheckRole:client']);;

        


   
});


Route::group(['prefix' => 'v2/currency_transfers'], function ($app) {
  
    Route::group(['prefix' => '/owner', 'middleware' => ['CheckRole:owner']], function ($app) {
        Route::get('get', 'API\CurrencyTransfersController@getByOwnerId');
        Route::post('update', 'API\CurrencyTransfersController@update');
        Route::post('set', 'API\CurrencyTransfersController@store');


        
        
    });
});


Route::group(['prefix' => 'v2/ItemsReservationRequirements'], function ($app) {
  
    Route::group(['prefix' => '/owner', 'middleware' => ['CheckRole:owner']], function ($app) {
        Route::Post('store', 'API\ItemsReservationRequirementsController@store');
        Route::Post('update/{id}', 'API\ItemsReservationRequirementsController@update');
        Route::delete('delete/{id}', 'API\ItemsReservationRequirementsController@destroy');
    });
});

Route::group(['prefix' => 'v2/ReservationRequirements'], function ($app) {
  
    Route::group(['prefix' => '/user', 'middleware' => ['CheckRole:client']], function ($app) {
        Route::Post('store', 'API\ReservationRequirementsController@store');
    });
});

Route::group(['prefix' => 'owner/v2', 'middleware' => ['CheckRole:owner']], function ($app) {
    Route::get('OwnerInfo', 'API\OwnerController@show');
    Route::Post('UpdateInfo', 'API\OwnerController@Update');
    Route::get('myoffers', 'API\OfferController@getOwnerOffers');
     
    Route::get('OwnerResCount', 'API\ReservationController@OwnerResCount');
    Route::Post('UpdatePassword', 'API\UserController@UpdatePassword');
    Route::delete('DeleteImage/{id}', 'API\UserController@DeleteImage');
    Route::get('StatusByStatusType/{id}', 'API\StatusController@GetStatusByStatusType');

    Route::group(['prefix' => 'address'], function ($app) {
        Route::get('get', 'API\OwnerController@getAddress');
        Route::post('set', 'API\OwnerController@addAddress');
        Route::post('update/{id}', 'API\OwnerController@updateAddress');
        Route::delete('delete/{id}', 'API\AddressController@destroy');
   
    });
    


    //Get  Reservertions Between Date

    //Owner Items Group
    //  Route::group(['prefix' => 'Items','middleware' => ['CheckRole:client']],function($app){
    //     Route::apiResources(['/'=> 'API\ItemController']); //Get  Reservertions
    //     // Route::get('getOwnerItems','API\ItemController@getOwnerItems');

    //   });

    //Owner Reservertion Group
    Route::group(['prefix' => 'reservation', 'middleware' => ['CheckRole:client']], function ($app) {
        //Route::apiResources(['/' => 'API\ReservationController']); //Get  Reservertions
        Route::get('getBetweenDate', 'API\ReservationController@getBetweenDate'); //Get  Reservertions Between Date
      ///  Route::get('ChangeStatus', 'API\ReservationController@changeReservationStatus'); //Get  Reservertions Between Date

    });

});

//Owner Reservertion Group
//Route::apiResources(['v2/reservation' => 'API\ReservationController']);
Route::group(['prefix' => 'v2/reservation'], function ($app) {

    
    Route::post('GetAvilableReservationPeriod/{date}', 'API\ReservationController@GetAvilableReservationPeriod');
    Route::get('search', 'API\ReservationController@search'); //Get  Reservertions Between Date




    Route::post('GetAvilableReservationPeriods', 'API\ReservationController@GetAvilableReservationPeriod');
    Route::group(['middleware' => ['CheckRole:owner']], function ($app) {
        Route::get('getBetweenDate/{first_date}/{first_second}', 'API\ReservationController@getBetweenDate'); //Get  Reservertions Between Date
        Route::get('ChangeStatus', 'API\ReservationController@changeReservationStatus'); //Get  Reservertions Between Date
        Route::get('getOwnerReservs', 'API\ReservationController@getByOwnerId');
        Route::get('getReservCounts', 'API\ReservationController@getOwnerReservCounts'); //Get  Reservertions Between Date
        Route::post('OwnerChangeStatus/{id}', 'API\ReservationController@owner_change_reservation_status');
        
           Route::delete('delete/{id}', 'API\ReservationController@destroy');
           

    });

    Route::group(['middleware' => ['CheckRole:client']], function ($app) {
        Route::get('/all', 'API\ReservationController@getByUserId'); //Get  Client Reservertions
        Route::get('/count', 'API\ReservationController@getReservationsCountByUserId');
        Route::post('/store', 'API\ReservationController@store');

    });

});

Route::group(['prefix' => 'client/v2', 'middleware' => ['CheckRole:client']], function ($app) {

    Route::group(['prefix' => 'reservation', 'middleware' => ['CheckRole:client']], function ($app) {
        Route::get('/', 'API\ReservationController@getByUserId'); //Get  Client Reservertions
        Route::get('/count', 'API\ReservationController@getReservationsCountByUserId');
        Route::get('cancel/{id}', 'API\ReservationController@UserCancelReservation'); //Cancel  Reservertions

    });

});
//
Route::group(['prefix' => 'user' ,'middleware' => 'CheckRole:client'], function ($app) {
        Route::get('getBills/{status}', 'API\BillsController@getUserBills');
        
});



Route::group(['prefix' => 'bills' ], function ($app) {


Route::group(['prefix' => 'user' ,'middleware' => 'CheckRole:client'], function ($app) {
    Route::get('getBills/{status}', 'API\BillsController@getUserBills');
    Route::get('show/{id}', 'API\BillsController@show');
});


Route::group(['prefix' => 'operation' ,'middleware' => 'CheckRole:admin'], function ($app) {
    Route::get('getAllBills', 'API\BillsController@index');
    Route::get('search', 'API\BillsController@search');
    
});

Route::group(['prefix' => 'owner' ,'middleware' => 'CheckRole:owner'], function ($app) {

    Route::group(['prefix' => 'tasdid'], function ($app) {
        Route::get('get', 'API\BillsController@GetOwnerTasdidBill');
        
        
    });
    
    
    
});


});



Route::group(['prefix' => 'operation','middleware' => 'CheckRole:admin'], function ($app) {
    Route::get('changeOwnerStatus/{statusId}/{userId?}', 'API\UserController@changeStatus');
    Route::get('getAllOwners', 'API\OwnerController@index');
   
    Route::get('getOwnersByStatusId/{status}', 'API\OwnerController@getOwnersByStatusId');
    Route::get('getOwnersByBillsStatusId/{status}', 'API\OwnerController@getOwnersByBillsStatusId');
    Route::PATCH('update', 'API\OwnerController@index');
	 Route::post('delete_image/{id}', 'API\ItemController@DeleteImage');
     Route::post('UpdateOwnerInfo', 'API\UserController@UpdateOwnerByOpertions');
     Route::post('OwnerAccountActivate/{otpNumber}', 'API\UserController@accountActivateByOpertion');
     Route::get('SearchOwner', 'API\OwnerController@Search');
     Route::get('/contactus', 'API\ContactUsController@index');

});

///Route::post('upload/{model}', 'API\UploadController@store');

////reservation

Route::group(['prefix' => 'notifications'], function () {
    Route::get('AlertUserRservationApproaches', 'API\NotificationController@AlertUserRservationApproaches');

    Route::group(['prefix' => 'user','middleware' => ['CheckRole:client']], function () {
        
        Route::get('get', 'API\NotificationController@GetUserNotifications');
    });
    Route::group(['prefix' => 'reservations'], function () {
    Route::group(['prefix' => 'owner','middleware' => ['CheckRole:owner']], function () {
        Route::get('get', 'API\NotificationController@GetOwnerRerserverationsNotifications');
    });
});

});
Route::group(['prefix' => 'reservation'], function () {

    Route::group(['prefix' => 'user','middleware' => ['CheckRole:client']], function () {
        Route::post('set', 'API\ReservationController@store');
        Route::post('update', 'API\ReservationController@update');
        Route::get('get', 'API\ReservationController@getUserReservations');
        Route::get('count', 'API\ReservationController@getUserReservationsCount');
        Route::post('changeStatus', 'API\ReservationController@changeStatusByUser');
        Route::get('search', 'API\ReservationController@SearchUser');

    });

    Route::get('SearchAvilableReservation', 'API\ReservationController@SearchAvilableReservation');

    Route::group(['prefix' => 'owner','middleware' => ['CheckRole:owner']], function () {
        Route::get('get', 'API\ReservationController@getOwnerReservations');
        Route::post('set', 'API\ReservationController@storeOwner');
        Route::get('getBetweenDates/{owner_id}/{first_date}/{second_date}', 'API\ReservationController@getBetweenDateOwner');
        Route::get('dashboard', 'API\ReservationController@getOwnerDashboardCounts');
        Route::post('changeStatus', 'API\ReservationController@changeStatusByOwner');
        Route::get('getAllPartnership', 'API\PartnershipController@index');
        Route::post('addPartnership', 'API\PartnershipController@store');
        Route::post('updatePartnership', 'API\PartnershipController@update');
        Route::post('partnershipChangeStatus', 'API\PartnershipController@changeStatus');
        Route::get('getCompaniesDelivery', 'API\OwnerController@getAllOwnersDelivery');
        Route::get('search', 'API\ReservationController@SearchOwner');
    

    });
  
});

// free data


//ItemFeatures Route
Route::group(['prefix' => 'ItemFeatures'], function () {
    Route::group(['prefix' => 'owner','middleware' => ['CheckRole:owner']], function () {
        Route::delete('delete/{id}', 'API\ItemFeaturesController@destroy');
    });
});


//ratings Route
Route::group(['prefix' => 'ratings'], function () {

    Route::group(['prefix' => 'user','middleware' => ['CheckRole:client']], function () {
        Route::get('get', 'API\RateController@show');
        Route::post('set', 'API\RateController@store');

    });

    Route::group(['prefix' => 'owner','middleware' => ['CheckRole:owner']], function () {
        Route::get('get', 'API\RateController@GetItemsRateByOwnerId');

    });
    Route::group(['prefix' => 'delivery','middleware' => ['jwt.auth.delivery']], function () {

    });
    Route::group(['prefix' => 'driver','middleware' => ['jwt.auth.driver']], function () {

    });
});




Route::group(['prefix' => 'info'], function () {
    Route::group(['prefix' => 'status'], function () {
        Route::get('getUser', 'API\StatusController@getByUser');
    });

    Route::group(['prefix' => 'province'], function () {

    });

    Route::group(['prefix' => 'category'], function () {

    });

    Route::group(['prefix' => 'sunCategory'], function () {

    });
});




//brand
Route::group(['prefix' => 'brand'], function () {
    Route::get('get', 'API\BrandController@index');
    Route::get('getBrandAgency', 'API\BrandAgencyOwnerController@index');

    Route::group(['prefix' => 'user','middleware' => ['CheckRole:client']], function () {

    });
    Route::group(['prefix' => 'owner','middleware' => ['CheckRole:owner']], function () {

    });
    Route::group(['prefix' => 'operation','middleware' => ['CheckRole:admin']], function () {
        Route::post('set', 'API\BrandController@store');
        Route::post('update', 'API\BrandController@update');
      
        Route::get('delete', 'API\BrandController@destroy');
        Route::post('addBrandAgency', 'API\BrandAgencyOwnerController@store');
        Route::post('updateBrandAgency', 'API\BrandAgencyOwnerController@update');
        Route::get('deleteBrandAgency', 'API\BrandAgencyOwnerController@destroy');
    });
});




Route::group(['prefix' => 'operation','middleware' => ['CheckRole:admin']], function () {
   Route::get('getAllOwnersDelivery', 'API\OwnerController@getAllOwnersDelivery');
   Route::delete('DeletedUser/{id}', 'API\UserController@DeletedUser');
    Route::post('OwnerChangeStatus', 'API\OwnerController@changeStatusOwner');
    
    Route::get('getAdminDashbourdCounts', 'API\UserController@getAdminDashbourdCounts');
    Route::get('getAllOwnersUsers', 'API\UserController@getAllOwnersUsers');
   
    
    
});


// Advertising


Route::group(['prefix' => 'v2/Advertising'], function () {

    Route::group(['prefix' => 'operation','middleware' => ['CheckRole:admin']], function () {
        Route::post('addAdvType', 'API\AdvertisingTypeController@store');
        Route::post('updateAdvType/{id}', 'API\AdvertisingTypeController@update');
        Route::get('getTypeById/{id}', 'API\AdvertisingTypeController@show');
        Route::get('getAllAdvType', 'API\AdvertisingTypeController@index');

        //AdvertisingTypeController
        Route::get('getAllAdvs', 'API\AdvertisingController@index');
        Route::post('changeStatus/{id}', 'API\AdvertisingController@changeStatus');
        Route::get('delete', 'API\AdvertisingTypeController@destroy');

        
    });

    Route::group(['prefix' => 'owner','middleware' => ['CheckRole:owner']], function () {
        Route::post('addAdv', 'API\AdvertisingController@store');
        Route::post('updateAdv/{id}', 'API\AdvertisingController@update');
        Route::get('getMyAdvs', 'API\AdvertisingController@getByOwnerID');
        Route::get('unActive/{id}', 'API\AdvertisingController@unActive');
        Route::get('delete/{id}', 'API\AdvertisingController@destroy');
        Route::get('getById/{id}', 'API\AdvertisingController@show');
       
    });

    Route::group(['prefix' => 'client'], function () {
       // Route::get('getByCategory/{id}', 'API\AdvertisingController@getItemsAdv');
       Route::get('getItemsAdv', 'API\AdvertisingController@getItemsAdv');
        Route::get('getOwners', 'API\AdvertisingController@getOwnersAdv');
    });
});


// SubscriptionPackages
Route::group(['prefix' => 'v2/SubscriptionPackages'], function () {
       Route::get('get', 'API\SubscriptionPackagesController@index');
    Route::group(['prefix' => 'operation','middleware' => ['CheckRole:admin']], function () {
        Route::post('set', 'API\SubscriptionPackagesController@store');
        Route::get('get', 'API\SubscriptionPackagesController@index');
        Route::post('update/{id}', 'API\SubscriptionPackagesController@update');
       
    });

});



//OwnerSubscriptions
Route::group(['prefix' => 'v2/OwnerSubscriptions'], function () {
    Route::group(['prefix' => 'owner','middleware' => ['CheckRole:owner']], function () {
    Route::post('set', 'API\OwnerSubscriptionsController@store');
    Route::post('update/{id}', 'API\OwnerSubscriptionsController@update');
    Route::get('get', 'API\OwnerSubscriptionsController@GetOwnerSubscriptions');
    });

    Route::group(['prefix' => 'operation','middleware' => ['CheckRole:admin']], function () {
        Route::get('get', 'API\OwnerSubscriptionsController@index');
   
        Route::post('changeStatue', 'API\OwnerSubscriptionsController@changeStatue');
        });

});

Route::group(['prefix' => 'v2/ItemFeatures'], function () {
    Route::group(['prefix' => 'owner','middleware' => ['CheckRole:owner']], function () {
    Route::post('set', 'API\ItemFeaturesController@store');
    Route::post('update/{id}', 'API\ItemFeaturesController@update');
  
    });

   

});





/// Web Hook api for tasdid
Route::post('pushStatus', 'API\TasdidBillsController@store');
// });


