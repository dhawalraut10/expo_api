<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use App\Expo;
use Validator;
use DB;
use Cache;
//use Mail;
use view;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

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
        // print_r($request->all());
        // exit;
        //mail("dhawalraut13@gmail.com","test array",print_r($request->all(),TRUE));exit; 
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'company_name' => 'required',
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
        $id = $request->input('expo_id');
        $company_local_id = $request->input('company_local_id');
        $image_type = $request->input('image_type');
        //print_r($request->all());exit;
        /*if(NULL != $request->file('upload_files'))
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
            {*/
                //echo "here3";exit;
                /*if($request->file('upload_files')->isValid())
                {*/
                    //mail("dhawalraut13@gmail.com","upload file 1", "a");
                    foreach($request->file('upload_files') as $uploaded_files)
                    {
                        $filename = str_random(40).".".$uploaded_files->getClientOriginalExtension();
                        $filename_arr[] = "http://182.75.51.133/expo_api/storage/app/uploads/".$filename;
                        $uploaded_files->move($destinationPath, $filename);

                        $fileUploaded = DB::table('images')->insertGetId(['expo_detail_id' => $id, 'name' => $filename, 'company_local_id' => $company_local_id, 'image_type' => $image_type, 'created_on' => date('Y-m-d H:i:s'), 'is_deleted' => '0']);
                        //mail("dhawalraut13@gmail.com","upload file 2", print_r($fileUploaded,true));
                    }
                        //mail("dhawalraut13@gmail.com","upload file 123","filename");
                    //exit;

                    if($fileUploaded)
                    {
                        //mail("dhawalraut13@gmail.com","upload file 3", "3");
                        $returnArr = ['file_name'        => $filename_arr,
                                      'company_local_id' => $company_local_id];
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
                        //mail("dhawalraut13@gmail.com","upload file 4", "4");
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
               /* }
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
                }*/
        /*    }
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
        }*/
        //mail("dhawalraut13@gmail.com","upload file 5", print_r($response_array,true));
        return $this->returnResponse($response_array);
    }

    public function saveImage2(Request $request)
    {
        //echo "<pre>";print_r($request->all());exit;
        if(!file_exists(storage_path('app/uploads')))
        {
            mkdir(storage_path('app/uploads'), 0777, TRUE);
        }

        $destinationPath = storage_path('app/uploads');
        

        /*foreach($request->input('companyRecords') as $uploaded_files)
        {
            $company_local_id = $request->input('company_local_id');
            $image_type = $request->input('companyRecords.0.image_type');
            $image_record_id = $request->input('companyRecords.0.recordId');
            $user_id = $request->input('u_id');

            //echo $request->input('companyRecords.0.image_type');exit;
            $filename = str_random(40);
            // $filename = str_random(40).".".$uploaded_files->getClientOriginalExtension();
            // $filename_arr[] = "http://182.75.51.133/expo_api/storage/app/uploads/".$filename;
            // $uploaded_files->move($destinationPath, $filename);
            $filename_arr[] = $filename;

            $fileUploaded = DB::table('images')->insertGetId(['name' => $filename, 'company_local_id' => $company_local_id, 'image_type' => $image_type, 'image_record_id' => $image_record_id, 'user_id' => $user_id, 'created_on' => date('Y-m-d H:i:s'), 'is_deleted' => '0']);
        }*/

        foreach($request->file('upload_files') as $uploaded_files)
        {
            $company_local_id = $request->input('company_local_id');
            $user_id = $request->input('u_id');

            $file = $uploaded_files->getClientOriginalName();
            $filename = pathinfo($file, PATHINFO_FILENAME);

            //separate file name
            $separateFilenameAndType = explode(";", $filename);

            $image_type = $separateFilenameAndType[0];
            $image_record_id = $separateFilenameAndType[1];

            $new_filename = $image_record_id."_".str_random(40).".".$uploaded_files->getClientOriginalExtension();
            $filename_arr[$image_record_id] = "http://182.75.51.133/expo_api/storage/app/uploads/".$new_filename;
            $uploaded_files->move($destinationPath, $new_filename);

            $fileUploaded = DB::table('images')->insertGetId(['name' => $new_filename, 'company_local_id' => $company_local_id, 'image_type' => $image_type, 'image_record_id' => $image_record_id, 'user_id' => $user_id, 'created_on' => date('Y-m-d H:i:s'), 'is_deleted' => '0']);
        }

        if($fileUploaded)
        {
            $returnArr = ['file_name'        => $filename_arr,
                          'company_local_id' => $company_local_id];
                          
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
        return $this->returnResponse($response_array);
    }

    public function expolist()
    {
        $expo_details = DB::table('expo_details')->get();
        // This code is added to prevent double json encode
        $i =0;
        //print_r($expo_details);exit;
        foreach($expo_details as $exp)
        {
            $decoded_json = json_decode($exp->other_contact);
            $expo_details[$i]->other_contact = $decoded_json;
            $expo_details[$i]->is_selected = 0;
            $i++;
        }
        // /print_r($expo_details);exit;
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
    
    public function saveDetailsRecursively(Request $request)
    {
        $expoArr = array(
            'expo_name' => strtolower($request->input('expo_name')),
            'created_on' => date('Y-m-d H:i:s'),
            'customer_id' => $request->input('customer_id')
        );

        $expo_datails = DB::table('expo_details')->insertGetId($expoArr);
        $expo_details_after_insertion = DB::table('expo_details')->where('id',$expo_datails)->first();

        if(NULL != $expo_datails)
        {
            $response_array = array(
                'code' => 200,
                'error_code' => '',
                'data' => $expo_details_after_insertion,
                'status' => 'success',
                'statusMsg' => 'Expo registred successfully',
                'error_msg' => '',
                'debug' => "TRUE"
            );
            return $this->returnResponse($response_array);
        }
        else
        {
            $response_array = array(
                'code' => 200,
                'error_code' => '',
                'data' => '',
                'status' => 'fail',
                'statusMsg' => 'Expo not registred',
                'error_msg' => '',
                'debug' => "TRUE"
            );
            return $this->returnResponse($response_array);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
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
            $check_user_exists = DB::table('users')->where([
                ['email', '=', strtolower($request->input('email'))],
                ['password', '=', MD5($request->input('password'))]
            ])->first();
            /* added here */
            if(NULL != $check_user_exists)
            {
                $user_id = $check_user_exists->id;
                $restore_data = DB::select("SELECT
                                                ed.expo_name,
                                                ed.id as expo_table_id,
                                                ed.customer_id,
                                                ed.expo_local_id as localExpoId,
                                                cd.name as companyName,
                                                cd.company_local_id,
                                                cd.expo_local_id as company_expo_id,
                                                cd.note as company_note,
                                                cd.priority,
                                                cd.id as company_table_id,
                                                cd.company_tags,
                                                i.image_record_id,
                                                i.name as image_name,
                                                i.image_type,
                                                i.id as image_table_id,
                                                i.company_local_id as image_company_local_id
                                            FROM
                                                expo_details ed left join  
                                                company_details cd on ed.expo_local_id = cd.expo_local_id
                                                left join images i on (cd.company_local_id = i.company_local_id AND i.is_deleted = 0)
                                            WHERE
                                                ed.customer_id = '".$user_id."'");
                

                $expo = [];
                $company = [];
                $image = [];

                $expo_id = 0;
                $company_id = 0;
                $image_id = 0;
                $allTags = [];
                $i=0;

                if(NULL != $restore_data)
                {
                    foreach($restore_data as $allData)
                    {
                        if($expo_id !=  $allData->localExpoId)
                        {
                            $expo['records']['expo'][$allData->localExpoId] =  array('localExpoId' => $allData->localExpoId,
                                            'expo_name' =>  $allData->expo_name,
                                            'id' => $allData->expo_table_id);
                            $expo_id = $allData->localExpoId;

                            if(NULL != $allData->company_local_id)
                            {
                                //echo $allData->company_local_id;
                                if($company_id != $allData->company_local_id)
                                {
                                    $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id] = array('company_name' => $allData->companyName,
                                                                'company_local_id' => $allData->company_local_id,
                                                                'expo_local_id' => $allData->company_expo_id,
                                                                'note' => $allData->company_note,
                                                                'priority' => $allData->priority,
                                                                'company_table_id' => $allData->company_table_id,
                                                                'tags' => json_decode($allData->company_tags,TRUE));
                                    $temp_array_tag = [];
                                    $temp_array_tag = json_decode($allData->company_tags,TRUE);
                                    if(!empty($temp_array_tag))
                                    {
                                        $allTags = array_merge($allTags,$temp_array_tag);
                                    }

                                    $company_id = $allData->company_local_id;
                                }
                                if($image_id != $allData->image_record_id)
                                {
                                    if(NULL != $allData->image_record_id)
                                    {
                                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'][] = array('image_record_id' => $allData->image_record_id,
                                                                    'image_name' => $allData->image_name,
                                                                    'image_type' => $allData->image_type,
                                                                    'image_table_id' => $allData->image_table_id,
                                                                    'image_company_local_id' => $allData->image_company_local_id);                        
                                    }
                                    else
                                    {
                                       $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'] = []; 
                                    }

                                    $image_id = $allData->image_record_id;
                                }
                                else
                                {
                                    $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'] = []; 
                                }
                            }
                            else
                                $expo['records']['expo'][$allData->localExpoId]['company'] = [];

                            //print_r($expo);

                        }
                        else
                        {
                            if(NULL != $allData->company_local_id)
                            {
                                if($company_id ==  $allData->company_local_id)
                                {
                                    //echo $allData->company_local_id;
                                    if(NULL != $allData->image_record_id)
                                    {
                                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'][] = array('image_record_id' => $allData->image_record_id,
                                                                    'image_name' => $allData->image_name,
                                                                    'image_type' => $allData->image_type,
                                                                    'image_table_id' => $allData->image_table_id,
                                                                    'image_company_local_id' => $allData->image_company_local_id);
                                    }
                                    else
                                    {
                                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'] = []; 
                                    }
                                }
                                else
                                {
                                    $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id] = array('company_name' => $allData->companyName,
                                                                'company_local_id' => $allData->company_local_id,
                                                                'expo_local_id' => $allData->company_expo_id,
                                                                'note' => $allData->company_note,
                                                                'priority' => $allData->priority,
                                                                'company_table_id' => $allData->company_table_id,
                                                                'tags' => json_decode($allData->company_tags,TRUE));

                                    $temp_array_tag = [];
                                    $temp_array_tag = json_decode($allData->company_tags,TRUE);
                                    if(!empty($temp_array_tag))
                                    {
                                        $allTags = array_merge($allTags,$temp_array_tag);
                                    }

                                    //$company_id = $allData->localExpoId;

                                    if(NULL != $allData->image_record_id)
                                    {
                                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'][] = array('image_record_id' => $allData->image_record_id,
                                                                    'image_name' => $allData->image_name,
                                                                    'image_type' => $allData->image_type,
                                                                    'image_table_id' => $allData->image_table_id,
                                                                    'image_company_local_id' => $allData->image_company_local_id);
                                    }
                                    else
                                    {
                                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'] = [];
                                    }
                                    $company_id = $allData->company_local_id;
                                }
                            }
                            else
                                $expo['records']['expo'][$allData->localExpoId]['company'] = [];

                            //print_r($expo);
                        }
                        $i++;
                    }
                    if(count($expo['records']['expo']) > 0)
                    {
                        $data['records'] = $expo['records'];
                    }

                    //print_r($expo);
                    //exit;
                }
                else
                {
                    $data['records']['expo'] = [];
                }
                $data['allTags'] = array_values(array_unique($allTags)); // remove repeated values in the array
                $data['user'] = $check_user_exists;

                $response_array = array(
                    'code' => 200,
                    'error_code' => '',
                    'data' => $data,
                    'status' => 'success',
                    'statusMsg' => 'User logged in successfully',
                    'error_msg' => '',
                    'debug' => "TRUE"
                );
                return $this->returnResponse($response_array);
            }
            else
            {
                $response_array = array(
                    'code' => 200,
                    'error_code' => '',
                    'data' => '',
                    'status' => 'fail',
                    'statusMsg' => 'Incorrect username or password',
                    'error_msg' => '',
                    'debug' => "TRUE"
                );
                return $this->returnResponse($response_array);
            }
            
            /* added ends here*/
        }
    }

    public function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
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
            $insertArr = [
                'name' => strtolower($request->input('name')),
                'email' => strtolower($request->input('email')),
                'password' => MD5($request->input('password')),
                'created_on' => date('Y-m-d H:i:s'),
                'is_deleted' => 0,
            ];

            $user_datails = DB::table('users')->insertGetId($insertArr);
            $final_user_details = DB::table('users')->where('id',$user_datails)->first();

            if(NULL != $user_datails)
            {
                $response_array = array(
                    'code' => 200,
                    'error_code' => '',
                    'data' => $final_user_details,
                    'status' => 'success',
                    'statusMsg' => 'User registred successfully',
                    'error_msg' => '',
                    'debug' => "TRUE"
                );
                return $this->returnResponse($response_array);
            }
            else
            {
                $response_array = array(
                    'code' => 200,
                    'error_code' => '',
                    'data' => '',
                    'status' => 'fail',
                    'statusMsg' => 'User registration process failed',
                    'error_msg' => 'Details not inserted.',
                    'debug' => "TRUE"
                );
                return $this->returnResponse($response_array);
            }
        }
    }

    public function updateInfo(Request $request)
    {
        $expoInsertArr = [];
        $expo_ids = [];
        foreach ($request->input('record') as $value) {
            if(NULL != $value['localExpoId'])
            {
                $checkifExpoExists = DB::table('expo_details')->select('id as expo_id', 'expo_local_id')->where('expo_local_id', $value['localExpoId'])->first();
                if(NULL != $checkifExpoExists)
                {
                    $expo_ids[] = $value['localExpoId'];
                }
                else
                {
                    $expoInsertArr[] = [
                        'expo_name' => $value['expoName'],
                        'expo_local_id' => $value['localExpoId'],
                        'customer_id' => $value['userId'],
                    ];
                    $expo_ids[] = $value['localExpoId'];
                }
            }
        }
        if(count($expoInsertArr) > 0)
        {
            $check_insert = DB::table('expo_details')->insert($expoInsertArr);
        }
        $data['expo_ids'] = DB::table('expo_details')->select('id as expo_id', 'expo_local_id', 'expo_name')->whereIn('expo_local_id', $expo_ids)->get();
        $companyInsertArr = [];
        $companyUpdateArr = [];
        if(NULL != $request->input('record'))
        {
            $i=0;
            foreach ($request->input('record') as $companyDetails)
            {
                if(NULL != $companyDetails['companies'])
                {
                    foreach($companyDetails['companies'] as $eachCompany)
                    {
                        $checkIfCompanyExists = DB::table('company_details')->where('company_local_id', $eachCompany['companyInternalId'])->first();
                        if(NULL != $checkIfCompanyExists)
                        {
                            $companyUpdateArr = [
                                'name' => $eachCompany['companyName'],
                                'expo_local_id' => $request->input('record.'.$i.'.localExpoId'),
                                //'company_local_id' => $companyDetails['companyInternalId'],
                                'note' => $eachCompany['note'],
                                'priority' => $eachCompany['priority'],
                                'company_tags' => json_encode($eachCompany['companyTags']),
                            ];
                            DB::table('company_details')->where('company_local_id', $eachCompany['companyInternalId'])->update($companyUpdateArr);
                        }
                        else
                        {
                            $companyInsertArr[] = [
                                'name' => $eachCompany['companyName'],
                                'expo_local_id' => $request->input('record.'.$i.'.localExpoId'),
                                'company_local_id' => $eachCompany['companyInternalId'],
                                'note' => $eachCompany['note'],
                                'priority' => $eachCompany['priority'],
                                'company_tags' => json_encode($eachCompany['companyTags']),
                            ];
                        }
                        $company_ids[] = $eachCompany['companyInternalId'];
                    }
                }
                $i++;
            }
            if(count($companyInsertArr) > 0)
            {
                $check_insert = DB::table('company_details')->insert($companyInsertArr);
            }
            $data['company_ids'] = DB::table('company_details')->select('id as company_id', 'company_local_id', 'expo_local_id as company_expo_id', 'name as companyName', 'company_tags', 'note', 'priority')->whereIn('company_local_id', $company_ids)->get();
            $final_arr = [];
            $checkIfExpoPushed = [];
            $checkIfCompanyPushed = [];

            foreach($data['expo_ids'] as $expo_list)
            {
                foreach ($data['company_ids'] as $eachNewCompany)
                {
                    if($eachNewCompany->company_expo_id == $expo_list->expo_local_id)
                    {
                        if(in_array($expo_list->expo_local_id, $checkIfExpoPushed))
                        {
                            $final_arr['records']['expo'][$expo_list->expo_local_id]['company'][] = array('company_name' => $eachNewCompany->companyName,
                                                    'company_local_id' => $eachNewCompany->company_local_id,
                                                    'expo_local_id' => $eachNewCompany->company_expo_id,
                                                    'note' => $eachNewCompany->note,
                                                    'priority' => $eachNewCompany->priority,
                                                    'tags' => json_decode($eachNewCompany->company_tags,TRUE));  

                        }
                        else
                        {
                            $final_arr['records']['expo'][$expo_list->expo_local_id] = ['expoName' => $expo_list->expo_name,
                                                             'localExpoId' => $expo_list->expo_local_id];
                            $final_arr['records']['expo'][$expo_list->expo_local_id]['company'][] = array('company_name' => $eachNewCompany->companyName,
                                                    'company_local_id' => $eachNewCompany->company_local_id,
                                                    'expo_local_id' => $eachNewCompany->company_expo_id,
                                                    'note' => $eachNewCompany->note,
                                                    'priority' => $eachNewCompany->priority,
                                                    'tags' => json_decode($eachNewCompany->company_tags,TRUE));
                            array_push($checkIfExpoPushed, $expo_list->expo_local_id);
                        }
                    }
                }
            }
            $response_array = array(
                'code' => 200,
                'error_code' => '',
                'data' => $final_arr,
                'status' => 'success',
                'statusMsg' => 'Data updated succesfully',
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
                'statusMsg' => 'Something went wrong.',
                'error_msg' => '',
                'debug' => "TRUE"
            );
        }
        
        return $this->returnResponse($response_array);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if($validator->fails())
        {
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
            $user_details = DB::table('users')->where('email',$request->input('email'))->first();
            if(NULL != $user_details)
            {
                $getRandomString = $this->generateRandomString();

                DB::table('users')->where('id', $user_details->id)->update(['password' => md5($getRandomString)]);

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: noreply@expoapplications.com\r\n";

                $message = "Hello,
As per your request to reset the password of Expo aaplication, we have generated new password.
New Password : ".$getRandomString."

Note: Kindly reset password after loggin in.

Thank you.";
                @mail($user_details->email,"Reset Password Request at Expo Application",$message,$headers);
                $mail = TRUE;
                if($mail)
                {
                    $response_array = array(
                        'code' => 200,
                        'error_code' => '',
                        'data' => '',
                        'status' => 'success',
                        'statusMsg' => 'New password is sent to your registered email address',
                        'error_msg' => '',
                        'debug' => "TRUE"
                    );
                    return $this->returnResponse($response_array);
                }
                else
                {
                    $response_array = array(
                        'code' => 200,
                        'error_code' => '',
                        'data' => '',
                        'status' => 'fail',
                        'statusMsg' => 'Something went wrong, please try resetting password after some time',
                        'error_msg' => 'Email was not sent',
                        'debug' => "TRUE"
                    );
                    return $this->returnResponse($response_array);
                }
            }
            else
            {
                $response_array = array(
                    'code' => 200,
                    'error_code' => '',
                    'data' => '',
                    'status' => 'fail',
                    'statusMsg' => 'Email does not exists.',
                    'error_msg' => 'Email was not found',
                    'debug' => "TRUE"
                );
                return $this->returnResponse($response_array);
            }
            //$mail = mail($to=$mail_arr['to'],$subject=$mail_arr['subject'],$message=$mail_arr['body'],$headers);

        }
    }

    protected function generateRandomString($length = 8) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) 
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function restoreData(Request $request)
    {
        $user_id = $request->input('userid');
        $restore_data = DB::select("SELECT
                                                ed.expo_name,
                                                ed.id as expo_table_id,
                                                ed.customer_id,
                                                ed.expo_local_id as localExpoId,
                                                cd.name as companyName,
                                                cd.company_local_id,
                                                cd.expo_local_id as company_expo_id,
                                                cd.note as company_note,
                                                cd.priority,
                                                cd.id as company_table_id,
                                                cd.company_tags,
                                                i.image_record_id,
                                                i.name as image_name,
                                                i.image_type,
                                                i.id as image_table_id,
                                                i.company_local_id as image_company_local_id
                                            FROM
                                                expo_details ed left join  
                                                company_details cd on ed.expo_local_id = cd.expo_local_id
                                                left join images i on (cd.company_local_id = i.company_local_id AND i.is_deleted = 0)
                                            WHERE
                                                ed.customer_id = '".$user_id."'");

        $expo = [];
        $company = [];
        $image = [];

        $expo_id = 0;
        $company_id = 0;
        $image_id = 0;
        $i=0;
        foreach($restore_data as $allData)
        {
            if($expo_id !=  $allData->localExpoId)
            {
                $expo['records']['expo'][$allData->localExpoId] =  array('localExpoId' => $allData->localExpoId,
                                'expo_name' =>  $allData->expo_name,
                                'id' => $allData->expo_table_id);
                $expo_id = $allData->localExpoId;

                if($company_id != $allData->company_local_id){
                    $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id] = array('company_name' => $allData->companyName,
                                                'company_local_id' => $allData->company_local_id,
                                                'expo_local_id' => $allData->company_expo_id,
                                                'note' => $allData->company_note,
                                                'priority' => $allData->priority,
                                                'company_table_id' => $allData->company_table_id,
                                                'tags' => json_decode($allData->company_tags,TRUE));

                    $company_id = $allData->company_local_id;
                }
                if($image_id != $allData->image_record_id){
                    if(NULL != $allData->image_record_id)
                    {
                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'][] = array('image_record_id' => $allData->image_record_id,
                                                    'image_name' => $allData->image_name,
                                                    'image_type' => $allData->image_type,
                                                    'image_table_id' => $allData->image_table_id,
                                                    'image_company_local_id' => $allData->image_company_local_id);                        
                    }
                    else
                    {
                       $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'] = []; 
                    }

                    $image_id = $allData->image_record_id;
                }
            }
            else
            {
                if($company_id ==  $allData->company_local_id)
                {
                    if(NULL != $allData->image_record_id)
                    {
                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'][] = array('image_record_id' => $allData->image_record_id,
                                                    'image_name' => $allData->image_name,
                                                    'image_type' => $allData->image_type,
                                                    'image_table_id' => $allData->image_table_id,
                                                    'image_company_local_id' => $allData->image_company_local_id);
                    }
                    else
                    {
                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'] = []; 
                    }
                }
                else
                {
                    $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id] = array('company_name' => $allData->companyName,
                                                'company_local_id' => $allData->company_local_id,
                                                'expo_local_id' => $allData->company_expo_id,
                                                'note' => $allData->company_note,
                                                'priority' => $allData->priority,
                                                'company_table_id' => $allData->company_table_id,
                                                'tags' => json_decode($allData->company_tags,TRUE));
                    $company_id = $allData->localExpoId;

                    if(NULL != $allData->image_record_id)
                    {
                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'][] = array('image_record_id' => $allData->image_record_id,
                                                    'image_name' => $allData->image_name,
                                                    'image_type' => $allData->image_type,
                                                    'image_table_id' => $allData->image_table_id,
                                                    'image_company_local_id' => $allData->image_company_local_id);
                    }
                    else
                    {
                        $expo['records']['expo'][$allData->localExpoId]['company'][$allData->company_local_id]['images'] = [];
                    }
                    $company_id = $allData->company_local_id;
                }
            }
            $i++;
        }

        if(NULL != $expo['records']['expo'])
        {
            $response_array = array(
                'code' => 200,
                'error_code' => '',
                'data' => $expo,
                'status' => 'success',
                'statusMsg' => 'data returned',
                'error_msg' => '',
                'debug' => "TRUE"
            );
        }
        else
        {
            $response_array = array(
                'code' => 200,
                'error_code' => '',
                'data' => array(),
                'status' => 'fail',
                'statusMsg' => 'No data found',
                'error_msg' => '',
                'debug' => "TRUE"
            );
            
        }
        return $this->returnResponse($response_array);
    }

    public function forgetPassword(Request $request)
    {
        $validator_array = [];
        
        $validator_array = [
            'email' => 'required|email'
        ];

        $validator = Validator::make($request->all(),$validator_array);

        if($validator->fails())
        {
            $response_array = array(
                'code' => 200,
                'error_code' => 422,
                'data' => '',
                'status' => 'fail',
                'statusMsg' => $validator->errors()->first(),
                'error_msg' => 'Validation failed',
                'debug' => "TRUE"
            );
        }
        else
        {
            $customer_details = DB::table('users')->where('email', mb_strtolower($request->input('email')))
                                                    ->first(['id','name', 'email', 'is_deleted']);

            if(NULL != $customer_details)
            {
                $slug = $this->generateRandomString();

                DB::table('users')->where('id',$customer_details->id)
                                    ->update(['password' => MD5($slug), 'updated_on' => date('Y-m-d H:i:s')]);

                $customer_name = "Customer";
                if(NULL != $customer_details->name)
                        $customer_name = $customer_details->name;

                $subject = "Reset Password for Expo Services";
                $to = $customer_details->email;
                $data = ['customer_name' => $customer_name,
                         'password' => $slug];

                Mail::send('reset_password', $data, function($message) use ($subject,$to,$customer_name)
                {
                    

                    $message->sender("noreply@xhtmlchop.com", "Expo Services");
                    //$message->to($to);
                    $message->to('dhawalraut13@gmail.com', $customer_name);
                    $message->replyTo("noreply@xhtmlchop.com", $name = null);
                    $message->subject($subject);
                    $message->priority($level = 1);

                    // Attach a file from a raw $data string...
                    //$message->attachData($data, $name, array $options = []);

                    // Get the underlying SwiftMailer message instance...
                    $print_message = $message->getSwiftMessage();
                });

/*              if(NULL != $customer_details->email && ($customer_details->email != ""))
                {
                    if(NULL != $customer_details->name)
                        $customer_name = $customer_details->name;

                    $headers  = "From: Expo Services < noreply@xhtmlchop.com >\n";
                    //$headers .= "Cc: testsite < mail@testsite.com >\n"; 
                    $headers .= "X-Sender: Expo Services < noreply@xhtmlchop.com >\n";
                    $headers .= 'X-Mailer: PHP/' . phpversion();
                    $headers .= "X-Priority: 1\n"; // Urgent message!
                    $headers .= "Return-Path: noreply@xhtmlchop.com\n"; // Return path for errors
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=iso-8859-1\n";

                    $subject = "Reset Password for Expo Services";

                    $body = "<p>Dear ".$customer_name.",</p><p> You have made a request to reset the password for Expo Services. <br/><br/> Your System Generated Password is : ".$slug."</p><p>Kindly change your password after loggin in.</p><p>Thank you</p><p>Expo Services</p>";

                    //mail('dhawalraut13@gmail.com', 'OTP for forgot password', $body, $headers);
                    if(mail($customer_details->email, $subject, $body, $headers))
                    {
                        $response_array = array(
                            'code' => 200,
                            'error_code' => '',
                            'data' => '{}',
                            'status' => 'success',
                            'statusMsg' => 'OTP sent on your email/phone.',
                            'error_msg' => '',
                            'debug' => "TRUE"
                        );
                        return $this->returnResponse($response_array);
                    }
                }*/
                

               /* if(!empty($result) && $result->status == 'success')
                {   
                    $response_array = array(
                        'code' => 200,
                        'error_code' => '',
                        'data' => '{}',
                        'status' => 'success',
                        'statusMsg' => 'OTP sent on your email/phone.',
                        'error_msg' => '',
                        'debug' => "TRUE"
                    );
                }
                else
                {
                    $response_array = array(
                        'code' => 200,
                        'error_code' => '',
                        'data' => '{}',
                        'status' => 'success',
                        'statusMsg' => 'OTP sent on your email/phone.',
                        'error_msg' => '',
                        'debug' => "TRUE"
                    );
                } */
            }
            else
            {
                $x = new ExpoController();
                $response_array = array(
                        'code' => 200,
                        'error_code' => 422,
                        'data' => $x,
                        'status' => 'fail',
                        'statusMsg' => 'Your email number is not verified with us. Please use correct registered email to reset the password',
                        'error_msg' => 'Email not registered',
                        'debug' => "TRUE"
                    );
            }
        }
        return $this->returnResponse($response_array);
    }
}
