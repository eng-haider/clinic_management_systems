<?php

namespace App\Http\Controllers\API;
use Illuminate\Routing\Controller;
use App\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Item;
use JWTAuth;
use App\Reservation;
use App\Notification;
use Illuminate\Support\Facades\DB;
use App\Offer;
class NotificationController extends Controller
{

    protected $smsUrl = 'https://sms-gw.net:91/v2/api/Gateway/SendMessage';
    protected $smsToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IjE4NzI3NmRkLTQ3N2ItNDZiOC1iMDViLWI0YTAyYWRiMzZhOCIsImVtYWlsIjoiYWxpLnF1bWFyQGVuamF6LnRlY2giLCJnaXZlbl9uYW1lIjoiRU4tU01TIiwiaHR0cDovL3NjaGVtYXMueG1sc29hcC5vcmcvd3MvMjAwNS8wNS9pZGVudGl0eS9jbGFpbXMvbmFtZSI6IkVOLVNNUyIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IkN1c3RvbWVyIiwiaHR0cDovL3NjaGVtYXMueG1sc29hcC5vcmcvd3MvMjAwOS8wOS9pZGVudGl0eS9jbGFpbXMvYWN0b3IiOiIyIiwianRpIjoiZjEyZTc1NWYtNTI0ZC00MDEyLTg5ZWUtNTRmMjdlZjljNGI0IiwibmJmIjoxNjA0MTM3ODY5LCJleHAiOjE2MzU2NzM4NjksImlzcyI6Iklzc3VlciIsImF1ZCI6IkF1ZGllbmNlIn0.DSihSIsVl9Ux3_sl7R_cSCOsGjhumlEq1YYID-owvRo';
    protected $data = [];
    protected $code;
    protected $smsData = [];
 

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
    public function store($send_data)
    {

    
        $notification = new Notification;
        $notification->notification_type_id =$send_data['notification_type_id'];
        $notification->reservation_id=$send_data['res']->id;
        $notification->notification_title=$send_data['notification_title'];
        $notification->user_id =$send_data['user']->id;
        $notification->save();
       
       
        $this->SendSms($send_data);
       



     
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function show(Driver $driver)
    {
        //
    }


    public function GetUserNotifications(Request $request)
    {
        
       
        $user = JWTAuth::parseToken()->authenticate();

        $Notification = Notification::whereIn('notification_type_id',[1,3,6])
           -> whereHasMorph('notificationable', [Offer::class])
        ->orwhereHasMorph('notificationable', [Reservation::class])
  
        ->where('user_id','=',$user->id)
    
        ->where('status_id','=',26)
       
        ->with('notificationable.item.image')
        ->orderBy('id','DESC')
        ->paginate(15);
       
                  return response()->json([
                     'success' => true,
                     'data' => $Notification
                 ], 200);
    }





    public function GetOwnerRerserverationsNotifications(Request $request)
    {
        
       
                $user = JWTAuth::parseToken()->authenticate();

                $Notification = Notification::whereHasMorph('notificationable', [Reservation::class],
                function ($query) use($user) {
                    $query->whereHas('item.owner', function ($query) use($user) {
                            $query->where('id','=',$user->owner->id); 
                        });
                }
                
                )
                ->with('notification_type')
                ->whereIn('notification_type_id',[2])
                ->where('status_id','=',26)
                ->orderBy('id','DESC')
                ->paginate(15);
       
           
            

                  return response()->json([
                     'success' => true,
                     'data' => $Notification
                 ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function edit(Driver $driver)
    {
        //
    }

    public function AlertUserRservationApproaches()
    {
       
      date_default_timezone_set('Asia/Baghdad');

      //Get Current Time 
      date_default_timezone_set('Asia/Baghdad');
      $date = date('Y-m-d G:i', time());

  

     $Notification = Notification::whereIn('notification_type_id',[3,6])
//     -> whereHasMorph('notificationable', [Offer::class])
//     ->orwhereHasMorph('notificationable', [Reservation::class])
// ->whereHasMorph('notificationable', [Reservation::class])
     
   //->whereHasMorph('notificationable', 'Reservation::class')
       //->whereIn('notification_type_id',[3,6])
      ->where('sending_date','=',$date)
           ->where('status_id','=',27)
      ->get();
   
  
   
      
       foreach($Notification as $note)
       {
      

      $reserverations=Reservation::with(['user','item'])->where('id','=',$note->notificationable_id)->get();
    $Notification = Notification::find($note->id);
      $Notification->status_id=26;
      $Notification->save();
        
      foreach($reserverations as $res)
      {
        $send_data=['user'=>$res->user,'data'=>$res,'notification_type_id'=>$note->notification_type_id,'notification_title'=>$note->notification_title,'notification_body'=>$note->notification_body];
        $this->sendPhoneNotification($send_data);
      }
      

  
      
       
       }
     
     

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Driver $driver)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Driver $driver)
    {
        //
    }



    

    
    public function SendSms($note_id)
    {

        $Notification=Notification::find($note_id);
        $this->smsData['number']=$Notification->user->user_phone;
        $this->smsData['text']=$Notification->notification_body;
        $this->smsData['messageType'] = 3;
        $this->smsData['sentThrough'] = 1;
        $response = Http::withToken($this->smsToken)->post($this->smsUrl, $this->smsData);           
        return $response;

    }

    
    public function sendPhoneNotification($send_data)
    {





            $title = array("en" => $send_data['notification_title']);
            $content = array("en" => $send_data['notification_body']);
            $DevicesTokens=$send_data['user']->DevicesTokens;

            $json_data= array
                (
                    // $DevicesTokens[$i]->device_token
                );
            for($i=0;$i<count($DevicesTokens);$i++)
            {
               
                $json_data= array
                (
                    $DevicesTokens[$i]->device_token
                );
             }

             
        


                $fields = array(
                'app_id' => "5d3f9116-5a73-45f1-aed1-56ea255f74cf",
                'include_player_ids' => $json_data,
                'channel_for_external_user_ids' => 'push',
                'data' =>$send_data,
                'contents' => $content,
                "headings"=>$title,
            //  "big_picture"=>"https://ichef.bbci.co.uk/news/976/cpsprodpb/C7BD/production/_104033115_hi050184980.jpg",
           //   "included_segments" => ["All"]
                );
                $fields = json_encode($fields);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                        'Authorization: Basic ZWNlZmY3NmQtNjZhZS00NzEyLTg5ODUtOGQxZTNkZGM3NGRm'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                $response = curl_exec($ch);
                curl_close($ch);
                
                return $response;
    
    
                $response = sendMessage();
                $return["allresponses"] = $response;
                $return = json_encode( $return);
                
                print("\n\nJSON received:\n");
                print($return);
                print("\n");


       

        


     
       


    
    }

}
