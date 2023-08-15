<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use App\Bills;
use App\TasdidBills;
class ThirdPartyService
{
    protected $smsUrl = 'https://sms-gw.net:91/v2/api/Gateway/SendMessage';
    protected $smsToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IjE4NzI3NmRkLTQ3N2ItNDZiOC1iMDViLWI0YTAyYWRiMzZhOCIsImVtYWlsIjoiYWxpLnF1bWFyQGVuamF6LnRlY2giLCJnaXZlbl9uYW1lIjoiRU4tU01TIiwiaHR0cDovL3NjaGVtYXMueG1sc29hcC5vcmcvd3MvMjAwNS8wNS9pZGVudGl0eS9jbGFpbXMvbmFtZSI6IkVOLVNNUyIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcm9sZSI6IkN1c3RvbWVyIiwiaHR0cDovL3NjaGVtYXMueG1sc29hcC5vcmcvd3MvMjAwOS8wOS9pZGVudGl0eS9jbGFpbXMvYWN0b3IiOiIyIiwianRpIjoiZjEyZTc1NWYtNTI0ZC00MDEyLTg5ZWUtNTRmMjdlZjljNGI0IiwibmJmIjoxNjA0MTM3ODY5LCJleHAiOjE2MzU2NzM4NjksImlzcyI6Iklzc3VlciIsImF1ZCI6IkF1ZGllbmNlIn0.DSihSIsVl9Ux3_sl7R_cSCOsGjhumlEq1YYID-owvRo';
    protected $smsData = [];
    private $tasdidAuthUrl = 'https://api-uat.tasdid.net/v1/api/Auth/Token';
    private $tasdidAuthUrlAdd='https://uat.tasdid.net:85/v1/api/Provider/AddBill';
    private $tasdidAuthFields = ['username' => 'ali.qumar@enjaz.tech', 'password' => '12345678'];
 

    private $tasdidBillData = [
        'payId' => '', 'dueDate' => '',
        'payDate' => '', 'status' =>2, 'note' => '',
        'clientId' =>120, 'serviceId' => '4b09d251-5548-421b-a781-656f4c4f89d0',
    ];

    private function getTasdidToken()
    {
      
         $request = json_encode($this->tasdidAuthFields);
        $response = Http::post($this->tasdidAuthUrl,$this->tasdidAuthFields);
        if ($token['token'] = json_decode($response, true)) {
            return $token['token'];
        }
        return false;

    }

    public function createTasdidBill($name,$phone,$amount,$bill_id,$dueDate)
    {
  
        $token = $this->getTasdidToken();
        $this->tasdidBillData['amount'] =$amount;
        $this->tasdidBillData['customerName'] =$name;
        $this->tasdidBillData['phoneNumber'] =$phone;
        $this->tasdidBillData['dueDate'] =$dueDate;
        $this->tasdidBillData['payDate'] =$dueDate;
     


        $token = $this->getTasdidToken();


        if ($token = $this->getTasdidToken()) {

           $response = Http::withToken($token['token'])
            ->put($this->tasdidAuthUrlAdd,$this->tasdidBillData);
            $obj = json_decode($response);
        //     echo $obj->message=='Bill Created Successfully';
        //  return   $obj = json_decode($response);
           if( $obj->message=='Bill Created Successfully')
            {
              
             
               $x= $obj->{'data'}; 
             
        
                $TasdidBills = new TasdidBills;
                $TasdidBills->PayId =  $x->payId;
                $TasdidBills->PhoneNumber = $x->phoneNumber;
                $TasdidBills->CustomerName = $x->customerName;
                $TasdidBills->ServiceId =  $x->serviceId;
                $TasdidBills->CreateDate =  $x->createDate;
                $TasdidBills->DueDate = $x->dueDate;
                $TasdidBills->PayDate = $x->payDate;
                $TasdidBills->Amount =  $x->amount;
                $TasdidBills->PayDate = $x->payDate;
                $TasdidBills->Status =  $x->status;
                //$TasdidBills->Key = $x->Key;
                $TasdidBills->bill_id=$bill_id;
                $TasdidBills->status_id=40;
                $TasdidBills->save();

                return   $x ; 
            }
            else
            {
                return false;
            }
         $obj = json_decode($response);
         
   
          $x= $obj->{'data'}; 
         
            return $x;
        }
        return false;
    }

    public function sms($phone,$content)
    {
       
        $this->smsData['number'] = $phone;
        $this->smsData['text'] = $content;
        $this->smsData['messageType'] = 3;
        $this->smsData['sentThrough'] = 1;
         $response = Http::withToken($this->smsToken)
             ->post($this->smsUrl, $this->smsData);
         return $response;
    }

}
