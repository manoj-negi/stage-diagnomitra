<?php
namespace App\Http\Controllers;

use App\Models\LabProfile;
use App\Models\LabProfileTests;
use App\Models\Role;
use App\Models\User;
use App\Models\LabTest;
use App\Models\LabSelectedPackages;
use App\Models\Package;
use Datatables; 
use Illuminate\Http\Request;
use Validate;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index(Request $request)
    // {   
    //     $data['title'] = 'Lab Profile';
    //     $data['page_name'] = "List";
    //     $data['labs'] = User::whereHas('roles', function($q){$q->where('id','=', 4);})->get();
    //     $dataArray = LabProfile::query();

    //     if (Auth::user()->roles->contains(4)) {
    //         $dataArray->where('lab_id', Auth::user()->id);
    //     } elseif (Auth::user()->roles->contains(1)) {
    //         $dataArray->where('lab_id', '!=', Auth::user()->id);
    //     }

    //     if (!empty($request->search)) {
    //         $dataArray->where('profile_name', 'LIKE', '%' . $request->search . '%');
    //     }

    //     if (!empty($request->lab)) {
    //         $dataArray->where('lab_id', $request->lab);
    //     }

    //     $data['data'] = $dataArray->orderBy('id', 'desc')->paginate($request->pagination ?? 20)->withQueryString();

    //     return view("admin.labprofile.index", $data);
    // }
    
    public function index(Request $request)
    {
        $data['title'] = 'Lab Profile';
        $data['page_name'] = "List";
    
        
        $dataArray = LabProfile::query()
            ->join('labs', 'lab_profile.lab_id', '=', 'labs.id')
            ->select('lab_profile.*', 'labs.name as lab_name');
    
     
        if (Auth::user()->roles->contains(4)) {
            $dataArray->where('lab_profile.lab_id', Auth::user()->id);
        } elseif (Auth::user()->roles->contains(1)) {
           
        }
    
        
        if (!empty($request->search)) {
            $dataArray->where('lab_profile.profile_name', 'LIKE', '%' . $request->search . '%');
        }
    
     
        if (!empty($request->lab)) {
            $dataArray->where('lab_profile.lab_id', $request->lab);
        }
    
        // Debugging: Print the query and results to ensure all data is being retrieved
        // Uncomment the following lines to debug
        // dd($dataArray->toSql()); // Shows the raw SQL query
        // dd($dataArray->get()); // Shows the actual data being retrieved
    
       
        $data['data'] = $dataArray->orderBy('lab_profile.id', 'desc')
            ->paginate($request->input('pagination', 20))
            ->withQueryString();
    
        return view("admin.labprofile.index", $data);
    }


    public function indexDiagno(Request $request)
    {   
        $data['title'] = 'Lab Profile';
        $data['page_name'] = "List";
        $dataArray = Package::where('type','profile')->where('lab_id',1);
        if(!empty($request->search)){
            $dataArray->where('package_name', 'LIKE', '%' . $request->search . '%');
       }
        $data['data'] = $dataArray->orderBy('id','desc')->paginate($request->pagination ?? 20)->withQueryString();
        return view("admin.labprofile.index", $data);
    }
    public function test(Request $request)
    {
        $LabId = $request->lab_id; 
        $keyWord = $request->q; 
        $lab  = Package::where('type','test')->where('package_name','LIKE',"%$keyWord%");
        if(Auth::user()->roles->contains(4)){
            $lab->where('lab_id',Auth::user()->id);
        }
        $page = $request->input('page', 1);
        $items  = $lab->paginate(10, ['*'], 'page', $page);

        $response = [
            'items' => $items->items(),
            'total_count' => $items->total()
        ];
    
        return response()->json($response);
    }

    // public function getTestsByParent(Request $request)
    // {
    //     $parentId = $request->input('parent_id');
    //     // Retrieve the parent_id from the request
    //     // $LabId = $request->lab_id; 
    //     // $keyWord = $request->q; 
    //     $lab = Package::where('id',$parentId)->first();
        
    //     return response()->json($lab->getChilds ?? []);
    // }
    
    
    public function getTestsByParent(Request $request)
{
    $parentPackage = Package::where('type', 'test')
    ->where('lab_id', Auth::user()->id)
    ->with('getChilds')
    ->find($parentId);

$childPackages = $parentPackage ? $parentPackage->getChilds : [];
$additionalTests = Package::where('type', 'test')
      ->where('lab_id', Auth::user()->id)
      ->where('parent_id', '!=', $parentId)
      ->get();
$allTests = $childPackages->merge($additionalTests);

if ($allTests->isNotEmpty()) {
    return response()->json($allTests);
} else {
    return response()->json([]);
}
}

