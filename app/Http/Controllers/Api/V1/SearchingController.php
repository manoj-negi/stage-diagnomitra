<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Package;
use App\Models\User;
use App\Models\LabCities;
use App\Models\LabSelectedPackages;
use App\Models\Role;
use App\Models\UserPrescription;
use App\Models\City;
use App\Helper\ResponseBuilder;
use App\Helper\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\HospitalCollection;
use App\Http\Resources\Lab\PackageCollection;
use App\Http\Resources\Lab\TestCollection;
use App\Http\Resources\Lab\PackagePreviewCollection;
use App\Http\Resources\Lab\ProfilePreviewCollection;
use App\Http\Resources\Lab\ProfileCollection;
use App\Http\Resources\HospitalResource;
use App\Http\Resources\Lab\TestResource;
use App\Http\Resources\HospitalColletion;
use DB;
use Auth;
class SearchingController extends Controller
{
    const MERCHANT_SALT = "lB7ehBSkap"; // Add your Salt here.
    const MERCHANT_SECRET_KEY = "QPcf1rGY"; // Add Merchant Secret Key - Optional

    public function generatePaymentHash(Request $request)
	{
        try {
            // $validator = Validator::make($request->all(), [
            //     'your_hash_name' => 'required',
            //     'your_hash_string' => 'required',
            //     'salt' => 'required',
            // ]);
            // if ($validator->fails()) {
            //     return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            // }
            
            // $response = [
            //     'hashName' => $request->your_hash_name,
            //     'hashString' => $request->your_hash_string,
            //     'hashType' => 'hashVersionV2', // or 'mcpLookup' or other hash types
            //     'postSalt' => $request->salt,
            // ];
            // $finalHash = $this->generateHash($response);
            // return($finalHash);

            $status='pending';
            $firstname='tapan';
            $amount='100';
            $txnid='text100';
            $posted_hash=null;
            $productinfo=null;
            $email='tapang786@gmail.com';
            $udf1 = null;
            $udf2 = null;
            $udf3 = null;
            $udf4 = null;
            $udf5 = null;

            // Salt should be same Post Request
            // if(isset($_POST["additionalCharges"])){
            //     $additionalCharges=$_POST["additionalCharges"];
            //     $retHashSeq = $additionalCharges.'|'.$salt.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
            // }else{
            //     $retHashSeq = $salt.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
            // }

            // hash('sha512',  'QPcf1rGY|' . $txnid . '|' . $amount . '|' . $params['productinfo'] . '|' . $params['firstname'] . '|' . $params['email'] . '|' . $params['udf1'] . '|' . $params['udf2'] . '|' . $params['udf3'] . '|' . $params['udf4'] . '|' . $params['udf5'] . '||||||' . $this->salt);
            
            $key = 'QPcf1rGY';
            $salt = 'lB7ehBSkap';
      
            $payhash_str = $key . '|' . $this->checkNull($txnid) . '|' .$this->checkNull($amount)  . '|' .$this->checkNull($productinfo)  . '|' . $this->checkNull($firstname) . '|' . $this->checkNull($email) . '|' . $this->checkNull($udf1) . '|' . $this->checkNull($udf2) . '|' . $this->checkNull($udf3) . '|' . $this->checkNull($udf4) . '|' . $this->checkNull($udf5) . '||||||' . $salt;
            $paymentHash = strtolower(hash('sha512', $payhash_str));
            return $paymentHash; 
            // return ResponseBuilder::success($this->response, 'Filter applied successfully!');   
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    public function checkNull($value) {
        if ($value == null) {
              return '';
        } else {
              return $value;
        }
    }


    // function getHashes($txnid, $amount, $productinfo, $firstname, $email, $user_credentials, $udf1, $udf2, $udf3, $udf4, $udf5,$offerKey,$cardBin)
    // {
    //       // $firstname, $email can be "", i.e empty string if needed. Same should be sent to PayU server (in request params) also.
    //       $key = 'XXXXXX';
    //       $salt = 'YYYYY';
    
    //       $payhash_str = $key . '|' . checkNull($txnid) . '|' .checkNull($amount)  . '|' .checkNull($productinfo)  . '|' . checkNull($firstname) . '|' . checkNull($email) . '|' . checkNull($udf1) . '|' . checkNull($udf2) . '|' . checkNull($udf3) . '|' . checkNull($udf4) . '|' . checkNull($udf5) . '||||||' . $salt;
    //       $paymentHash = strtolower(hash('sha512', $payhash_str));
    //       $arr['payment_hash'] = $paymentHash;
    
    //       $cmnNameMerchantCodes = 'get_merchant_ibibo_codes';
    //       $merchantCodesHash_str = $key . '|' . $cmnNameMerchantCodes . '|default|' . $salt ;
    //       $merchantCodesHash = strtolower(hash('sha512', $merchantCodesHash_str));
    //       $arr['get_merchant_ibibo_codes_hash'] = $merchantCodesHash;
    
    //       $cmnMobileSdk = 'vas_for_mobile_sdk';
    //       $mobileSdk_str = $key . '|' . $cmnMobileSdk . '|default|' . $salt;
    //       $mobileSdk = strtolower(hash('sha512', $mobileSdk_str));
    //       $arr['vas_for_mobile_sdk_hash'] = $mobileSdk;
    
    //     // added code for EMI hash
    //       $cmnEmiAmountAccordingToInterest= 'getEmiAmountAccordingToInterest';
    //       $emi_str = $key . '|' . $cmnEmiAmountAccordingToInterest . '|'.checkNull($amount).'|' . $salt;
    //       $mobileEmiString = strtolower(hash('sha512', $emi_str));
    //      $arr['emi_hash'] = $mobileEmiString;
    
    
    //       $cmnPaymentRelatedDetailsForMobileSdk1 = 'payment_related_details_for_mobile_sdk';
    //       $detailsForMobileSdk_str1 = $key  . '|' . $cmnPaymentRelatedDetailsForMobileSdk1 . '|default|' . $salt ;
    //       $detailsForMobileSdk1 = strtolower(hash('sha512', $detailsForMobileSdk_str1));
    //       $arr['payment_related_details_for_mobile_sdk_hash'] = $detailsForMobileSdk1;
    
    //       //used for verifying payment(optional)
    //       $cmnVerifyPayment = 'verify_payment';
    //       $verifyPayment_str = $key . '|' . $cmnVerifyPayment . '|'.$txnid .'|' . $salt;
    //       $verifyPayment = strtolower(hash('sha512', $verifyPayment_str));
    //       $arr['verify_payment_hash'] = $verifyPayment;
    
    //       if($user_credentials != NULL && $user_credentials != '')
    //       {
    //             $cmnNameDeleteCard = 'delete_user_card';
    //             $deleteHash_str = $key  . '|' . $cmnNameDeleteCard . '|' . $user_credentials . '|' . $salt ;
    //             $deleteHash = strtolower(hash('sha512', $deleteHash_str));
    //             $arr['delete_user_card_hash'] = $deleteHash;
    
    //             $cmnNameGetUserCard = 'get_user_cards';
    //             $getUserCardHash_str = $key  . '|' . $cmnNameGetUserCard . '|' . $user_credentials . '|' . $salt ;
    //             $getUserCardHash = strtolower(hash('sha512', $getUserCardHash_str));
    //             $arr['get_user_cards_hash'] = $getUserCardHash;
    
    //             $cmnNameEditUserCard = 'edit_user_card';
    //             $editUserCardHash_str = $key  . '|' . $cmnNameEditUserCard . '|' . $user_credentials . '|' . $salt ;
    //             $editUserCardHash = strtolower(hash('sha512', $editUserCardHash_str));
    //             $arr['edit_user_card_hash'] = $editUserCardHash;
    
    //             $cmnNameSaveUserCard = 'save_user_card';
    //             $saveUserCardHash_str = $key  . '|' . $cmnNameSaveUserCard . '|' . $user_credentials . '|' . $salt ;
    //             $saveUserCardHash = strtolower(hash('sha512', $saveUserCardHash_str));
    //             $arr['save_user_card_hash'] = $saveUserCardHash;
    
    //             $cmnPaymentRelatedDetailsForMobileSdk = 'payment_related_details_for_mobile_sdk';
    //             $detailsForMobileSdk_str = $key  . '|' . $cmnPaymentRelatedDetailsForMobileSdk . '|' . $user_credentials . '|' . $salt ;
    //             $detailsForMobileSdk = strtolower(hash('sha512', $detailsForMobileSdk_str));
    //             $arr['payment_related_details_for_mobile_sdk_hash'] = $detailsForMobileSdk;
    //       }
    
    
    //       // if($udf3!=NULL && !empty($udf3)){
    //             $cmnSend_Sms='send_sms';
    //             $sendsms_str=$key . '|' . $cmnSend_Sms . '|' . $udf3 . '|' . $salt;
    //             $send_sms = strtolower(hash('sha512',$sendsms_str));
    //             $arr['send_sms_hash']=$send_sms;
    //       // }
    
    
    //       if ($offerKey!=NULL && !empty($offerKey)) {
    //                   $cmnCheckOfferStatus = 'check_offer_status';
    //                         $checkOfferStatus_str = $key  . '|' . $cmnCheckOfferStatus . '|' . $offerKey . '|' . $salt ;
    //                   $checkOfferStatus = strtolower(hash('sha512', $checkOfferStatus_str));
    //                   $arr['check_offer_status_hash']=$checkOfferStatus;
    //             }
    
    
    //             if ($cardBin!=NULL && !empty($cardBin)) {
    //                   $cmnCheckIsDomestic = 'check_isDomestic';
    //                         $checkIsDomestic_str = $key  . '|' . $cmnCheckIsDomestic . '|' . $cardBin . '|' . $salt ;
    //                   $checkIsDomestic = strtolower(hash('sha512', $checkIsDomestic_str));
    //                   $arr['check_isDomestic_hash']=$checkIsDomestic;
    //             }
    
    
    
    //     return $arr;
    // }
    

    public function getSHA512Hash($hashData) {
        $hash = hash('sha512', $hashData);
        return $hash;
    }
    public function latLongCity(Request $request) {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
        }
      
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $apiKey = env('GOOGLE_GEOCODE_API_KEY');

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $latitude . ',' . $longitude . '&key=' . $apiKey;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($response);
        if ($output && $output->status === 'OK') {
            $results = $output->results;
            $addressComponents = $results[0]->address_components;
            $cityName = "";

            foreach ($addressComponents as $component) {
                $types = $component->types;
                if (in_array("locality", $types) && in_array("political", $types)) {
                    $cityName = $component->long_name;
                }
            }
            if ($cityName == "") {
                return ResponseBuilder::error('Error occurred while fetching the address.', $this->badRequest); 
            } 
            $City = City::where('city',$cityName)->pluck('id')->toArray();
            $LabCities = LabCities::whereIn('city',$City)->count();
            $data['available_serives'] = $LabCities > 0 ? true : false;
            $data['city_id'] = $LabCities > 0 ? ($City[0] ?? '') : '' ;
            return ResponseBuilder::success($data,'sucess');

        } 
        return ResponseBuilder::error('Error occurred while fetching the address.', $this->badRequest);  

    }
    public function getHmacSHA256Hash($hashData, $salt) {
        // $key = utf8_encode($salt);
        $key = mb_convert_encoding($salt, 'UTF-8', 'ISO-8859-1');
        $hash = hash_hmac('sha256', $hashData, $key, true);
        $hmacBase64 = base64_encode($hash);
        return $hmacBase64;
    }
    public function uploadPre(Request $request) {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
      
        } else {
            return ResponseBuilder::error("User not found", $this->unauthorized);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
        }
        $path = 'uploads/pre';
        // $file = $this->uploadDocuments($request->file('file'), $path) ?? '';
        $files = $request->file('file');
        $imageName = rand(1000,9999).$files->getClientOriginalName();
        $files->move($path, $imageName);
        $file = url($path.'/'.$imageName);
        UserPrescription::create([
            'user_id' => $user->id,
            'prescription_file' => $imageName,
            'prescription_title' => $request->prescription_title ?? '',
        ]);
        return ResponseBuilder::success('', 'success');   
    }
    public function userPrescription(Request $request)
    {
        $UserAddresses = UserPrescription::where('user_id',Auth::user()->id)->orderBy('id','desc')->get()->map(function($data){
            return [
                'id' => $data->id,
                'prescription_file' => !empty($data->prescription_file) ? url('uploads/pre',$data->prescription_file) : '',
                'prescription_title' => $data->prescription_title,
                'uploaded_date' => date('d F Y h:i A',strtotime($data->created_at)),
            ];
        });
    return ResponseBuilder::successMessage('Success',  $this->success , $UserAddresses);
    }
    public function getHmacSHA1Hash($hashData, $salt) {
        // $key = utf8_encode($salt);
        $key = mb_convert_encoding($salt, 'UTF-8', 'ISO-8859-1');
        $hash = hash_hmac('sha1', $hashData, $key);
        return $hash;
    }
    public  function generateHash($response) {
        
        $hashName = $response['hashName'];
        $hashStringWithoutSalt = $response['hashString'];
        $hashType = $response['hashType'];
        $postSalt = $response['postSalt'];
        $hash = "";

        if ($hashType === 'hashVersionV2') {
            $hash = self::getHmacSHA256Hash($hashStringWithoutSalt, self::MERCHANT_SALT);
        } elseif ($hashName === 'mcpLookup') {
            $hash = self::getHmacSHA1Hash($hashStringWithoutSalt, self::MERCHANT_SECRET_KEY);
        } else {
            $hashDataWithSalt = $hashStringWithoutSalt . self::MERCHANT_SALT;
            if ($postSalt !== null) {
                $hashDataWithSalt .= $postSalt;
            }
            $hash = self::getSHA512Hash($hashDataWithSalt);
        }

        return [$hashName => $hash];
    }

