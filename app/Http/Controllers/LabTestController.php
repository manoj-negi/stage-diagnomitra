<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\HospitalCategory; 
use File;
use Datatables;
use App\Models\Role;
use App\Models\Doctor_hospital; 
use App\Models\User;
use Auth;
use App\Models\State;
use App\Models\hospitalsReport;
use App\Models\City;
use App\Models\LabTest;
use App\Models\HospitalDoctor;
use App\Models\PatientReport;
use App\Models\Appointment;
use Illuminate\Support\Facades\Hash;

class LabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['title'] = "Lab Test";
        $data['page_name'] = "List";
        $data['labs'] = User::whereHas('roles', function($q){ $q->where('id','=', 4); })->get();

        $dataTest = LabTest::query()
            ->join('labs', 'labs_tests.lab_id', '=', 'labs.id')
            ->select('labs_tests.*', 'labs.name as lab_name');

        if(!empty($request->search)){
            $dataTest->where('labs_tests.test_name', 'LIKE', '%' . $request->search . '%');
        }

        if(!empty($request->lab)){
            $dataTest->where('labs_tests.lab_id', $request->lab);
        }

        if(Auth::user()->roles->contains(4)){
            $dataTest->where('labs_tests.lab_id', Auth::user()->id);
        }

        $data['data'] = $dataTest->orderBy('labs_tests.id', 'DESC')
            ->paginate($request->pagination ?? 10)
            ->withQueryString();

        return view('admin.lab-test.index', $data);
    }

    public function indexDiagno(Request $request)
    {
        $data['title'] = "Lab Test";
        $data['page_name'] = "List";
        $dataTest = LabTest::where('lab_id', 1);

        if(!empty($request->search)){
            $dataTest->where('test_name', 'LIKE', '%' . $request->search . '%');
        }
        if(!empty($request->lab)){
            $dataTest->where('lab_id', $request->lab);
        }

        $data['data'] = $dataTest->orderBy('id', 'DESC')->paginate($request->pagination ?? 10)->withQueryString();
        return view('admin.lab-test.index', $data);
    }

    public function labtestUpdate(Request $request, $id)
    {
        $data = LabTest::find($id);
        $data->update(['admin_status' => $request->admin_status]);
        return redirect()->back()->with('msg', 'Status Approved');
    }

    public function hospitalUpdate(Request $request, $id)
    {
        $data = User::where('id', $id)->first();
        $data->is_approved = $request->is_approved;
        $data->save();

        return redirect()->back()->with('msg', 'Status Updated Successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $result['title'] = "Lab";
        $result['page_name'] = "Create";
        $result['labs'] = User::whereHas('roles', function ($query) { 
            $query->where('id', 4); 
        })->get();
        return view('admin.lab-test.create', $result);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'test_name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'lab_id' => 'required|exists:labs,id',
        ]);
    
        $labID = $request->lab_id;
        if (Auth::user()->roles->contains(4)) {
            $labID = Auth::user()->id;
        }
    
        $labTest = LabTest::updateOrCreate(
            ['id' => $request->id],
            [
                'test_name' => $request->test_name,
                'amount' => $request->amount,
                'lab_id' => $labID,
            ]
        );
    
        if ($labTest) {
            $message = $request->id ? 'Test Successfully Updated' : 'Test Successfully Created';
            return redirect()->to($request->url)->with('msg', $message);
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
        $data['appointments'] = Appointment::where('hospital_id', $id)->get();
        $data['doctor'] = HospitalDoctor::where('hospital_id', $id)->get();
        $data['title'] = "Lab";
        $data['page_name'] = "List";
        $get['abc'] = User::where('is_hospital', 1)->get();
        $asd = HospitalDoctor::all();
        return view('admin.lab-test.show', $data, $get)->with('HospitalDoctor', $asd);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $result['title'] = "Edit Lab Test";
        $result['page_name'] = "Edit";
        $result['states'] = State::all();
        $result["data"] = LabTest::findOrFail($id);
        $result['labs'] = User::whereHas('roles', function ($query) { $query->where('id', 4); })->get();
        if ($result["data"]) {
            return view("admin.lab-test.create", $result);
        } else {
            return redirect()->back()->with("error", "Data not found");
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
        // Implementation for updating lab test (if needed)
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = LabTest::find($id);
        $data->delete();
        return redirect()->back()->with('msg', 'Test Deleted Successfully');
    }

    public function autocomplete(Request $request)
    {
        $term = $request->input('term');
        $tests = LabTest::where('test_name', 'LIKE', "%{$term}%")->get();

        $results = $tests->map(function ($test) {
            return [
                'id' => $test->id,
                'text' => $test->test_name,
            ];
        });

        if ($results->isEmpty()) {
            $results->push([
                'id' => 'new',
                'text' => 'Create new test',
            ]);
        }

        return response()->json($results);
    }
}
