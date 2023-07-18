<?php

namespace App\Http\Controllers;

use App\Models\images;
use App\Models\Sessions;

use App\Models\CaseDoctor;


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
use App\Models\doctors;
//images
use Intervention\Image\ImageManagerStatic as Image;
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
      ->with(['Patient','CaseCategories','Bills','Status','images','Sessions','Doctors'])
      ->whereHas('Doctors', function ($query) use($Doctor_id) {
        $query->where('doctors.id','=',$Doctor_id);
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

     public function testin(){




        foreach(Cases::with('Patient')->get() as $case){

            CaseDoctor::create(

                [
                'doctors_id'=>$case->Patient->doctor_id,
                'cases_id'=>$case->id,
                ]
            );
        }

     }

     public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'case_categores_id' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }


        //Doctor
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

               //add caseDoctors


               if(!isset($request->doctors)){
    $doctor=array(auth()->user()->Doctor->id);
    $Cases->Doctors()->sync($doctor);
               }

// else if(count($request->doctors)==0){
//     $doctor=array(auth()->user()->Doctor->id);
//     $Cases[0]->Doctors()->sync($doctor);
// }

else{

    $doctors=$request->doctors;
    if(auth()->user()->role_id==2 && !in_array(auth()->user()->Doctor->id, $request->doctors)){

        array_push($doctors,auth()->user()->Doctor->id);
    }

    $Cases->Doctors()->sync($doctors);
}




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

     public function DeleteCaseDoctor(Request $request){
        try {

            $Doctor_id=auth()->user()->Doctor->id;
            CaseDoctor::where('doctors_id','=',$Doctor_id)->destroy($request->ids);
            return response()->json([
                'message'=>"Posts Deleted successfully."
            ],200);

        } catch(\Exception $e) {
            report($e);
        }
     }
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




//add caseDoctors
// return $request->doctors[0];
if(!is_array($request->doctors) && is_object($request->doctors[0])){

}

else if(count($request->doctors)==0){
    $doctor=array(auth()->user()->Doctor->id);


    $Cases[0]->Doctors()->sync($doctor);


}else{
    $Cases[0]->Doctors()->sync($request->doctors);
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
        $Cases = Cases::with(['Patient','CaseCategories','Bills','Status','images','Sessions','Doctors'])
        // ->whereHas('Patient', function ($query) use($Doctor_id) {
        //     $query->where('doctor_id','=',$Doctor_id);
        // })

        ->whereHas('Doctors', function ($query) use($Doctor_id) {
            $query->where('doctors.id','=',$Doctor_id);
        })


        ->where('patient_id','=',$patient_id)
        ->orderBy('id', 'desc')
        ->get();

        return new CasesCollection($Cases);

    }


    //getByDoctor

    public function getByDoctor($Doctors)
    {
     //  $user_id=auth()->user()->id;

       $Doctor_id=(int)$Doctors;
        $Cases = Cases::with(['Patient','CaseCategories','Bills','Status','images','Sessions','Doctors'])
          ->whereHas('Doctors', function ($query) use($Doctor_id) {
            $query->where('doctors.id','=',$Doctor_id);
        })
        ->orderBy('id', 'desc')
        ->get();





        return new CasesCollection($Cases);

    }

    public function UserCases()
    {
     //  $user_id=auth()->user()->id;

       $Doctor_id=auth()->user()->doctor->id;
        $Cases = Cases::with(['Patient','CaseCategories','Bills','Status','images','Sessions','Doctors'])
          ->whereHas('Doctors', function ($query) use($Doctor_id) {
            $query->where('doctors.id','=',$Doctor_id);
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
               // Image::make($request->file('file'))->save('case_photo/'.$img_name.'');
               $this->smart_resize_image($request->file('file'), 850, 600, false,
               'file', 'file', 'case_photo/'.$img_name.'', false, false, 100, false);
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

     public function smart_resize_image($file,

     $string = null,

     $width = 0,

     $height = 0,

     $proportional = false,

     $output = 'file',

     $targetfile = NULL,

     $delete_original = true,

     $use_linux_commands = false,

     $quality = 100,

     $grayscale = false

)

{


if ($height <= 0 && $width <= 0) return false;

if ($file === null && $string === null) return false;


# Setting defaults and meta

$info = $file !== null ? getimagesize($file) : getimagesizefromstring($string);

$image = '';

$final_width = 0;

$final_height = 0;

list($width_old, $height_old) = $info;

$cropHeight = $cropWidth = 0;


# Calculating proportionality

if ($proportional) {

if ($width == 0) $factor = $height / $height_old;

elseif ($height == 0) $factor = $width / $width_old;

else                    $factor = min($width / $width_old, $height / $height_old);


$final_width = round($width_old * $factor);

$final_height = round($height_old * $factor);

} else {

$final_width = ($width <= 0) ? $width_old : $width;

$final_height = ($height <= 0) ? $height_old : $height;

$widthX = $width_old / $width;

$heightX = $height_old / $height;


$x = min($widthX, $heightX);

$cropWidth = ($width_old - $width * $x) / 2;

$cropHeight = ($height_old - $height * $x) / 2;

}


# Loading image to memory according to type

switch ($info[2]) {

case IMAGETYPE_JPEG:

$file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);

break;

case IMAGETYPE_GIF:

$file !== null ? $image = imagecreatefromgif($file) : $image = imagecreatefromstring($string);

break;

case IMAGETYPE_PNG:

$file !== null ? $image = imagecreatefrompng($file) : $image = imagecreatefromstring($string);

break;

default:

return false;

}


# Making the image grayscale, if needed

if ($grayscale) {

imagefilter($image, IMG_FILTER_GRAYSCALE);

}


# This is the resizing/resampling/transparency-preserving magic

$image_resized = imagecreatetruecolor($final_width, $final_height);

if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) {

$transparency = imagecolortransparent($image);

$palletsize = imagecolorstotal($image);


if ($transparency >= 0 && $transparency < $palletsize) {

$transparent_color = imagecolorsforindex($image, $transparency);

$transparency = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);

imagefill($image_resized, 0, 0, $transparency);

imagecolortransparent($image_resized, $transparency);

} elseif ($info[2] == IMAGETYPE_PNG) {

imagealphablending($image_resized, false);

$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);

imagefill($image_resized, 0, 0, $color);

imagesavealpha($image_resized, true);

}

}

imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);