	public function search(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'keyword' => 'required',
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            }
            
            $keyword = $request->keyword;
            
            // $LabProfile = LabProfile::where('title', 'LIKE', "%$keyword%")->pluck('id')->toArray();
            // $LabProfile = PackageProfile::whereIn('profile_id',$LabProfile)->pluck('package_id')->toArray();


            $Package = Package::where('package_name', 'LIKE', "%$keyword%")->pluck('id')->toArray();

            // $LabTest = LabTest::where('test_name', 'LIKE', "%$keyword%")->pluck('id')->toArray();
            // $LabProfileTests = LabProfileTests::whereIn('test_id',$LabTest)->pluck('profile_id')->toArray();
            // $LabTest = PackageProfile::whereIn('profile_id',$LabProfileTests)->pluck('package_id')->toArray();

            // $mergedArray = [];
            // if (!empty($LabProfile) || !empty($Package) || !empty($LabTest)) {
                // $arraysToMerge = array_filter([$LabProfile, $Package, $LabTest], function ($array) {
                    // return !empty($array);
                // });
                // $mergedArray = Arr::collapse($arraysToMerge);
            // }
            
            $mergedArray = array_values(array_unique($Package));
            $getLabByCities = Helper::getLabByCities($request->city_id ?? null);
            $LabSelectedPackages = LabSelectedPackages::whereIn('package_id',$mergedArray)->whereIn('lab_id',$getLabByCities)->where('is_selected',true)->get();
            $this->response = new PackageCollection($LabSelectedPackages);
            return ResponseBuilder::success($this->response, 'Filter applied successfully!');   
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    public function labData(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'lab_id' => 'required',
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            }
            $keyword = $request->search;
            $lab = User::where('id',$request->lab_id)->first();

