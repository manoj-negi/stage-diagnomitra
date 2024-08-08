<?php
namespace App\Http\Controllers;

use App\Models\LabProfile;
use App\Models\User;
use App\Models\LabTest;
use App\Models\Package;
use App\Models\PackageProfile;
use App\Models\LabSelectedPackages;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data["title"] = "Lab Package";
        $data["page_name"] = "List";
    
        // Build the query for packages with join to lab_profiles and labs
        $dataArray = Package::query()
            ->join('lab_profile', 'labs_packages.lab_profile_id', '=', 'lab_profile.id')
            ->join('labs', 'lab_profile.lab_id', '=', 'labs.id')
            ->select(
                'labs_packages.id',
                'labs_packages.package_name',
                'labs_packages.amount as price',
                'labs.name as lab_name',
                'lab_profile.profile_name as lab_profile_name'
            )
            ->orderBy('labs_packages.id', 'desc');
    
        // Apply role-based filtering
        // if (Auth::user()->roles->contains(4)) {
        //     $dataArray->where('labs_packages.lab_id', Auth::user()->id);
        // } elseif (Auth::user()->roles->contains(1)) {
        //     $dataArray->where('labs_packages.lab_id', '!=', '1');
        // }
    
        // Apply search filter if provided
        if (!empty($request->search)) {
            $dataArray->where('labs_packages.package_name', 'LIKE', '%' . $request->search . '%');
        }
    
        // Paginate the results and preserve the query string
        $data['data'] = $dataArray->paginate($request->pagination ?? 20)->withQueryString();
       
        return view("admin.package.index", $data);
    }
    
    
    public function indexDiagno(Request $request)
    {
        $data["title"] = "Digno Package";
        $data["page_name"] = "List";
        $dataArray = Package::where('type','package')->where('lab_id',1);
        $data['data'] = $dataArray->orderBy('id','desc')->paginate(20);
        return view("admin.package.index", $data);
    }

    public function packageProfile(Request $request)
    {
        $LabId = $request->lab_id;
        $lab = LabProfile::where("lab_id", $LabId)->get();

        $outputDataemployees = "";
        if ($lab->count() > 0) {
            foreach ($lab as $item) {
                $outputDataemployees .= "<option value='{$item->id}'>{$item->title}</option>";
            }
        }
        return response()->json([
            "status" => true,
            "outputDataemployees" => $outputDataemployees,
        ]);
    }

    public function getPackageProfile(Request $request)
    {
        $LabId = $request->lab_id;
        $keyWord = $request->q;
        $profileSelectedData = '';
        $lab  = Package::where('type','profile')->where('package_name','like', '%' . $keyWord . '%');
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

    public function create(Request $request)
    {
        $data["page_name"] = "Create";
        $data["title"] = "Add Lab Package";
        $data["hospitals"] = User::whereHas("roles", function ($query) {
            $query->where("id", 4);
        })->get();
        $data["tests"] = Package::orderBy('id','desc')->where('type','profile')->where('lab_id',Auth::user()->id)->take(10)->get();
        $data["packageName"] = Package::where('type','package')->where('lab_id',1)->where('parent_id',null)->orderBy('id','desc')->get();
        $data['subprofile'] = Package::where('type','profile')->where('lab_id',1)->get();
        return view("admin.package.create", $data);
    }

    public function deleted($id, $profile_id)
    {
        PackageProfile::where("package_id", $profile_id)
            ->where("profile_id", $id)
            ->delete();

        return redirect()->back();
    }

    public function amountUpdate(Request $request)
    {
        $package = $request->packagesID;
        $amount = $request->amount;
        $checkbox = $request->checkbox;

       if($package){
         foreach($package as $key => $item){
            $data = Package::findOrFail($item);
            $data = LabSelectedPackages::updateOrCreate([
                    "package_id" => $item,
                    "lab_id" => Auth::user()->id,
                ], [
                    "amount" => $amount[$key],
                    "is_selected" => $checkbox[$key] ?? 0,
                ]
            );
         }
       }
       $message = 'Data Updated Succssfully';
       return redirect()->back()->with("msg", $message);
    }

    public function packageAmountUpdate(Request $request)
    {
        $data = LabSelectedPackages::where('lab_id', Auth::user()->id)->where('package_id', $id)->get();
        return view('admin.package.index',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validationRules = [
            'package_name' => 'required',
        ];
    
        $CheckUser = Package::where('parent_id',$request->parent_id)->where('lab_id',Auth::id())->first();
        $CheckEdit = Package::where('id',$request->id)->first();

        if(is_null($CheckEdit))
        if (empty($request->parent_id) || $CheckUser) {
            $validationRules['package_name'] = 'required|unique:labs_packages,package_name';
        }
        $validatedData = $request->validate($validationRules);
    
        // $customMessages = [
        //     'title.required' => 'The title field is required.',
        //     'title.unique' => 'The title must be unique when parent_id is provided.',
        // ];
    
        // $request->validate($validationRules, $customMessages);
        $labID = Auth::id();
        if(is_null($CheckEdit)){
        if (!is_null($request->parent_id)) {
            $profileCount = Package::where('parent_id', $request->parent_id)
                                    ->where('type', 'package')->where('lab_id',Auth::id())
                                    ->count();
            if ($profileCount >= 3) {
                return redirect()->back()->with(['msg' => 'You can only create 3 Sub profiles with this Profile.']);
            }
        }
    }

        $data = Package::updateOrCreate( [
                "id" => $request->id,
            ], [
                "package_name" => $request->package_name,
                "parent_id" => $request->parent_id,
                "lab_id" => $labID,
                "amount" => $request->amount,
                "is_frequently_booking" => $request->is_frequently_booking ?? 0,
                "is_lifestyle" => $request->is_lifestyle ?? 0,
                "type" => 'package',
            ]
        );
        if (isset($request->image)) {
            $path = public_path("/uploads/package/");
            $uploadImg = $this->uploadDocuments($request->image, $path);
            $data->image = $uploadImg;
            $data->save();
        }

        if (!empty($request->profile_id)) {
            $data->getChilds()->sync($request->profile_id);
        }

        $msg = isset($request->id) && !empty($request->id) ? "Updated Successfully." : "Created Successfully";

        return redirect()->route("package.index")->with("data_created", $msg);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $data["page_name"] = "Show";
        $data["title"] = " Package Details";
        $data["data"] = LabProfile::where("id", $id)->first();
        $data["data"] = Package::where("id", $id)->first();

        return view("admin.package.show", $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $packageData["title"] = "Edit Package";
        $packageData["page_name"] = "Edit";
        $packageData["data"] = Package::find($id);
        $packageData["packageName"] = Package::where('type','package')->where('lab_id',1)->where('parent_id',null)->orderBy('id','desc')->get();
        $packageData["hospitals"] = User::whereHas("roles", function ($query) {
            $query->where("id", 4);
        })->get();

        $hospitalIds = $packageData["data"]->pluck("lab_id")->toArray();
        $profiles = Package::where('type', 'profile')->where('lab_id', Auth::user()->id);

        // if(isset($packageData['data']->getChilds)){
        //     $profiles->whereIn('id', $packageData['data']->getChilds->pluck('id')->toArray());
        // }

        $packageData["profiles"] =  $profiles->orderBy('id','desc')->get();
        $packageData["profile_test"] = LabTest::where("hospital_id", Auth::user()->id)->get();
        return view("admin.package.create", $packageData);
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
    public function destoryPackage($id)
    {
        Package::find($id)->delete();
        return redirect()
            ->route("package.index")
            ->with("msg", "Package Deleted Successfully");
    }
}
