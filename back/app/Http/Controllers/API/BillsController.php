<?php

namespace App\Http\Controllers\API;
use App\Bills;
use Validator;
use JWTAuth;
use App\Price;
use App\ZainCashs;
use App\TasdidBills;
use App\Reservation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\BillsCollection;
use App\Http\Resources\BillsResource;
use App\Http\Services\ZainCash;
use App\Offer;
use App\Http\Controllers\API\TasdidBillsController ;
use App\Http\Services\ThirdPartyService;
use Illuminate\Support\Facades\App;
class BillsController extends Controller
{

    public function ModifyTablePaymenytMethod()
    {
        $prices=Price::all();
        for($i=0;$i<=count($prices);$i++)
        {
            if($prices[$i]->payment_when_receiving==0)
            {
                $pr=Price::find($prices[$i]->id);
                $pr->is_tasdid_payment=1;
                $pr->save();

            }
        }

    }
    public function index()
    {
        $all = Bills::all();
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }

    public function    decodeZainCashToken (Request $request,$token)
    {

        
        // $zc = new ZainCash([
        //    'msisdn' =>'9647835077893',
        //    'secret' =>'$2y$10$hBbAZo2GfSSvyqAyV2SaqOfYewgYpfR1O19gIh4SqyGWdmySZYPuS',
        //    'merchantid'=>'5ffacf6612b5777c6d44266f',
        //     'production_cred'=>false,
        //     'language'=>'ar', // 'en' or 'ar'
        //     'redirection_url'=>'https://tctate.com/api/zaincashpay'
        
        //   ]); 


         $zc = new ZainCash([
           'msisdn' =>'9647828920186',
           'secret' =>'$2y$10$BsdotV4vqhJEQGPrP4lq7.PBk0T/5T0nId.YVGqYTZYPVL2tMdCGq',
           'merchantid'=>'60d81f510792585290258648',
            'production_cred'=>true,
            'language'=>'ar', // 'en' or 'ar'
            'redirection_url'=>'https://tctate.com/api/zaincashpay'
        

          ]); 
       
       
//  return $zc->decode($token);
          $ZainCashs=ZainCashs::where('id','=',intval($zc->decode($token)['orderid']))->where('status_id','=',36)->first();
        if($zc->decode($token)['status']=='failed')
        {

      
        $ZainCashs->zain_id=$zc->decode($token);
        //['id'];

        if($zc->decode($token)['status']=='failed')
            {
                $ZainCashs->status_id=38;
                $ZainCashs->save();
        
                
             if($zc->decode($token)['msg']=='Not enough credit on balance')
            {

               
  
                return response()->json([
                'success' => false,
                'data' => 'لايوجد رصيد كافي في المحفظه !'
                ], 200);

            }
            }
             
        }
             else if($zc->decode($token)['status']=='success')
             {

                $ZainCashs->status_id=37;
                $ZainCashs->save();

                $bill=Bills::where('id','=',$ZainCashs->bill_id)->where('status_id','=',32)->first();
                $bill->status_id=31;
                $bill->payment_method_id=3;
                $bill->save();
                }

                $res = Reservation::find($bill->reservation_id);
                if($res->status_id !==6 )
                {
                    
                    $res->status_id=6;  
                    $res->save();
                
                    $TasdidBillsController =new TasdidBillsController();
                    $TasdidBillsController->SendNotification($res->id);
                    
                    
                }

                return response()->json([
                    'success' => true,
                    'data' => 'تمت العمليه بنجاح'
                    ], 200);
    


            //  return  $zc->decode($token);
         //  return  $zc->decode($token);

            }

   
    public function TasdidPay(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $reservation=Reservation::where('id','=',$request->res_id)->where('user_id','=',$user->id)->first();
       



         $bill= Bills::where('id','=',$reservation->bill->id)->where('status_id','=',32)
        ->where('status_id','!=',31)
        ->where('payment_method_id','=',null)
        ->first();
        if($bill)
        {

 
        $response = (new ThirdPartyService)->createTasdidBill($reservation->user->full_name,$request->phone,$bill->amount,$bill->id,$reservation->reservation_end_date);
            if($response )
            {
                return response()->json([
                    'success' => true,
                    'payId' =>$response->payId 
                ], 200);

            }
            else
            {
                return response()->json([
                    'success' => false,
                    'data' =>'error' 
                ], 400);


            }
           

            
      //    return  $response = (new ThirdPartyService)->createTasdidBill($reservation->user->full_name,$request->phone,$bill->amount,$bill->id,$reservation->reservation_end_date);

        }
        else
        {
            return response()->json([
                'success' => false,
                'data' =>'error' 
            ], 400);


        }

    }

    public function ZainCashPay(Request $request,$res_id)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $reservation=Reservation::where('id','=',$res_id)->where('user_id','=',$user->id)->first();
        $reservation->bill;
        $item=$reservation->item;

