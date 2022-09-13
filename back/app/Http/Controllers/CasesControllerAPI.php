<?php

namespace App\Http\Controllers;

use App\Models\images;
use App\Models\Sessions;

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

     public function getCaseCategoriesCounts()
     {
        $Doctor_id=auth()->user()->Doctor->id;
        $Cases = Cases::with(['Patient','CaseCategories','Bills','Status','images','Sessions'])
        ->whereHas('Patient', function ($query) use($Doctor_id) {
            $query->where('doctor_id','=',$Doctor_id);
        })
        ->orderBy('id', 'desc')
        ->get();
      //  CaseCategories::get();
      
      $Cases =CaseCategories::withCount('Case')->get();
        return new CasesCollection($Cases);
     }




     public function Search(request $request){

       

     
        $Doctor_id=auth()->user()->Doctor->id;
      
        $Cases = QueryBuilder::for(Cases::class)
      ->with(['Patient','CaseCategories','Bills','Status','images'])
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
                    'upper_right'=>$request->upper_right,
                    'upper_left'=>$request->upper_left,
                    'lower_right'=>$request->lower_right,
                    'lower_left'=>$request->lower_left,
                    
                    ]
                ));


            if(count($request->images)>0 && $request->images[0]['img'] !==null){
                $upload = new UploadImageController;
                $path = $upload->uploadInner($request);
            
                $Cases->images()->create(
                [
                'image_url' =>$path[0],
                'descrption'=>$request->images[0]['descrption']
                ]);

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

        $Cases[0]->upper_right=$request->upper_right;
        $Cases[0]->upper_left=$request->upper_left;

        $Cases[0]->lower_right=$request->lower_right;
        $Cases[0]->lower_left=$request->lower_left;


        $Cases[0]->notes=$request->notes;
        $Cases[0]->save();
     





        for($i=0;$i<count($request->sessions);$i++){
            if (isset($request->sessions[$i]['id']))
            {
                $sessions=Sessions::where('id','=',$request->sessions[$i]['id'])->first();
                $sessions->update(['note' =>$request->sessions[$i]['note']]);

            }else{
                Sessions::create(
                    [
                    'note' =>$request->sessions[$i]['note'] ,
                   // 'date'=>$request->sessions[$i]['date'],
                    'case_id'=>$Cases[0]->id,
                    ]);
                   
            }
        
            }


        
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

            }else{
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
