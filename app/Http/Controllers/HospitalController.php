<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\HospitalCategory; 
use File;
use Auth;
use App\Models\Role;
use App\Models\Doctor_hospital; 
use App\Models\User;
use App\Models\State;
use App\Models\LabCities;
use App\Models\hospitalsReport;
use App\Models\City;
use App\Models\HospitalDoctor;
use App\Models\PatientReport;
use App\Models\Appointment;
use App\Models\LabsAvailability;
use Illuminate\Support\Facades\Hash;
use Datatables;
class HospitalController extends Controller
{
      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
  {
    $search = $request->search;
    $records = config('app.admin_page_limit');
    $data['title'] = 'Labs';
    $data['page_name'] = 'Labs List';
    if ($request->ajax()) {
        $data = Role::where('role', 'Lab')->first()->users();
        if(Auth::user()->roles->contains(1)){
            $data->orderBy('id','desc');
         }
        return Datatables::of($data)
            ->addIndexColumn()
           
            ->addColumn('lab_name', function($row){
                 return isset($row->name) ? $row->name : '';
            })
            ->addColumn('city_id', function($row){
                $html = '';
               if(isset($row->cities)){
                    foreach($row->cities as $item){
                        $html .= $item->city.','; 
                    }
               }
                 return isset($html) ? $html : '';
            })
            ->addColumn('action', function($row){
                 return  '
              

                <a class="btn btn-primary btn-sm" href="'.route('lab.edit',$row->id).'">
                    <i class="fa fa-eye" aria-hidden="true"></i>
                </a>
                        
                 <form action="'.route('lab.destroy',$row->id).'" method="POST" style="display: inline-block;">
                 <input type="hidden" name="_token" value="'.csrf_token().'">
                 <input type="hidden" name="_method" value="delete">
                     <a class=" text-white btn btn-danger  show_confirm btn-sm"  value="Delete">
                         <i class="fa fa-trash" aria-hidden="true"></i>
                     </a>';
            })
            ->rawColumns(['lab_name','action','city_id'])
            ->make(true);
    }

    return view('admin.hospitals.index', $data);
}

    
    
    public function hospitalUpdate(Request $request, $id)
    {
        $data = User::where('id', $id)->first();
        $data->is_approved = $request->is_approved;
        $data->save();
        
        return redirect()->back()->with('msg', 'Status Updated Successfully');
    }