       $bill= Bills::where('id','=',$reservation->bill->id)->where('status_id','=',32)
        ->where('status_id','!=',31)
        ->where('payment_method_id','=',null)
        ->first();
        

            $ZainCashs=new ZainCashs;
            $ZainCashs->bill_id=$bill->id;
            $ZainCashs->status_id=36;
            $ZainCashs->amount=$bill->amount;
            $ZainCashs->save();



            

            // try {
            // $zc = new ZainCash([
            //      'msisdn' =>'9647835077893',
            //     'secret' =>'$2y$10$hBbAZo2GfSSvyqAyV2SaqOfYewgYpfR1O19gIh4SqyGWdmySZYPuS',
            //     'merchantid'=>'5ffacf6612b5777c6d44266f',
            //     'production_cred'=>false,
            //     'language'=>'ar', // 'en' or 'ar'
            //     'redirection_url'=>'https://tctate.com/api/zaincashpay'

            // ]);


            try {
            $zc = new ZainCash([
                 'msisdn' =>'9647828920186',
                'secret' =>'$2y$10$BsdotV4vqhJEQGPrP4lq7.PBk0T/5T0nId.YVGqYTZYPVL2tMdCGq',
                'merchantid'=>'60d81f510792585290258648',
                'production_cred'=>true,
                'language'=>'ar', // 'en' or 'ar'
                'redirection_url'=>'https://tctate.com/api/zaincashpay'

            ]);



            
            $zain=$zc->charge(
                $bill->amount,
                    'Product purchase or something',
                    $ZainCashs->id
                );
                

                    return response()->json([
                        'success' => true,
                        'url' =>$zain 
                    ], 200);



            } catch (Exception $e) {
                echo $e->getMessage();
            }

       

}
    
    // public function getUserBills($status)
    // {
    //     $user = JWTAuth::parseToken()->authenticate();
    //     $all = Bills::with('item','tasdid_bills')
    //    ->where('payId','!=' ,null)
    //   //  ->Join('tasdid_bills', 'tasdid_bills.PayId', '=', 'bills.payId') 
    //     // ->whereHas('status', function($query) use($status){ 
    //     //  $query->where('id','=',$status);  
    //     // })
    //     ->where('user_id','=',$user->id)->orderBy('id','DESC')->paginate(15);
    //     return response()->json([
    //         'success' => true,
    //         'data' => $all
    //     ], 200);
    // }



    public function getUserBills($status)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $all = Bills::with('item','tasdid_bills') ->where('payment_method_id','!=' ,1)
      

        ->where('user_id','=',$user->id)->orderBy('id','DESC')->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }

    public function gernerateZainCashUrl()
    {
        
    }

    public function show($id)
    {
        $id=(int)$id;
        $user = JWTAuth::parseToken()->authenticate();
        $all = Bills::with('item','tasdid_bills')
       ->where('payment_method_id','=' ,2)
       ->where('id','=',$id)
      //  ->Join('tasdid_bills', 'tasdid_bills.PayId', '=', 'bills.payId') 
        ->whereHas('status', function($query) use($status){ 
         $query->where('id','=',$status);  
        })
        ->where('user_id','=',$user->id)->first();
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }

    
    public function GetOwnerTasdidBill(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $all = Bills::where('payId','!=' ,null)
        ->whereHas('item.owner', function($query) use($user){
         $query->where('id','=',$user->owner->id);
        })
        ->orderBy('id', 'desc')
        ->paginate(15);

       return new BillsCollection($all );
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }



    public function search(Request $request)
    {

        $Bills = QueryBuilder::for(Bills::class)
                ->allowedFilters(['item.owner_id','status_id'])
                ->orderBy('id', 'desc')
                ->paginate(10);

                return new BillsCollection($Bills);
    }


    


    public function getAllOwnerBills()
    {
        
        $user = JWTAuth::parseToken()->authenticate();
        $all = Bills::with(['item'
        =>function($query) use($user){
            return  $query->where('owner_id',$user->owner->id)->get();
         }
        ])
        
       ->get();
        return response()->json([
            'success' => true,
            'data' => $all
        ], 200);
    }

    public function store(Request $request)
    {


            $validator = Validator::make($request->all(), [
                'reservation_id' => 'required|numeric',
                'payment_method_id' => 'required',
            
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'Data' =>  $validator->messages()
                    ], 400);

                    }


                  //Payment By Zain Cash
                  if($request->payment_method_id==3)
                  {

                  }

         $Bills = new Bills;
         $Bills->item_id = $request->item_id;
         $Bills->Bills_number = $request->Bills_number;
         $Bills->status = $request->status;
        
         $Bills->save();

         return response()->json([
             'success' => true,
             'data' => null
         ], 200);
    }


    public function update(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'item_id' => 'required|numeric',
            'Bills_number' => 'required',
            'status' => 'required',


            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

    }


    public function destroy(Request $request)
    {


    }
}