// public function getTestsByParent(Request $request)
// {
//     $parentId = $request->input('parent_id');

//     // Retrieve the package with the specified parent_id
//     $parentPackage = Package::with('getChilds')->find($parentId);

//     // Check if the package and its children exist
//     if ($parentPackage) {
//         // Return the child packages as a JSON response
//         return response()->json($parentPackage->getChilds);
//     } else {
//         // Return an empty array if no package or children are found
//         return response()->json([]);
//     }
// }


    public function create(Request $request)
    {
        $data['page_name'] = "Create";
        $data['title'] = 'Add Lab Profile';
        $data['selectedTests'] = LabProfileTests::where('profile_id', $request->id)->pluck('test_id')->toArray();
        $data['hospitals'] = User::whereHas('roles', function ($query) {$query->where('id', 4);  })->get();
        $data['tests']  = Package::where('type','test')->where('lab_id',Auth::user()->id);
        $data['tests'] = $data['tests']->take(10)->get();
        // = Package::where('parent_id',null)->get();
        $data['subprofile'] = Package::where('type','profile')->where('lab_id',1)->get();
        
        return view('admin.labprofile.create',$data);
    }
    public function deleted($id, $test_id)
    {
        LabProfileTests::where("profile_id", $id)
            ->where("test_id", $test_id)
            ->delete();

        return redirect()->back();
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {    
        $labID = $request->lab_id ?? 1;
        if (Auth::user()->roles->contains(4)) {
            $labID = Auth::user()->id;
        }
        
        $validationRules = [
            'title' => 'required',
        ];
    
        $CheckUser = Package::where('parent_id',$request->parent_id)->where('lab_id',Auth::id())->first();

        $CheckEdit = Package::where('id',$request->id)->first();

        if(is_null($CheckEdit)){
        if (empty($request->parent_id) || $CheckUser) {
            $validationRules['title'] = 'required|unique:labs_packages,package_name';
        }
    }
        $validatedData = $request->validate($validationRules);

        if(is_null($CheckEdit)){
        if (!is_null($request->parent_id)) {
            $profileCount = Package::where('parent_id', $request->parent_id)
                                    ->where('lab_id', $labID)
                                    ->where('type', 'profile')
                                    ->count();
            if ($profileCount >= 3) {
                return redirect()->back()->with(['msg' => 'You can only create 3 Sub profiles with this Profile.']);
            }
        }
    }
        // return $profileCount;
        
        // Create or update the package
        $data = Package::updateOrCreate([
            'id' => $request->id,
        ], [
            'package_name' => $request->title,
            'parent_id' => $request->parent_id,
            'amount' => $request->amount,
            'lab_id' => $labID,
            'type' => 'profile',
        ]);
      
        if (!empty($request->test_id)) {
            $data->getChilds()->sync($request->test_id);
        }
    
        if (isset($request->image)) {
            $path = public_path("/uploads/package/");
            $uploadImg = $this->uploadDocuments($request->image, $path);
            $data->image = $uploadImg;
            $data->save();
        }
        if($labID != 1){
            LabSelectedPackages::updateOrcreate(
                [ 'package_id' => $data->id]
                ,[
                'lab_id' => $labID,
                'amount' => $request->amount ?? '',
                'is_selected' => true,
            ]);
        }
        $msg=isset($request->id) && !empty($request->id)?'Updated Successfully.':'Created Successsfully';
        return redirect()->to($request->url)->with('data_created',$msg);
                       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        $data['page_name'] = "Show";
        $data['title'] = ' Profile Details';
        $data['data'] = Package::where('id', $id)->first();

        return view('admin.labprofile.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    { 
        $categories['title'] = 'Edit LabProfile';
        $categories['page_name'] = "Edit";
        $categories['subprofile'] = Package::where('type','profile')->where('lab_id',1)->get();
        $categories['data'] = Package::find($id);
        
        $categories['hospitals'] = User::whereHas('roles', function ($query) {
            $query->where('id', 4);
        })->get(); 
        
        $categories['tests'] = Package::where('type','test');
        if(Auth::user()->roles->contains(4)){
            $categories['tests']->where('lab_id',Auth::user()->id);
        }
        
        if(isset($categories['data']->getChilds)){
            $categories['tests']->whereIn('id',$categories['data']->getChilds->pluck('id')->toArray());
        }
        $categories['tests'] = $categories['tests']->get();
        return view('admin.labprofile.create', $categories);
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
    public function destoryProfile($id)
    {
        Package::find($id)->delete();
        return redirect()->back()->with('msg', 'Profile Deleted Successfully');
    }
}