            if(!$lab){
                return ResponseBuilder::error('Lab not found!', $this->badRequest);  
            }
            
            // $Package = Package::where('lab_id', $request->lab_id);
            // $LabProfile = LabProfile::where('lab_id', $request->lab_id);
            // $LabTest = LabTest::where('hospital_id', $request->lab_id);

            // if(!empty($request->search)){
                // $Package->where('package_name', 'LIKE', "%$keyword%");
                // $LabProfile->where('title', 'LIKE', "%$keyword%");
                // $LabTest->where('test_name', 'LIKE', "%$keyword%");
            // }
            $LabSelectedPackages = LabSelectedPackages::where('lab_id',$lab->id)->where('is_selected',true)->paginate(50);
            $this->response->lab_data = new HospitalResource($lab);
            $this->response->package = new PackageCollection($LabSelectedPackages);

            // $returnData['PackageCollection'] = new PackageCollection($Package->get());
            // $returnData['ProfileCollection'] = new ProfileCollection($LabProfile->get());
            // $returnData['TestCollection'] = new TestCollection($LabTest->get());

            return ResponseBuilder::success($this->response, 'Lab data fetch successfully');   
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    public function labTests(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'lab_id' => 'required',
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            }
            $keyword = $request->keyword;
            $lab = User::where('id',$request->lab_id)->first();

