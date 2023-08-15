<?php

namespace App\Http\Controllers\API;
use Validator;
use App\Image;
use App\Price;
use App\DayToOpen;
use App\TimeToOpen;
use App\Address;
use App\Item;
use App\SubTime;
use App\Owner;
use App\ItemsAddresses;
use JWTAuth;
use App\CurrencyTransfers;
use App\Status;
use App\sub_category;
use App\ItemFeatures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ItemsCollection;
use App\Http\Resources\ItemsCollectionUser;
use Illuminate\Routing\Controller;
use App\Http\Resources\ItemsResource;
use App\Http\Resources\ItemsSearchResource;
use App\Http\Resources\ItemsSearchCollection;
use App\Http\Resources\ItemsResourceUser;
use App\Http\Resources\ItemResourceOwner;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UploadImageController;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class ItemController extends Controller
{
	
	

    const ActiveStatusId =1;


    public function __construct()
    {
        $this->middleware('CheckRole:owner')->only(['destroy','update','store','getByOwnerId','destroyTimeOpen']);
    }


    public function index()
    {

      return new Itemscollection(Item::orderBy('id','DESC') ->where('status_id','!=',25)->paginate(15));

    }

    public function OwnerItem()
    {
         $items=Item::groupBy('owner_id')
         ->where('status_id','!=',25)
         ->get();
         return new ItemsCollectionUser($items);

    }

  public function  getAllItemsTitle()
  {
   return $items=Item::select('item_name')->get();
 
  }



  public function HomeItems(request $request)
  {
    $lat = $request->header('lat');
    $lon = $request->header('long');
      $items = QueryBuilder::for(Item::class)

     ->join( 'address_item', 'address_item.item_id', '=',  'items.id')
     ->join('addresses','addresses.id','=','address_item.address_id')
     ->where('status_id','!=',25)
     ->WhereHas('owner.currentOwnerSubscription', function ($query)  {$query->where('status_id', '=',29)->where('remaining_reservations_number', '>',0);})
     ->WhereHas('owner.user', function ($query)  {$query->where('status_id', '=', 1);})
     ->allowedSorts('id','item_name','distance')
     ->allowedFilters(['item_name','owner.owner_barnd_name','LastCategory.id','owner.user_id','item_description','user.id','status.id','sub_category.id','owner_id','sub_category.category.id','SubCategory.id','address.province.id','address.province.country_id',AllowedFilter::scope('Getoffers'),AllowedFilter::scope('GetByCityZipCode')])
     ->select('items.*',DB::raw("6371 * acos(cos(radians('$lat'))   * cos(radians(addresses.lat))  * cos(radians(addresses.long) - radians('$lon')) + sin(radians('$lat'))* sin(radians(addresses.lat))) AS distance")  )
     ->orderBy('distance','ASC')->groupBy('owner_id')
   
     ->paginate(15);


     return new  ItemsSearchcollection($items);


  }

public function search(request $request)
  {
   

DB::commit();


      $lat = $request->header('lat');
      $lon = $request->header('long');
      $items = QueryBuilder::for(Item::class)

       ->join( 'address_item', 'address_item.item_id', '=',  'items.id')
       ->join('addresses','addresses.id','=','address_item.address_id')
       ->where('status_id','!=',25) 
       
      // ->with(['favorite'=>function($query) use($user_id) {    if($user_id !=='not_found') {$query->where('user_id', '=',$user_id)->where('favoriteable_id', '=','.items.id.')->get();}  else{  $query->where('user_id', '=','12123134124312412488884343412412'); }   }])         
       ->WhereHas('owner.currentOwnerSubscription', function ($query)  {$query->where('status_id', '=',29)->where('remaining_reservations_number', '>',0);})
       ->WhereHas('owner.user', function ($query)  {$query->where('status_id', '=', 1);})
       ->allowedSorts('id','item_name','distance')
       ->allowedFilters(['item_name','owner.owner_barnd_name','LastCategory.id','owner.user_id','item_description','user.id','status.id','sub_category.id','owner_id','sub_category.category.id','SubCategory.id','address.province.id','address.province.country_id',AllowedFilter::scope('Getoffers'),AllowedFilter::scope('GetByCityZipCode')])
       ->select('items.*',DB::raw("6371 * acos(cos(radians('$lat'))   * cos(radians(addresses.lat))  * cos(radians(addresses.long) - radians('$lon')) + sin(radians('$lat'))* sin(radians(addresses.lat))) AS distance") 

       )

       ->groupBy('items.id')
       ->paginate(15);


      return new  ItemsSearchcollection($items);
   

  }

public function StoreAdress($owner,$item,$Address,$address_id,$request)
{
  
    if(count($address_id)==0)
    {

            if($request->long==null || $request->lat==null  || !isset($request->lat)  || !isset($request->long))
            {
             
                $ip =$_SERVER['REMOTE_ADDR'];
                $access_key = '404b133e44aa0ced6faaf521cb09368c';
                $ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $json = curl_exec($ch);
                curl_close($ch);
                $api_result = json_decode($json, true);
                $long=$api_result['longitude'];
                $lat=$api_result['latitude'];
                
        
            }
            else
            {
                $long=$Address['long'];
                $lat=$Address['lat'];


            }

            
       
            $owner_Address= $owner->address()->create(
              [
                  'province_id' =>$Address['province']['id'],
                  'long' =>$long,
                  'lat' =>$lat,
                  'address_descraption' =>$Address['address_descraption'],
              ]);
              $ItemsAddresses=new ItemsAddresses;
              $ItemsAddresses->item_id=$item->id;
              $ItemsAddresses->address_id =$owner_Address->id;
              $ItemsAddresses->save();


     


      
    
  

    }
     else
     {

        for($i=0;$i<count($address_id);$i++)
        {
            $ItemsAddresses=new ItemsAddresses;
            $ItemsAddresses->item_id=$item->id;
            $ItemsAddresses->address_id =$address_id[$i];
            $ItemsAddresses->save();

        }
         
       
       



     }

     $item->save();


}
    public function store(Request $request)
    {


            $validator = Validator::make($request->all(), [
                'item_name' => 'required',
                'item_description' => 'required', 
                'sub_category_id' => 'required|numeric',
                'status_id' => 'required|numeric',
              //  'last_category_id' => 'numeric',
                'Addres*.province_id' => 'required|numeric',
                'price*.price_value'=> 'required|numeric',
                'price*.payment_when_receiving'=> 'required',
                'price*.is_dollars'=> 'required',
                'number_of_items'=>'numeric',
                'price*.free_cancellation'=> 'numeric',
                'work_times' => 'array',
                'price*.deduction'=> 'numeric',
                'possib_reserving_period'=>'numeric',
               // 'work_times'=>'array|required'

                ]);

                
                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'Data' =>  $validator->messages()
                    ], 400);

                    }
                    DB::beginTransaction();

                    try {
                    $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
                    $item = new Item;
                    $item->item_name = $request->item_name;
                    $item->owner_id =$owner->id;
                    $item->sub_category_id = $request->sub_category_id;
                    $item->item_description = $request->item_description;
                    $item->last_category_id=$request->last_category_id;
                    $item->possib_reserving_period=$request->possib_reserving_period;
                //    $item->status_id=$request->status_id; 
                        $item->status_id=11; 
                    

                     $item->save();

                     $this->StoreAdress($owner,$item,$request->Address,$request->address_id,$request);




          if(count($request->ItemFeatures)!==0)
          {
              for($i = 0;$i <count($request->ItemFeatures);$i++)
              {
                $ItemFeatures = new ItemFeatures;
                $ItemFeatures->item_id =  $item->id;
                $ItemFeatures->feature_name = $request->ItemFeatures[$i]['feature_name'];
                $ItemFeatures->feature_price = $request->ItemFeatures[$i]['feature_price'];
                $ItemFeatures->save();

              }


            }

           

 

          
          $price = new Price;
          $price->item_id=$item->id;
          $price->is_tasdid_payment =$request->price['is_tasdid_payment']==true && ($request->price['payment_when_receiving']==2 || $request->price['payment_when_receiving']==1 );
          $price->is_zaincash_payment = $request->price['is_zaincash_payment']==true && ($request->price['payment_when_receiving']==2 || $request->price['payment_when_receiving']==1 );
          $price->price_value =$request->price['price_value'];
          $price->is_dollars =$request->price['is_dollars'];
          $price->payment_when_receiving =$request->price['payment_when_receiving'];
          $price->free_cancellation = $request->price['free_cancellation'] ;
          $price->deduction =$request->price['deduction'];
          $price->cancellation_deduction_ratio =$request->price['cancellation_deduction_ratio'] ;
          $price->save();
          $this->StoreWorkTime($request->work_times,$item);



        DB::commit();
        return response()->json([
            'success' => true,
            'data' => null,
            'item_id'=>$item->id
        ], 200);
        }
         catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'data' => $e->getMessage(),
           
        ], 400);
        }


}


        public function StoreWorkTime($work_times,$item)
        {
            for($i = 0;$i < 7;$i++){
                if(count($work_times[$i]['time_to_open'])>0)
                {
                        if($work_times[$i]['status']['id']==23)
                        {
                            $day_status_id=23;
                        }
                        else
                        {
                            $day_status_id=22;

                        }                       

                }
                else
                {
                    $day_status_id=22;

                }

                $work_times[$i]['every_day'];
                    
                $day=$item->day_to_open()->create(
                    [
                        'work_day' =>$work_times[$i]['work_day'],
                        'every_day' =>$work_times[$i]['every_day'],
                        'status_id'=>$day_status_id,
                    ]);



                    for($j=0;$j<count($work_times[$i]['time_to_open']);$j++)
                    {
                       
                     
                        $time = new TimeToOpen;
                        $reservations_number=1;
                        $time->day_to_open_id =$day->id;
                        $status_id=22;
                        $time->from_time = $work_times[$i]['time_to_open'][$j]['from_time'];
                        $time->to_time = $work_times[$i]['time_to_open'][$j]['to_time'];
                        $time->every_time =1;
                        $time->status_id=$work_times[$i]['time_to_open'][$j]['status']['id'];
                        $time->status_id=23;
                        $time->reservation_duration=$work_times[$i]['time_to_open'][$j]['reservation_duration'];
                        $time->reservation_type_id=$work_times[$i]['time_to_open'][$j]['reservation_type']['id']==null?1: $work_times[$i]['time_to_open'][$j]['reservation_type']['id'];
                        $time->reservations_number=$work_times[$i]['time_to_open'][$j]['reservations_number'];
                        $time->save();
                        
                        if(count($work_times[$i]['time_to_open'])>0)    
                        {

                            // if (in_array("SubTime", $work_times[$i]['time_to_open'][$j])) {
                           

                                for($s=0;$s<count($work_times[$i]['time_to_open'][$j]['SubTime']);$s++)
                                {
                                   
                                    $sub_time=new SubTime();
                                    $sub_time->time_to_open_id=$time->id;
                                    $sub_time->from_sub_time=$work_times[$i]['time_to_open'][$j]['SubTime'][$s]['from_sub_time'];
                                    $sub_time->to_sub_time=$work_times[$i]['time_to_open'][$j]['SubTime'][$s]['to_sub_time'];
                                    $sub_time->save();

                                }

                          //  }
             

                    }


                    }








        }
        }


    public function UploudeImage(Request $request,$item_id)
    {
        $item_id=(int)$item_id;

        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1'
            ]);

            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        $item=Item::where('id','=',$item_id)->where('owner_id','=',$owner->id)->first();

        if(count($request->images)!==0)
        {

            try{

                $upload = new UploadImageController;
                $path = $upload->uploadInner($request);
               
                $item->image()->create(
                [
                'image_url' =>$path[0],
                ]);
                return response()->json([
                    'success' => true,
                    'data' =>null
                ], 200);

        

            }
            catch(\Illuminate\Database\QueryException $e){

                 DB::rollback();
              
                 return response()->json([
                    'success' => false,
                    'Data' => $e->getMessage()
                ], 400);

             }
            

       


        }

    }
    public function show($id)
    {


        

    

        try {
             $item=new ItemsResourceUser(Item::where('id','=',$id)->where('status_id','!=',25)->WhereHas('owner.user', function ($query)  {$query->where('status_id', '=', 1);})
           ->WhereHas('owner.currentOwnerSubscription', function ($query)  {$query->where('status_id', '=',29)->where('remaining_reservations_number', '>',0);})
           ->first());
           $success=true;

        } catch (\Illuminate\database\QueryException $e) {
           
            DB::rollback();
            return response()->json([
                'success' => false,
                'data' => null,
            ], 400);

        }

        if($success)
        {
            return $item;

        }
        else

        {
            return response()->json([
                'success' => false,
                'data' => null,
            ], 400);

        }



    }


    public function OwnerItemshow($id)
    {

        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id )->first();
  
        return $item=new ItemResourceOwner(Item::where('id','=',$id)->where('owner_id','=',$owner->id)->where('status_id','!=',25)->first());



    }


    public function GetOwnerDeletedItems()
    {
      
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
  
        return $item=new ItemsCollectionUser(Item::where('owner_id','=',$owner->id)->where('status_id','=',25)->paginate(15));

    }




    //item Reducing the number
    public function ItemReducingNum($res)
    {
        // $item=Item::find($res->item_id);
        // if($item->status_id !==26 && $item->number_of_items>0 && $item->number_of_items>=$res->reservation_number)
        // {
        // $item->number_of_items=$item->number_of_items-$res->reservation_number;
        // $item->save();
        // return true;
        // }
        // else
        // {
            
           

        //     $this->ChangeStatus($item->id,26);
        //     return false;
        // }
        
    }


    public function UpdateDayOpenByItemId(Request $request,$item_id)
    {
        $item_id=(int)$item_id;

        $owner_id=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->get('id');
        $item = Item::where('id','=',$item_id)->where('owner_id','=',$owner_id[0]->id)->first();
        if($item)
        {

        }
        else
        {
            return response()->json([
                'success' => false,
                'Data' => null
            ], 400); 
        }
        $item->status_id=$request->status_id;
        $item->save();


        $item=item::find($item_id);
        if(count($request->work_times)!==0)
        {
      $day=$item->day_to_open()->get();
      for($i = 0;$i<7;$i++){

          if(count($request->work_times[$i]['time_to_open'])>0)
          {


                  if($request->work_times[$i]['status']['id']==23)
                  {
                      $day_status_id=23;


                  }
                  else
          {
              $day_status_id=22;

          }



          }
          else
          {
              $day_status_id=22;

          }

          $day[$i]['status_id']=$day_status_id;
          $day[$i]['every_day']=$request->work_times[$i]['every_day'];
          
          $day[$i]->save();

          for($j=0;$j<count($request->work_times[$i]['time_to_open']);$j++)
          {

        if(isset($day[$i]['time_to_open'][$j]['id']))
        {
            $x=$day[$i]['time_to_open'][$j]['id'];
            $time=TimeToOpen::where('id',$x)->first();

                if(isset($time->from_time))
                {

                     $time->from_time= $request->work_times[$i]['time_to_open'][$j]['from_time'];
                     $time->to_time = $request->work_times[$i]['time_to_open'][$j]['to_time'];
                     $time->status_id=$request->work_times[$i]['time_to_open'][$j]['status']['id'];
                     $time->reservation_type_id=$request->work_times[$i]['time_to_open'][$j]['reservation_type']['id']==null?1: $request->work_times[$i]['time_to_open'][$j]['reservation_type']['id'];
                     $time->reservations_number=$request->work_times[$i]['time_to_open'][$j]['reservations_number'];
                     $time->save();




                }



        }




             else
             {

              $time = new TimeToOpen;
              $reservations_number=1;
              $time->day_to_open_id =$day[$i]['id'];
              $status_id=22;
              $time->from_time = $request->work_times[$i]['time_to_open'][$j]['from_time'];
              $time->to_time = $request->work_times[$i]['time_to_open'][$j]['to_time'];
              $time->every_time =1;
              $time->reservation_duration=$request->work_times[$i]['time_to_open'][$j]['reservation_duration'];
              $time->status_id=$request->work_times[$i]['time_to_open'][$j]['status']['id'];
              $time->reservation_type_id=$request->work_times[$i]['time_to_open'][$j]['reservation_type']['id']==null?1: $request->work_times[$i]['time_to_open'][$j]['reservation_type']['id'];
              $time->reservations_number=$request->work_times[$i]['time_to_open'][$j]['reservations_number'];
              $time->save();


                          for($s=0;$s<count($request->work_times[$i]['time_to_open'][$j]['SubTime']);$s++)
                          {
                             
                              $sub_time=new SubTime();
                              $sub_time->time_to_open_id=$time->id;
                              $sub_time->from_sub_time=$request->work_times[$i]['time_to_open'][$j]['SubTime'][$s]['from_sub_time'];
                              $sub_time->to_sub_time=$request->work_times[$i]['time_to_open'][$j]['SubTime'][$s]['to_sub_time'];
                              $sub_time->save();

                          }


             }





          }



     }
        }
    }

    public function ChangeStatus($item_id,$status_id)
    {
        $ownerStatus = ['QuantityFinished','unactive','active'];
     
        
        $status = Status::find($status_id);
       
        if (in_array($status->status_name, $ownerStatus)) {
            $item = Item::find($item_id);
            $item->status_id = $status_id;
            $item->save();
        }

        // return response()->json([
        //     'success' => true,
        //     'data' => null,
        // ], 200);
    }






     public function getByOwnerId()
     {

        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->first();
        return $item =new Itemscollection(Item::where('owner_id','=',$owner->id)->where('status_id','!=',25)->orderBy('id','DESC')->paginate(15));
     }







     public function update(Request $request,$id)
     {
 
         $id=(int)$id;
 
         $validator = Validator::make($request->all(), [
           'item_name' => 'required',
           'item_description' => 'required',
           'sub_category_id' => 'required|numeric',
           'Addres*.province_id' => 'required|numeric',
           'price*.price_value'=> 'required|numeric',
           'status_id' => 'required|numeric',
           'price*.is_dollars'=> 'required',
           'price*.payment_when_receiving'=> 'required',
           'number_of_items'=>'numeric',
           'price*.free_cancellation'=> 'numeric',
           "address_id"=>'required|array|min:1',
           'work_times' => 'array',
           'price*.deduction'=> 'numeric',
           'possib_reserving_period'=>'numeric'
           
             ]);
 
             if ($validator->fails()) {
                 return response()->json([
                     'success' => false,
                     'Data' =>  $validator->messages()
                 ], 400);
 
                 }
 
 
         $Data = null;
         DB::beginTransaction();
         try{
 
 
             //save Item
             $owner_id=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id)->get('id');
             $item = Item::where('id','=',$id)->where('owner_id','=',$owner_id[0]->id)->first();
             if($item)
             {
 
             }
             else
             {
                 return response()->json([
                     'success' => false,
                     'Data' => null
                 ], 400); 
             }
             $item->item_name = $request->item_name;
             $item->owner_id= $owner_id[0]->id;
             $item->sub_category_id = $request->sub_category_id;
             $item->item_description = $request->item_description;
             $item->number_of_items=$request->number_of_items;
             $item->last_category_id=$request->last_category_id;
             $item->deliverable_id=$request->deliverable_id;
             $item->possib_reserving_period=$request->possib_reserving_period;
             $item->status_id=$request->status_id;
             $item->save();






             //Update Address
             $oldAddresses=[];
            $OldItemsAddresses=ItemsAddresses::where('item_id','=',$item->id)->select('address_id')->get();
            for($i=0;$i<count( $OldItemsAddresses);$i++)
            {
                array_push($oldAddresses,$OldItemsAddresses[$i]->address_id);
            }
    
            $result=array_diff($oldAddresses,$request->address_id);
            foreach ($result as $key => $value) {
            $AddressesDelete=ItemsAddresses::where('item_id','=',$item->id)->where('address_id','=',$result[$key])->first();
            $AddressesDelete->delete();
            }
             for($i=0;$i<count($request->address_id);$i++)
             {
                 $OldItemsAddresses=ItemsAddresses::where('item_id','=',$item->id)->where('address_id','=',$request->address_id[$i])->get();
                 if(count($OldItemsAddresses)==0)
                 {
                     $ItemsAddresses=new ItemsAddresses;
                     $ItemsAddresses->item_id=$item->id;
                     $ItemsAddresses->address_id =$request->address_id[$i];
                     $ItemsAddresses->save();
 
                 }
            
             }
 
             
 
 
 
            
 
 
               
               
               $price=Price::where('item_id',$item->id)->first('id');
               $price->is_tasdid_payment =$request->price['is_tasdid_payment']==true && ($request->price['payment_when_receiving']==2 || $request->price['payment_when_receiving']==1 );
               $price->is_zaincash_payment = $request->price['is_zaincash_payment']==true && ($request->price['payment_when_receiving']==2 || $request->price['payment_when_receiving']==1 );
               $price->item_id = $item->id;
               $price->is_dollars =$request->price['is_dollars'];
               $price->price_value =$request->price['price_value'];
               $price->payment_when_receiving =$request->price['payment_when_receiving'];
               $price->free_cancellation = $request->price['free_cancellation'] ;
               $price->deduction =$request->price['deduction'];
               $price->cancellation_deduction_ratio =$request->price['cancellation_deduction_ratio'] ;
               $price->save();
 
             
 
 
 
           if(count($request->work_times)!==0)
               {
             $day=$item->day_to_open()->get();
             for($i = 0;$i<7;$i++){
                 
 
                 if(count($request->work_times[$i]['time_to_open'])>0)
                 {
 
 
                         if($request->work_times[$i]['status']['id']==23)
                         {
                             $day_status_id=23;
 
 
                         }
                         else
                 {
                     $day_status_id=22;
 
 
                 }
 
 
 
                 }
                 else
                 {
                     $day_status_id=22;
 
                 }
 
                $day[$i]['status_id']=$day_status_id;
                $day[$i]['every_day']=$request->work_times[$i]['every_day'];
                $day[$i]->save();
 
                 for($j=0;$j<count($request->work_times[$i]['time_to_open']);$j++)
                 {
 
               if(isset($day[$i]['time_to_open'][$j]['id']))
               {
                   $x=$day[$i]['time_to_open'][$j]['id'];
                   $time=TimeToOpen::where('id',$x)->first();
 
                       if(isset($time->from_time))
                       {
 
                            $time->from_time= $request->work_times[$i]['time_to_open'][$j]['from_time'];
                            $time->to_time = $request->work_times[$i]['time_to_open'][$j]['to_time'];
                            $time->status_id=$request->work_times[$i]['time_to_open'][$j]['status']['id'];
                            $time->reservation_type_id=$request->work_times[$i]['time_to_open'][$j]['reservation_type']['id']==null?1: $request->work_times[$i]['time_to_open'][$j]['reservation_type']['id'];
                            $time->reservations_number=$request->work_times[$i]['time_to_open'][$j]['reservations_number'];
                            $time->save();
 
 
 
 
                       }
 
 
 
               }
 
 
 
 
                    else
                    {
 
                     $time = new TimeToOpen;
                     $reservations_number=1;
                     $time->day_to_open_id =$day[$i]['id'];
                     $status_id=22;
                     $time->from_time = $request->work_times[$i]['time_to_open'][$j]['from_time'];
                     $time->to_time = $request->work_times[$i]['time_to_open'][$j]['to_time'];
                     $time->every_time =1;
                     $time->reservation_duration=$request->work_times[$i]['time_to_open'][$j]['reservation_duration'];
                     $time->status_id=$request->work_times[$i]['time_to_open'][$j]['status']['id'];
                     $time->reservation_type_id=$request->work_times[$i]['time_to_open'][$j]['reservation_type']['id']==null?1: $request->work_times[$i]['time_to_open'][$j]['reservation_type']['id'];
                     $time->reservations_number=$request->work_times[$i]['time_to_open'][$j]['reservations_number'];
                     $time->save();
 
 
                                 for($s=0;$s<count($request->work_times[$i]['time_to_open'][$j]['SubTime']);$s++)
                                 {
                                    
                                     $sub_time=new SubTime();
                                     $sub_time->time_to_open_id=$time->id;
                                     $sub_time->from_sub_time=$request->work_times[$i]['time_to_open'][$j]['SubTime'][$s]['from_sub_time'];
                                     $sub_time->to_sub_time=$request->work_times[$i]['time_to_open'][$j]['SubTime'][$s]['to_sub_time'];
                                     $sub_time->save();
 
                                 }
 
 
                    }
 
 
 
 
 
                 }
 
 
 
            }
               }
 
         
             $Data =  'dd';
 
         }catch(\Illuminate\Database\QueryException $e){
             dd($e->getMessage());
              DB::rollback();
              $Data = null;
          }
         DB::commit();
         if(empty($Data)){
             return response()->json([
                 'success' => false,
                 'Data' => null
             ], 400);
         }else{
             return response()->json([
                 'success' => true,
                 'Data' => null,
                 'item_id'=>$item->id
             ], 200);
         }
     }
 


    public function DeleteImage($id)
    {

        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id )->first();
        $Items=Item::where('id','=',$id)->where('owner_id','=',$owner->id)->first();
        
      $Image = Image::whereHasMorph('imageable', [Item::class],
      function ($query) use($owner) {
      $query->whereHas('owner', function ($query) use($owner) {
      $query->where('id','=',$owner->id); 
      });
    }
     
     )
     ->where('id','=',$id)
    
        ->first();
        if($Image)
        {
            $Image->delete();
            return response()->json([
                'success' => true,
                'Data' => null
            ], 200);


        }
        else
        {
            return response()->json([
                'success' => false,
                'Data' => null
            ], 400);

        }


        
        

        


    }

    public function destroyTimeOpen($id)
    {
      
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id )->first();
       
        $TimeToOpen=TimeToOpen::where('id','=',$id)
        ->whereHas('day_to_open', function ($query) use($owner)  {
   
            $query->whereHasMorph('day_to_open_able',[Item::class], function ($query) use($owner) 
            {
                $query->where('owner_id', '=',$owner->id);
            }
        );
        })
            
         ->first();
        if($TimeToOpen)
        {
            $TimeToOpen->delete();
            return response()->json([
                'success' => true,
                'Data' => null
            ], 200);
        }
        else
        {
            return response()->json([
                'success' => false,
                'Data' => null
            ], 400);

        }
        

        
    }

   

    public function destroy($id)
    {

        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id )->first();
        $Items=Item::where('id','=',$id)->where('owner_id','=',$owner->id)->first();

        $Items->status_id=25;
        $Items->save();
        
       // $Items->delete();

        return response()->json([
            'success' => true,
            'Data' => null
        ], 200);



    }


    public function return_delete($id)
    {
        $owner=Owner::where('user_id','=',JWTAuth::parseToken()->authenticate()->id )->first();
        $Items=Item::where('id','=',$id)->where('owner_id','=',$owner->id)->first();


       
        $Items->status_id=1;
        $Items->save();
        
       // $Items->delete();

        return response()->json([
            'success' => true,
            'Data' => null
        ], 200);



    }




}
