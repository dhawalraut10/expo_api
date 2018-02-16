<?php
namespace App\Http\Controllers;
use App\Customer;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Cache;

class ExpoController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return Response
     */
    public function __construct()
    {
        //
        Cache::flush();
    }
    
    protected function returnResponse($data)
    {
        if(empty($data['data'])) { $data_var = (object)$data['data']; } else { $data_var = $data['data']; } 
        $response = [
            'Data' =>  $data_var,
            'Response' => array(
                        'response_code' => $data['code'],
                        'status' => $data['status'],
                        'status_msg' => $data['statusMsg']
                    ),
            'Error' => array(
                        'error_code' => $data['error_code'],
                        'error_msg' => $data['error_msg']
                    ),
            'Debug' => $data['debug']
        ];

        return response()->json($response, $data['code']);
    }

    public function list(Request $request)
    {
        if ($request->isMethod('post')) {


        }
        else
        {
            $category_details = DB::table('categories')->get();
            $response_array = array(
                    'code' => 200,
                    'error_code' => '',
                    'data' => $category_details,
                    'status' => 'success',
                    'statusMsg' => 'Categories sent successfully',
                    'error_msg' => '',
                    'debug' => "TRUE"
                );
        }
        return $this->returnResponse($response_array); 
    }
    public function saveDetails(Request $request)
    {
        //mail("dhawalraut13@gmail.com","test array",print_r($request->all(),TRUE));exit; 
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'company_name' => 'required'
        ]);

        if($validator->fails())
        {
            //mail("dhawalraut13@gmail.com","test array","here 0");
            $response_array = array(
                'code' => 200,
                'error_code' => '',
                'data' => '',
                'status' => 'fail',
                'statusMsg' => $validator->errors()->first(),
                'error_msg' => 'Validation failed',
                'debug' => "TRUE"
            );
            return $this->returnResponse($response_array);
        }
        else
        {  
            //mail("dhawalraut13@gmail.com","test array","here 2");
            $category = $request->input('category');
            $check_category = DB::table('categories')->where('name',strtolower($request->input('category')))->first();
            //print_r($check_category);exit;
            //mail("dhawalraut13@gmail.com","test array","here 2");
            if(NULL != $check_category)
            {
                $category = $check_category->id;
            }
            else
            {
                $category = DB::table('categories')->insertGetId(['name' => strtolower($request->input('category')), 'created_on' => date('Y-m-d H:i:s'), 'is_deleted' => '0']);
            }

            $id = DB::table('expo_details')->insertGetId(['expo_name' => $request->input('expo_name'), 'name' => $request->input('name'), 'company_name' => $request->input('company_name'), 'email' => $request->input('email'), 'other_contact' => json_encode($request->input('other_contact')), 'category' => $category, 'notes' => $request->input('notes'), 'priority' => $request->input('priority'), 'created_on' => date('Y-m-d H:i:s')]);

            $returnArr = ['expo_detail_id' => $id];
            //mail("dhawalraut13@gmail.com","test array","here 3");

            
            $response_array = array(
                'code' => 200,
                'error_code' => '',
                'data' => $returnArr,
                'status' => 'success',
                'statusMsg' => 'Information saved successfully',
                'error_msg' => '',
                'debug' => TRUE
            );
            return $this->returnResponse($response_array);
        }
    }

    public function saveImage(Request $request)
    {
        //echo "here1";exit;
        if(!file_exists(storage_path('app/uploads')))
        {
            mkdir(storage_path('app/uploads'), 0777, TRUE);
        }

        $destinationPath = storage_path('app/uploads');

        if(NULL != $request->file('upload_files'))
        {
            //echo "here2";exit;
            //mail("dhawalraut13@gmail.com","test array","here 4");
            $id = $request->input('expo_detail_id');
            if($request->file('upload_files')->getClientSize() > 10000000)
            {
                $response_array = array(
                    'code' => 400,
                    'error_code' => '',
                    'data' => array(),
                    'status' => 'failed',
                    'statusMsg' => 'File upload failed',
                    'error_msg' => 'File size too large. You can upload files upto 10 MB',
                    'debug' => TRUE
                    );
                
            }
            else
            {
                //echo "here3";exit;
                if($request->file('upload_files')->isValid())
                {
                    $filename = str_random(40).".".$request->file('upload_files')->getClientOriginalExtension();
                    $request->file('upload_files')->move($destinationPath, $filename);

                    $fileUploaded = DB::table('images')->insertGetId(['expo_detail_id' => $id, 'name' => $filename, 'created_on' => date('Y-m-d H:i:s'), 'is_deleted' => '0']);
                    if($fileUploaded)
                    {
                        $returnArr = ['file_name' => "http://174.141.231.145/exp/expo_api/storage/app/uploads/".$filename];
                        $response_array = array(
                            'code' => 200,
                            'error_code' => '',
                            'data' => $returnArr,
                            'status' => 'success',
                            'statusMsg' => 'File uploaded Successfully',
                            'error_msg' => '',
                            'debug' => TRUE
                        );
                    }
                    else
                    {
                        $response_array = array(
                            'code' => 400,
                            'error_code' => '',
                            'data' => array(),
                            'status' => 'fail',
                            'statusMsg' => 'File not uploaded.',
                            'error_msg' => '',
                            'debug' => TRUE
                        );
                    }
                }
                else
                {
                    $response_array = array(
                            'code' => 400,
                            'error_code' => '',
                            'data' => array(),
                            'status' => 'fail',
                            'statusMsg' => 'File not uploaded',
                            'error_msg' => '',
                            'debug' => TRUE
                        );
                }
            }
        }
        else
        {
            $response_array = array(
                'code' => 200,
                'error_code' => '',
                'data' => array(),
                'status' => 'success',
                'statusMsg' => 'File not selected',
                'error_msg' => '',
                'debug' => TRUE
            );
        }
        return $this->returnResponse($response_array);
    }

    public function expolist()
    {
        $expo_details = DB::table('expo_details')->get();
        // This code is added to prevent double json encode
        $i =0;
        foreach($expo_details as $exp)
        {
            $decoded_json = json_decode($exp->other_contact);
            $expo_details[$i]->other_contact = $decoded_json;
            $i++;
        }
        // Extra code ends here
        if(NULL != $expo_details)
        {
            $response_array = array(
                    'code' => 200,
                    'error_code' => '',
                    'data' => $expo_details,
                    'status' => 'success',
                    'statusMsg' => 'Expos sent successfully',
                    'error_msg' => '',
                    'debug' => "TRUE"
                );
        }
        else
        {
            $response_array = array(
                    'code' => 400,
                    'error_code' => '',
                    'data' => array(),
                    'status' => 'fail',
                    'statusMsg' => 'No expo found',
                    'error_msg' => '',
                    'debug' => "TRUE"
                );
        }
        return $this->returnResponse($response_array);
    }

    public function expodetails($id)
    {
        if(NULL != $id && $id > 0)
        {
            $expo_details = DB::table('expo_details')->where('id',$id)->first();
            $expo_details->other_contact = json_decode($expo_details->other_contact,TRUE);
            //print_r($expo_details);exit;
            if(NULL != $expo_details)
            {
                $response_array = array(
                        'code' => 200,
                        'error_code' => '',
                        'data' => $expo_details,
                        'status' => 'success',
                        'statusMsg' => 'Expos sent successfully',
                        'error_msg' => '',
                        'debug' => "TRUE"
                    );
            }
            else
            {
                $response_array = array(
                        'code' => 400,
                        'error_code' => '',
                        'data' => array(),
                        'status' => 'fail',
                        'statusMsg' => 'No expo found',
                        'error_msg' => '',
                        'debug' => "TRUE"
                    );
            }
            return $this->returnResponse($response_array);
        }
    }
    /* this is comment*/
    /*This comment is added after git setup on server Thanks*/
}