            if(!$lab){
                return ResponseBuilder::error('Lab not found!', $this->badRequest);  
            }
           
            $tests = Package::where('lab_id', $request->lab_id)->where('type','test');
            if(!empty($keyword)){
                $tests->where('package_name', 'LIKE', "%$keyword%");
            }
            $tests = $tests->paginate(50);
            $this->response = new TestCollection($tests);
            return ResponseBuilder::successWithPagination($tests,$this->response, 'Lab data fetch successfully'); 
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    public function multiTestsLab(Request $request){
        $validator = Validator::make($request->all(), [
            'test' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
        }
        $testIds = explode(",",$request->test);
        $labId = LabSelectedPackages::whereIn('package_id', $testIds)
                ->where('is_selected',true)
                ->groupBy('lab_id')
                ->havingRaw('COUNT(DISTINCT package_id) = ' . count($testIds))
                ->pluck('lab_id')
                ->toArray();

        $labs = User::whereIn('id', $labId);
        if(!empty($request->city_id)){
            $getLabByCities = Helper::getLabByCities($request->city_id ?? null);
            $labs->whereIn('id',$getLabByCities);
        }
        $labs = $labs->get();
        $labdata = new HospitalCollection($labs);
        $tests = Package::whereIn('id',$testIds)->where('type','test')->get();
        $testData = new TestCollection($tests);
        $labdata = $labdata->map(function($data) use ($labId,$testIds){
        $LabSelectedPackages = LabSelectedPackages::whereIn('package_id',$testIds)->where('lab_id',$data->id)->get();
        return [
            'lab_data' => $data,
            'test_data' => $LabSelectedPackages->map(function($testData){ 
                return [ 
                    'id' => isset($testData->packageData) ? $testData->packageData->id : '', 
                    'test_name' => isset($testData->packageData) ? $testData->packageData->package_name : '', 
                    'amount' => isset($testData->amount) ? $testData->amount : (isset($testData->packageData->amount) ? $testData->packageData->amount : '0'), 
                    'description' => isset($testData->packageData) ? $testData->packageData->description : '', 
                ]; 
            }),
        ];
       });
       return ResponseBuilder::success($labdata, 'result fetch successfully');
    }
    public function searchLab(Request $request)
	{
        try {
            $keyword = $request->keyword;
        
             $lab = Role::where('role', 'Lab')->first()->users();

             /** data according cities */
             if(!empty($request->city_id)){
                 $getLabByCities = Helper::getLabByCities($request->city_id ?? null);
                 $lab->whereIn('id',$getLabByCities);
             }
            $lab  = $lab->pluck('id')->toArray();
            $keyword = Helper::searchShortKeys($keyword);
            $Package = Package::whereIn('lab_id',$lab)->where('package_name', 'LIKE', "%$keyword%")->where('type','test')->get();
            $this->response = new TestCollection($Package);
            return ResponseBuilder::success($this->response, 'Tests fetch successfully');
            // $lab = $lab->pluck('id')->toArray();
            // $lab = array_values(array_unique($lab));
            // $mergedArray =  Package::where('package_name', 'LIKE', "%$keyword%")->where('type','test')->pluck('id')->toArray();
            // $LabSelectedPackages = LabSelectedPackages::whereIn('package_id',$mergedArray)->where('is_selected',true)->pluck('lab_id')->toArray();
            
            // if(!empty($LabSelectedPackages) && !empty($lab)){
            //     $lab = array_merge($lab,$LabSelectedPackages);
            // }elseif(!empty($LabSelectedPackages)){
            //     $lab = $LabSelectedPackages;
            // }

            // $lab = User::whereIn('id',$lab)->get();
            // $labData = new HospitalCollection($lab);
            // foreach($labData as $item){
            //     $tests = Package::where('package_name', 'LIKE', "%$keyword%")->where('type','test')->where('lab_id',$item->id)->first();
            //     $return[]['lab_data'] = $item;
            //     $return[]['test'] = !empty($tests) ? new TestResource($tests):'';
            // }
            
            // $this->response = $return ?? [];
            // return ResponseBuilder::success($this->response, 'Lab data fetch successfully');   
            

            /**V Care plus logic */
            $Package = Package::where('package_name', 'LIKE', "%$keyword%")->where('type','test')->get();
            $this->response = new TestCollection($Package);
            return ResponseBuilder::success($this->response, 'Lab data fetch successfully');
            
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    public function labsPackage(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'package_id' => 'required',
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            }

            /**data according cities */

            $getLabByCities = Helper::getLabByCities($request->city_id ?? null);
            // $getLabByCities = Helper::getLabByCities();

            $LabSelectedPackages = LabSelectedPackages::where('package_id',$request->package_id)->whereIn('lab_id',$getLabByCities)->where('is_selected',true)->whereHas('packageData', function ($query) {
                return $query->where('type', '=', 'package');
            })->get();
            $this->response = new PackageCollection($LabSelectedPackages);
            return ResponseBuilder::success($this->response, 'Labs fetch successfully');   
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    public function profileLabs(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            }

            /**data according cities */

            $getLabByCities = Helper::getLabByCities($request->city_id ?? null);
            // $getLabByCities = Helper::getLabByCities();

            $LabSelectedPackages = LabSelectedPackages::where('package_id',$request->profile_id)->whereIn('lab_id',$getLabByCities)->where('is_selected',true)->whereHas('packageData', function ($query) {
                return $query->where('type', '=', 'profile');
            })->get();
            $this->response = $LabSelectedPackages->map(function($data){
                return [
                    "id"                => isset($data->packageData) ? $data->packageData->id : '',
                    "package_name"      => isset($data->packageData) ? $data->packageData->package_name : '',            
                    "amount"            => !empty($data->amount) ? $data->amount : (isset($data->packageData) ? $data->packageData->amount : '0.00'),            
                    "lab"               => isset($data->labDetails) && !empty($data->labDetails) ? new HospitalResource($data->labDetails) : '',            
                    "tests"          => isset($data->packageData->getChilds) && !empty($data->packageData->getChilds) ? new ProfileCollection($data->packageData->getChilds) : [],            
                ];
            });
            return ResponseBuilder::success($this->response, 'Labs fetch successfully');   
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    public function packageList(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:all,is_lifestyle,is_frequently_booking',
            ]);
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);  
            }

            $Package = Package::orderBy('id','desc')->where('type','package');
            if($request->type=='is_lifestyle'){
                $Package->where('is_lifestyle',true);
            }
            if($request->type=='is_frequently_booking'){
                $Package->where('is_frequently_booking',true);
            }
            $Package = $Package->get();
            $this->response = new PackagePreviewCollection($Package);

            return ResponseBuilder::success($this->response, 'Package list');   
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    public function diagnoProfileList(Request $request)
	{
        try {
            $profiles = Package::orderBy('id','desc')->where('type','profile')->where('lab_id',1);
            $profiles = $profiles->get();
            $this->response = new ProfilePreviewCollection($profiles);
            return ResponseBuilder::success($this->response, 'Profile list');   
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }
    public function cities(Request $request)
	{
        try {
            $data = DB::table('cities')->select('id','city')->get();

            return ResponseBuilder::success($data, 'Cities list');   
        }
        catch (exception $e) {
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

}