# Taking care of original, if needed

if ($delete_original) {

if ($use_linux_commands) exec('rm ' . $file);

else @unlink($file);

}


# Preparing a method of providing result

switch (strtolower($output)) {

case 'browser':

$mime = image_type_to_mime_type($info[2]);

header("Content-type: $mime");

header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 60 * 60)));

$output = NULL;

break;

case 'file':

$output = $targetfile;

break;

case 'return':

return $image_resized;

break;

default:

break;

}


// if overlay is needed

/*$overlayImage = imagecreatefrompng($this->path . DS . 'static' . DS . 'images' . DS . 'site' . DS . 'imageoverlay.png');



$this->imagecopymerge_alpha($image_resized, $overlayImage, 1, 1, 0, 0, 100, 100, 75);*/


# Writing image according to type to the output destination and image quality

switch ($info[2]) {

case IMAGETYPE_GIF:

imagegif($image_resized, $output);

break;

case IMAGETYPE_JPEG:

imagejpeg($image_resized, $output, $quality);

break;

case IMAGETYPE_PNG:

$quality = 9 - (int)((0.9 * $quality) / 10.0);

imagepng($image_resized, $output, $quality);

break;

default:

return false;

}


return true;

}
    public function destroy(Cases $cases)
    {

        $Doctor_id=auth()->user()->Doctor->id;
        $Cases = Cases::where('id','=',$cases->id)->whereHas('Doctors', function ($query) use($Doctor_id) {
            $query->where('doctors.id','=',$Doctor_id);
        })
        ->orderBy('id', 'desc')
        ->get();

if(count($Cases)>0){
       $Sessions=Sessions::where('case_id','=',$cases->id)->get();
        foreach ($Sessions as &$Session) {

            $Session->delete();
}

        $Cases[0]->delete();
}




        return new CasesResource($cases);

    }
}
