<?php

namespace App\Http\Controllers;

// use App\Classes\AgoraDynamicKey\RtcTokenBuilder;
use App\Models\AdminNotification;
use App\Models\DocumentType;
use App\Models\FAQs;
use App\Models\GlobalFunction;
use App\Models\GlobalSettings;
use App\Models\Interest;
use App\Models\Post;
use App\Models\ProfileVerification;
use App\Models\Report;
use App\Models\ReportReason;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Google\Client;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    public function settingView()
    {
        $setting = Setting::get()->first();

        return view('setting', [
            'setting' => $setting,  
        ]);
    }
    
    function saveSettings(Request $request)
    {
        $settings = GlobalSettings::first();
        $settings->version = $request->version;
        $settings->save();

        return GlobalFunction::sendSimpleResponse(true, 'Settings updated successfully');
    }

    function index()
    {
        $user = User::count();
        $interest = Interest::count();
        $report = Report::count();
        $verificationRequests = ProfileVerification::count();
        $adminNotification = AdminNotification::count();
        $posts = Post::count();
        $rooms = Room::count();
        $faqs = FAQs::count();
        return view('index', [
            'user' => $user,
            'interest' => $interest,
            'report' => $report,
            'verificationRequests' => $verificationRequests,
            'adminNotification' => $adminNotification,
            'posts' => $posts,
            'rooms' => $rooms,
            'faqs' => $faqs,
        ]);
    }

    public function fetchSetting()
    {
        $data = Setting::first();
        $interests = Interest::get();
        $documentType = DocumentType::get();
        $reportReasons = ReportReason::get();

        
        foreach ($interests as $fetchInterest) {

            $roomsCount = Room::whereRelation('user', 'is_block', 0)
                    ->whereRaw('find_in_set("' . $fetchInterest->id . '", interest_ids)')
                    ->where('is_private', 0)
                    ->where('total_member', '<>', (int) $data->setRoomUsersLimit)
                    ->count();
           
            // $interestRoomsCount = Room::whereIn('interest_ids', $fetchInterest->id)->count();
            $fetchInterest->totalRoomOfInterest = $roomsCount;
        }

        $data->interests = $interests;
        $data->documentType = $documentType;
        $data->reportReasons = $reportReasons;

        return response()->json([
            'status' => true,
            'message' => 'Fetch Setting',
            'data' => $data,
        ]);
    }

    public function documentTypeList(Request $request)
    {
        $totalData = DocumentType::count();
        $rows = DocumentType::orderBy('id', 'DESC')->get();

        $result = $rows;

        $columns = [
            0 => 'id',
            1 => 'title',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = DocumentType::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result = DocumentType::Where('title', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = DocumentType::Where('title', 'LIKE', "%{$search}%")->count();
        }
        $data = [];
        foreach ($result as $item) {
            $edit = '<a href="#" data-title="' . $item->title . '" class="me-3 btn btn-success px-4 text-white edit" rel=' . $item->id . ' >' . __('<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>') . '</a>';
            $delete = '<a href="#" class="btn btn-danger px-4 text-white delete" rel=' . $item->id . ' >' . __('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>') . '</a>';
            $action = '<span class="float-right">' . $edit . $delete . ' </span>';

            $data[] = [$item->title, $action];
        }
        $json_data = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];
        echo json_encode($json_data);
        exit();
    }

    public function addDocumentType(Request $request)
    {
        $documentTypeList = DocumentType::where('title', $request->title)
            ->get()
            ->first();

        if ($documentTypeList != null) {
            return response()->json([
                'status' => false,
                'message' => 'Document Record Dublicate',
            ]);
        } else {
            $documentType = new DocumentType();
            $documentType->title = $request->title;
            $documentType->save();

            return response()->json([
                'status' => true,
                'message' => 'Document Added Successfully',
                'data' => $documentType,
            ]);
        }
    }

    public function updateDocumentType(Request $request, $id) 
    {
        $documentType = DocumentType::where('title', $request->title)
            ->get()
            ->first();

        if ($documentType != null) {
            return response()->json([
                'status' => false,
                'message' => 'Document Record Dublicate',
            ]);
        } else {
            $documentType = DocumentType::find($id);
            if ($documentType) {
                $documentType->title = $request->title;
                $documentType->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Document Type Updated Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Document Type Not Found',
                ]);
            }
        }
    }

    public function deleteDocumentType($id)
    {
        $documentType = DocumentType::find($id);

        if ($documentType) {
            $documentType->delete();
            return response()->json([
                'status' => true,
                'message' => 'Document Type Delete Successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Document Type Not Found',
            ]);
        }
    }

    // Add Report Reason
    public function reportReasonList(Request $request)
    {
        $totalData = ReportReason::count();
        $rows = ReportReason::orderBy('id', 'DESC')->get();

        $result = $rows;

        $columns = [
            0 => 'id',
            1 => 'title',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = ReportReason::offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result = ReportReason::Where('title', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = ReportReason::Where('title', 'LIKE', "%{$search}%")->count();
        }
        $data = [];
        foreach ($result as $item) {
            $edit = '<a href="#" data-title="' . $item->title . '" class="me-3 btn btn-success px-4 text-white edit" rel=' . $item->id . ' >' . __('<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>') . '</a>';
            $delete = '<a href="#" class="btn btn-danger px-4 text-white delete" rel=' . $item->id . ' >' . __('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>') . '</a>';
            $action = '<span class="float-right">' . $edit . $delete . ' </span>';

            $data[] = [$item->title, $action];
        }
        $json_data = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];
        echo json_encode($json_data);
        exit();
    }

    public function addreportReason(Request $request)
    {
        $reportReason = ReportReason::where('title', $request->title)
            ->get()
            ->first();

        if ($reportReason != null) {
            return response()->json([
                'status' => false,
                'message' => 'Report Reason Dublicate',
            ]);
        } else {
            $reportReason = new ReportReason();
            $reportReason->title = $request->title;
            $reportReason->save();

            return response()->json([
                'status' => true,
                'message' => 'Reason Added Successfully',
                'data' => $reportReason,
            ]);
        }
    }

    public function updateReportReason(Request $request, $id) 
    {
        $reportReason = ReportReason::where('title', $request->title)->get()->first();

        if ($reportReason != null) {
            return response()->json([
                'status' => false,
                'message' => 'Reason Already Exist',
            ]);
        } else {
            $reportReason = ReportReason::find($id);
            if ($reportReason) {
                $reportReason->title = $request->title;
                $reportReason->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Report Reason Updated Successfully',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => ' Reason Not Found',
                ]);
            }
        }
    }

    public function deleteReportReasonType(Request $request)
    {
        $reportReason = ReportReason::where('id', $request->reportReason_id)->first();
        if ($reportReason) {
            $reportReason->delete();
            return response()->json([
                'status' => true,
                'message' => 'Report Reason Delete Successfully',
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Report Reason not found',
        ]);
    }

    public function updateSettings(Request $request)
    {
        $setting = Setting::first();

        if ($setting) {
            if ($request->has('app_name')) {
                $setting->app_name = $request->app_name;
                $request->session()->put('app_name', $setting['app_name']);
            }
            if ($request->has('setRoomUsersLimit')) {
                $setting->setRoomUsersLimit = $request->setRoomUsersLimit;
            }
            if ($request->has('minute_limit_in_creating_story')) {
                $setting->minute_limit_in_creating_story = $request->minute_limit_in_creating_story;
            }
            if ($request->has('minute_limit_in_choosing_video_for_story')) {
                $setting->minute_limit_in_choosing_video_for_story = $request->minute_limit_in_choosing_video_for_story;
            }
            if ($request->has('minute_limit_in_choosing_video_for_post')) {
                $setting->minute_limit_in_choosing_video_for_post = $request->minute_limit_in_choosing_video_for_post;
            }
            if ($request->has('max_images_can_be_uploaded_in_one_post')) {
                $setting->max_images_can_be_uploaded_in_one_post = $request->max_images_can_be_uploaded_in_one_post;
            }
            if ($request->has('ad_banner_android')) {
                $setting->ad_banner_android = $request->ad_banner_android;
            }
            if ($request->has('ad_interstitial_android')) {
                $setting->ad_interstitial_android = $request->ad_interstitial_android;
            }
            if ($request->has('ad_banner_iOS')) {
                $setting->ad_banner_iOS = $request->ad_banner_iOS;
            }
            if ($request->has('ad_interstitial_iOS')) {
                $setting->ad_interstitial_iOS = $request->ad_interstitial_iOS;
            }
            if ($request->has('is_admob_on')) {
                $setting->is_admob_on = $request->is_admob_on;
            }
            $setting->save();

            return response()->json([
                'status' => true,
                'message' => 'Setting Updated Successfully',
            ]);
        }    
    }


    function admob()
    {
        $setting = Setting::first();
        return view('admob', [
            'setting' => $setting,
        ]);    
    }


    public function test(Request $request)
    {
        $roomUser = RoomUser::where('room_id', $request->room_id)->count();
        return response()->json([
            'status' => true,
            'message' => 'test',
            'data' => $roomUser,

        ]);
    }

    function pushNotificationToSingleUser(Request $request)
    {
        $client = new Client();
        $client->setAuthConfig('googleCredentials.json');
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $accessToken = $client->getAccessToken();
        $accessToken = $accessToken['access_token'];

        // Log::info($accessToken);
        $contents = File::get(base_path('googleCredentials.json'));
        $json = json_decode(json: $contents, associative: true);

        $url = 'https://fcm.googleapis.com/v1/projects/' . $json['project_id'] . '/messages:send';
        // $notificationArray = array('title' => $title, 'body' => $message);

        // $device_token = $user->device_token;

        $fields = $request->json()->all();

        // $fields = array(
        //     'message'=> [
        //         'token'=> $device_token,
        //         'notification' => $notificationArray,
        //     ]
        // );

        $headers = array(
            'Content-Type:application/json',
            'Authorization:Bearer ' . $accessToken
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        // print_r(json_encode($fields));
        $result = curl_exec($ch);
        // Log::debug($result);

        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

        // return $response;
        return response()->json(['result' => $result, 'fields' => $fields]);
    }
}