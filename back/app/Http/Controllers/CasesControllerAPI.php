<?php

namespace App\Http\Controllers;

use App\Models\images;
use App\Models\Sessions;

use App\Models\Patients;

use App\Models\Cases;
use App\Models\Bills;
use App\Models\CaseCategories;
use App\Http\Resources\CasesResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\CasesRequest;
use App\Http\Resources\Collections\CasesCollection;
use Validator;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\UploadImageController;
//images
use Image;
use finfo;
//getCaseCategories
class CasesControllerAPI extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Collections\CasesCollection
     */

     public function getCaseCategories()
     {
        return CaseCategories::get();

     }


     public function getDashbourdCounts()
     {
        $Doctor_id=auth()->user()->Doctor->id;
        $Casesall = Cases::whereHas('Patient', function ($query) use($Doctor_id) {
            $query->where('doctor_id','=',$Doctor_id);
        })
        ->count();


        $Casesactive = Cases::whereHas('Patient', function ($query) use($Doctor_id) {
            $query->where('doctor_id','=',$Doctor_id);
        })
        ->where('status_id','=',42)
        ->count();

        $Casescompleted = Cases::whereHas('Patient', function ($query) use($Doctor_id) {
            $query->where('doctor_id','=',$Doctor_id);
        })
        ->where('status_id','=',43)
        ->count();



    
      $patients = patients::with(['Cases.Bills'
      
      ])->where('doctor_id','=',$Doctor_id)->orderBy('id', 'desc')->count();



      return response()->json([
        'message' => 'successfully',
        'patients'=>$patients,
        'Casescompleted' =>$Casescompleted,
        'Casesall' =>$Casesall,
        'Casesactive' =>$Casesactive,
    ], 200);

      
       
     }

     public function getCaseCategoriesCounts()
     {
        $Doctor_id=auth()->user()->Doctor->id;
        $Cases = Cases::with(['Patient','CaseCategories','Bills','Status','images','Sessions'])
        ->whereHas('Patient', function ($query) use($Doctor_id) {
            $query->where('doctor_id','=',$Doctor_id);
        })
        ->orderBy('id', 'desc')
        ->get();

      
      $Cases =CaseCategories::whereHas('Case.Patient', function ($query) use($Doctor_id) {
        $query->where('doctor_id','=',$Doctor_id);
    })
    -> withCount('Case')
      
      ->get();
        return new CasesCollection($Cases);
     }




     public function Search(request $request){

       

     
        $Doctor_id=auth()->user()->Doctor->id;
      
        $Cases = QueryBuilder::for(Cases::class)
      ->with(['Patient','CaseCategories','Bills','Status','images','Sessions'])
      ->whereHas('Patient', function ($query) use($Doctor_id) {
            $query->where('doctor_id','=',$Doctor_id);
        })
    
        ->allowedFilters(['status_id', 'case_categores_id'])
        ->get();

        return new CasesCollection($Cases);
    
     }
    public function index()
    {
        $this->authorize('viewAny', Cases::class);

        $cases = Cases::all();

        return new CasesCollection($cases);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CasesRequest  $request
     * @return \App\Http\Resources\CasesResource
     */
    public function store(Request $request)
    {
      
       
        $validator = Validator::make($request->all(), [
            'case_categores_id' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $Cases = Cases::create(array_merge(
                    $validator->validated(),
                    [  
                    'patient_id'=>$request->patient_id,
                    'case_categores_id'=>$request->case_categores_id,
                    'status_id'=>$request->status_id,
                    'price'=>$request->price,
                    'user_id'=>auth()->user()->id,
                    'notes'=>$request->notes,
                    'tooth_num'=>$request->tooth_num,
                  
                    'upper_right'=>$request->upper_right,
                    'upper_left'=>$request->upper_left,
                    'lower_right'=>$request->lower_right,
                    'lower_left'=>$request->lower_left,
                    
                    ]
                ));


         
                if(count($request->images)>0){

                foreach($request->images as $image){
                 
                     $Cases->images()->create(
                [
                'image_url' =>$image,
                // 'descrption'=>$request->images[0]['descrption']
                ]);


                }

            }
               

            if(count($request->bills)>0 && $request->bills[0]['price'] !==null){
                $Cases->Bills()->create(
                    [
                    'price' =>$request->bills[0]['price'] ,
                    'PaymentDate'=>$request->bills[0]['PaymentDate']
                    ]);
                }

                for($i=0;$i<count($request->sessions);$i++){
                    Sessions::create(
                        [
                        'note' =>$request->sessions[$i]['note'] ,
                       // 'date'=>$request->sessions[$i]['date'],
                        'case_id'=>$Cases->id,
                        ]);
                    }
                

                return new CasesResource(  $Cases );

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cases  $cases
     * @return \App\Http\Resources\CasesResource
     */
    public function show(Cases $cases)
    {
      //  $this->authorize('view', $cases);

       return  new CasesResource($cases);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\CasesRequest  $request
     * @param  \App\Models\Cases  $cases
     * @return \App\Http\Resources\CasesResource
     */
    public function update(CasesRequest $request, Cases $cases)
    {


        $validator = Validator::make($request->all(), [
            'case_categores_id' => 'required|numeric',
          
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $Cases = Cases::
        // where('user_id','=',auth()->user()->id)  ->
         where('id','=',$cases->id)->get();

        

        $Cases[0]->case_categores_id=$request->case_categores_id; 
        $Cases[0]->status_id=$request->status_id;
        $Cases[0]->price=$request->price;
        $Cases[0]->notes=$request->notes;
        $Cases[0]->tooth_num=$request->tooth_num;
        $Cases[0]->upper_right=$request->upper_right;
        $Cases[0]->upper_left=$request->upper_left;

        $Cases[0]->lower_right=$request->lower_right;
        $Cases[0]->lower_left=$request->lower_left;


        $Cases[0]->notes=$request->notes;
        $Cases[0]->save();
     





    

        
        for($i=0;$i<count($request->images);$i++){
            if (isset($request->images[$i]['id']))
            {
                $image=images::where('id','=',$request->images[$i]['id'])->first();
                $image->update(['descrption' =>$request->images[$i]['descrption']]);

            }else{
               // $Cases[0]->images()->Create(['price' =>$request->bills[$i]['price'],'PaymentDate'=>$request->bills[$i]['PaymentDate']]);
                   
            }
        
            }

        for($i=0;$i<count($request->bills);$i++){


       
            if (isset($request->bills[$i]['id']))
            {
                $bill=Bills::where('id','=',$request->bills[$i]['id'])->first();
                $bill->update(['price' =>$request->bills[$i]['price'],'PaymentDate'=>$request->bills[$i]['PaymentDate']]);

            }else if($request->bills[$i]['price'] !==0){
                $Cases[0]->Bills()->Create(['price' =>$request->bills[$i]['price'],'PaymentDate'=>$request->bills[$i]['PaymentDate']]);
                   
          
        
        }



            }
         

        return new CasesResource( $Cases[0] );

    }





    


    //patientCases

    //Usercases

    
    
    public function patientCases($patient_id)
    {
       $Doctor_id=auth()->user()->Doctor->id;
        $Cases = Cases::with(['Patient','CaseCategories','Bills','Status','images','Sessions'])
        ->whereHas('Patient', function ($query) use($Doctor_id) {
            $query->where('doctor_id','=',$Doctor_id);
        })
        ->where('patient_id','=',$patient_id)
        ->orderBy('id', 'desc')
        ->get();
        
        return new CasesCollection($Cases);

    }
    
    public function UserCases()
    {
     //  $user_id=auth()->user()->id;

       $Doctor_id=auth()->user()->Doctor->id;
        $Cases = Cases::with(['Patient','CaseCategories','Bills','Status','images','Sessions'])
          ->whereHas('Patient', function ($query) use($Doctor_id) {
            $query->where('doctor_id','=',$Doctor_id);
        })
        ->orderBy('id', 'desc')
        ->get();
        
        
        
 

        return new CasesCollection($Cases);

    }

  
    public function uploude_image(Request $request)
    {
        $file=$request->file('file');
        $allowedfileExtension=['jpg','png','jpeg','JPG','JPEG','.gif'];
        // $extension =$file->getClientOriginalExtension();

        $filesize=$_FILES['file']['size'];
        $temp        = explode(".", $_FILES["file"]["name"]);
        $extension   = strtolower(end($temp));
        //$img_name = $_FILES["file"]["name"];
        $img_name    ='img'.time().'.'.$extension.'';

        $check=in_array($extension,$allowedfileExtension);
        $finfo = new finfo(FILEINFO_MIME);

        if($check)
        {
            if (in_array($finfo->file($_FILES["file"]['tmp_name']) , array('image/jpeg; charset=binary' , 'image/png; charset=binary' , 'image/gif; charset=binary'))) {
                if($filesize>5120000) {
                    return response()->json('file bigger than 5MB', 400); 
                }
                Image::make($request->file('file'))->save('case_photo/'.$img_name.'');
               
                return response()->json([
                    'success' => true,
                    'data' =>  $img_name 
                ], 200);


            } else {
                return response()->json('file type not allowed', 400);
            }
            
        } else {
            return response()->json('file type not allowed', 400);
        }
    }
    
   
//

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cases  $cases
     * @return \App\Http\Resources\CasesResource
     */
    public function destroy(Cases $cases)
    {
        // $this->authorize('delete', $cases);

        $cases->delete();

        return new CasesResource($cases);

    }
}
