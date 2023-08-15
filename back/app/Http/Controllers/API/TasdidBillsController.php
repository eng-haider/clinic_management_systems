<?php

namespace App\Http\Controllers\API;
use App\Bills;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use App\TasdidBillsReceived;

use App\TasdidBills;
use App\Reservation;
use App\OwnerSubscriptions;
use App\Http\Controllers\API\NotificationController;
use App\User;
use App\Item;
Use Exception;

class TasdidBillsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      //  echo $time_in_12_hour_format  = date("g:i a", strtotime("13:30"));;

       
        $validator = Validator::make($request->all(), [
            'PayId' =>'required',
            'PhoneNumber'=>'required',
            'CustomerName'=>'required',
        //    'ServiceId'=>'required',
            'CreateDate'=>'required',
            'DueDate' =>'required',
            'PayDate'=>'required',
            'Status'=>'required|numeric',
            'Amount'=>'required',
            'Key'=>'required'
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'Data' =>  $validator->messages()
                ], 400);

                }

                $x=true;

    try {
        
       
        DB::beginTransaction();
        $TasdidBills = new TasdidBillsReceived;
        $TasdidBills->PayId = $request->PayId;
        $TasdidBills->PhoneNumber =$request->PhoneNumber;
        $TasdidBills->CustomerName =$request->CustomerName;
        $TasdidBills->ServiceId = $request->ServiceId;
        $TasdidBills->CreateDate = $request->CreateDate;
        $TasdidBills->DueDate =$request->DueDate;
        $TasdidBills->PayDate =$request->PayDate;
        $TasdidBills->Amount = $request->Amount;
        $TasdidBills->PayDate =$request->PayDate;
        $TasdidBills->Status = $request->Status;
        $TasdidBills->Key =$request->Key;
        
        
        if($TasdidBills->save() && $TasdidBills->Status==3)
        {
          //  $bills= Bills::where('payId','=',$request->PayId)->first() ;
           

            $TasdidBills=TasdidBills::where('PayId','=',$request->PayId)->first();
            $TasdidBills->status_id=39;
            $TasdidBills->save();
            
            $bills= Bills::where('id','=', $TasdidBills->bill_id)->first();
            $bills->status_id=31;
            $bills->payment_method_id=2;
            $bills->save();
            
            

            if($bills->reservation_id)
            {
                $res = Reservation::find($bills->reservation_id);
                if($res->status_id !==6 )
                {
                    
                    $res->status_id=6;  
                    $res->save();

                    $this->SendNotification($res->id);
                    
                    
                }
              
            }
            else 
            {
                $OwnerSubscriptions=OwnerSubscriptions::where('bill_id','=',$bills->id)->first();
                $OwnerSubscriptions->status_id=29;
                $OwnerSubscriptions->save();

            }
            

        }
        
     
     

  


    }catch (\Exception $e) {
        $x=false;
        dd($e->getMessage());
      
    }
    
    DB::commit();
    if($x)
    {
        return response()->json([
            'success' => true,
            'data' => null
        ], 200);
    }
    else
    {
        
        return response()->json([
            'success' => false,
            'data' => null,
        ], 400);
    }
        }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function SendNotification($res_id)
    {

        $res = Reservation::find($res_id);

    
        $notification=new NotificationController();
        if($res->image && count($res->image)>0) {
        $img_url=$res->image[0]->image_url;
        $img_send='اضغط هنا لتحميل الصوره المرسله من الشركه '.'http://127.0.0.1:8000/images/'.$img_url;      
        } else{
        $img_send='';}
       
       
        
        $time = str_replace(['AM','PM'],['صباحاً',' مسائاً'],date('h:i A', strtotime($res->reservation_from_time)));
  
        // if($request->status_id==6)
        // {

           $notification_title='   تم قبول حجزك  في  '.$res->item->item_name.' موعد حجزك هو '.$res->reservation_start_date.' الساعه '.$time .''.$img_send  ;
           $notifications= $res->Notification()->create(
               [
                   'user_id' =>$res->user->id,
                   'notification_type_id'=>3,
                   'status_id'=>27,
                   'sending_date'=>$res->reservation_start_date.' '.date('H:i',strtotime($res->reservation_from_time)-60*60),
                   'notification_title'=>$res->item->item_name,
                   'notification_body'=>'   متبقي ساعه لموعد حجزك في  '.$res->item->item_name.' موعد حجزك هو '.$res->reservation_start_date.' الساعه '.$time .''.$img_send          
               ]);

               $send_data=['user'=>$res->user,'notification_type_id'=>1,'notification_title'=>$res->item->item_name,'notification_body'=>'  تم قبول حجزك موعد حجزك هو '.$res->reservation_start_date.' الساعه '.$time .''.$img_send  ];
               $notification->sendPhoneNotification($send_data);
               

        // }
        // else if($request->status_id==9)
        // {
        //    $notification_title='تم رفض حجز في'.$res->item->item_name;
        //    $send_data=['user'=>$res->user,'notification_type_id'=>1,'notification_title'=>$res->item->item_name,'notification_body'=>'  تم رفض حجزك  حجز  '.$img_send  ];
        //    $notification->sendPhoneNotification($send_data);
            
        // }

        
        $notifications= $res->Notification()->create(
           [
               'user_id' =>$res->user->id,
               'notification_type_id'=>1,
               'status_id'=>26,
               'notification_title'=>$res->item->item_name,
               'notification_body'=>$notification_title           
            
           ]);
           if($notifications)
           {
            $notification->SendSms($notifications->id);
          
           }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