    public function getcity(Request $request)
    {
        $companyId = $request->state_id; 
        $cite = City::where('state_id', $companyId)->get();
       //return  $cite ;
        $outputDataemployees = '';
        if ($cite->count() > 0) {
            foreach ($cite as $item) {
                $outputDataemployees .= "<option value='{$item->id}'>{$item->city}</option>";
            }
        }
        return response()->json(['status' => true, 'outputDataemployees' => $outputDataemployees,]);
    }

    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)    
    {
        $result['HospitalCategory'] = HospitalCategory::all();
        $result['title'] = "Lab";
        $result['page_name'] = "Create";
        $result['updates'] = User::where('id',$request->id)->first();
        $result['state'] = State::get();
        $result['city'] = City::all();
        // $result['city'] = LabCities::where('lab_id',Auth::id())->pluck('city')->toArray();
        // $result['allCity'] = City::where('state_id',Auth::user()->state_id)->get();
        $result['week_arr'] = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday']; 
       
        return view('admin.hospitals.create', $result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $validate = $request->validate([
        // Define your validation rules here
    ]);

    $path = 'uploads/hospital/';
    $documentFile = !empty($request->hospital_logo)? $this->uploadDocuments($request->hospital_logo, $path): $request->old_hospital_logo;

    $result = User::updateOrCreate(
        ['id' => $request->id],
        [
            'name' => $request->name,
            'email' => $request->email,
            'number' => $request->number,
            'city_id' => $request->city_id,
            'hospital_logo' => $documentFile,
            'address' => $request->address,
            'state_id' => $request->state_id,
            'home_collection' => $request->home_collection,
            'postal_code' => $request->postal_code,
            'gst' => $request->gst,
            'hospital_category' => $request->hospital_category,
            'hospital_description' => $request->hospital_description,
            // 'password' => Hash::make($request->password)
        ]
    );
    if(!empty($request->password)){
        $result->password = Hash::make($request->password);
        $result->save();
    }
    $result->roles()->sync([4]);

    if (!empty($request->city)) {
        LabCities::where('lab_id',$result->id)->delete();
        foreach ($request->city as $city) {
            LabCities::updateOrCreate([
                'lab_id' => $result->id,
                'city' => $city,
            ]);
        }
    }

    $data = array();
    for ($i=1; $i <= 7; $i++) { 
        $data[] = [
            'week_day' => $i, 
            'start_time' => !empty($request->start_time[$i]) ? $request->start_time[$i] : '09:00', 
            'end_time' => !empty($request->end_time[$i]) ? $request->end_time[$i] : '17:00',
            'status' => isset($request->weekday[$i]) ? $request->weekday[$i] : 0,
        ]
        + (!empty($result->id) ? ['lab_id' => $result->id] : ['lab_id' => $result->id])
        + (!empty($request->vendor_available_id) ? ['id' => $request->vendor_available_id[$i-1]] : []);
    }

    LabsAvailability::upsert($data, ['id','lab_id','week_day'],['start_time','end_time','status']);
            
    if ($result) {
        $message = $request->id ? 'Updated' : 'Created';
        if(Auth::user()->roles->contains(4)){
           return redirect()->back()->with('msg', "Lab is Successfully Update");
         }
        return redirect()->route('lab.index')->with('msg', "Lab is Successfully $message");
    } else {
        return redirect()->back()->with('error', 'Something went wrong. Please try again!');
    }
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {  
        
        $data['data'] = User::findOrFail($id);
        $data['appointments'] = Appointment::where('hospital_id',$id)->get();
        $data['doctor'] = HospitalDoctor::where('hospital_id',$id)->get();
        $data['title'] = "Lab";
        $data['page_name'] = "List";
        $get['abc'] = User::where('is_hospital',1)->get();
        $asd = HospitalDoctor::all();
        return view('admin.hospitals.show', $data,$get)->with('HospitalDoctor', $asd);;
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        if(Auth::user()->roles->contains(4)){
            if(Auth::user()->id != $id){
                return redirect()->to('dashboard');
            }
        }

        $data['title'] = "Edit Lab";
        $data["id"] = $id;
        $data['page_name'] = "Edit";
        $data['state'] = State::all();
        $data['HospitalCategory'] = HospitalCategory::all();
        $data['updates'] = User::findOrFail($id);
        $data['city'] = City::all();
        
        $data['week_arr'] = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday']; 
        if($data['updates'])
        {
            return view('admin.hospitals.create',$data);
        }
        else
        {
            return redirect()->back()->with('error', 'Data not found');   
        }
    }
     public function hospital(Request $request)
    {
        
       $data= City::where('state_id', $request->state_id)->get();
    
        $outputData = '';
       
         if(isset($data) && count($data)>1){    
            foreach($data as $key => $value){
                if(!empty($value)){
                    $valueID = $value->id;
                    $valueTitle = $value->city;
                    $outputData .= "<option value='$valueID'>".$valueTitle."</option>";
                }
            }
         }
         return response()->json(['status' => true, 'outputData' => $outputData]);
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
        $data = User::find($id);
        $data->delete();
        return redirect()->route('lab.index');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_pasword' => 'required|min:8',
            'new_password_confirmation' => 'required|same:new_pasword|min:8'
        ]);
        
        if(Auth::user()->roles->contains(4)){
            if(Auth::user()->id != $request->id){
                return redirect()->to('dashboard');
            }
        }
    
        $user = User::where('id', $request->id)->first();
    
        if (!Hash::check($request->old_password, $user->password)) {
            
            return redirect()->back()->with('msg', 'The old password is incorrect.');
        }
                                                                     
        $user->update([
            'password' => Hash::make($request->new_pasword)
        ]);

    
        return redirect()->back()->with('msg', 'Password changed successfully.');
    }
    
}