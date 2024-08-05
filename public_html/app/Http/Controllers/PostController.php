<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Constants;
use App\Models\FollowingList;
use App\Models\GlobalFunction;
use App\Models\Like;
use App\Models\Post;
use App\Models\PostContent;
use App\Models\Report;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\SavedNotification;
use App\Models\Setting;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function addPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'content_type' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = User::where('id', $request->user_id)
            ->get()
            ->first();

        if ($user) {
            $post = new Post();
            $post->user_id = (int) $request->user_id;
            if ($request->has('desc')) {
                $post->desc = $request->desc;
            }
            $post->tags = $request->tags;
            $post->save();

            if ($request->hasFile('content')) {
                $files = $request->file('content');
                for ($i = 0; $i < count($files); $i++) {
                    $postContent = new PostContent();
                    $postContent->post_id = $post->id;
                    $path = GlobalFunction::saveFileAndGivePath($files[$i]);
                    $postContent->content = $path;
                    if ($request->hasFile('thumbnail')) {
                        $thumbnails = $request->file('thumbnail');
                        $path = GlobalFunction::saveFileAndGivePath($thumbnails[$i]);
                        $postContent->thumbnail = $path;
                    }
                    $postContent->content_type = $request->content_type;
                    $postContent->save();
                }
            }

            $post = Post::where('id', $post->id)->with('content')->first();

            return response()->json([
                'status' => true,
                'message' => 'Post Uploaded',
                'data' => $post,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ]);
        }
    }

    public function fetchPosts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'limit' => 'required',
            'should_send_suggested_room' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = User::where('is_block', 0)->where('id', $request->my_user_id)->first();

        if ($user) {

            $blockUserIds = explode(',', $user->block_user_ids);

            $fetchPosts = Post::with('content')
                                ->inRandomOrder()
                                ->orderBy('created_at', 'desc')
                                ->with('user')
                                ->whereRelation('user', 'is_block', 0)
                                ->whereNotIn('user_id', $blockUserIds)
                                ->limit($request->limit)
                                ->get();

            if (!$fetchPosts->isEmpty()) {

                foreach ($fetchPosts as $fetchPost) {
                    $isPostLike = Like::where('user_id', $request->my_user_id)->where('post_id', $fetchPost->id)->first();
                    if ($isPostLike) {
                        $fetchPost->is_like = 1;
                    } else {
                        $fetchPost->is_like = 0;
                    }

                    $blockUserIds = User::where('is_block', 1)->pluck('id');

                    $comments_count = Comment::whereNotIn('user_id', $blockUserIds)->where('post_id', $fetchPost->id)->count();
                    $likes_count = Like::whereNotIn('user_id', $blockUserIds)->where('post_id', $fetchPost->id)->count();

                    $fetchPost->comments_count = $comments_count;
                    $fetchPost->likes_count = $likes_count;

                }

                if ($request->should_send_suggested_room == 1) {

                    $interest_ids = explode(',', $user->interest_ids);
                    $myRoomIds = RoomUser::where('user_id', $request->my_user_id)->pluck('room_id');
                    $blockUserIds = User::where('is_block', 1)->pluck('id');


                    if ($user->interest_ids == null) {

                        $suggestedRooms = Room::where('admin_id', '!=', $request->my_user_id)
                                    ->inRandomOrder()
                                    ->where('is_private', 0)
                                    ->whereNotIn('id', $myRoomIds)
                                    ->whereNotIn('admin_id', $blockUserIds)
                                    ->limit(2)
                                    ->get();

                        $setting = Setting::first();

                        return response()->json([
                            'status' => true,
                            'message' => 'Suggested random room',
                            'data' => $suggestedRooms,
                        ]);
                    } else {

                        $setting = Setting::first();

                        shuffle($interest_ids);

                            $suggestedRooms = [];

                            foreach ($interest_ids as $interest_id) {

                                $rooms = Room::where('admin_id', '!=', $request->my_user_id)
                                        ->whereRaw('find_in_set("' . $interest_id . '", interest_ids)')
                                        ->inRandomOrder()
                                        ->where('is_private', 0)
                                        ->whereNotIn('id', $myRoomIds)
                                        ->whereNotIn('admin_id', $blockUserIds)
                                        ->limit(2)
                                        ->get();

                                foreach ($rooms as $room) {
                                    if ($room->total_member < $setting->setRoomUsersLimit) {
                                        if(!in_array($room, $suggestedRooms) && (count($suggestedRooms) != 2)) {
                                        array_push($suggestedRooms, $room);
                                    }
                                }
                            }

                            if (count($suggestedRooms) <= 1) {
                                $rooms = Room::where('admin_id', '!=', $request->my_user_id)
                                        ->inRandomOrder()
                                        ->where('is_private', 0)
                                        ->whereNotIn('id', $myRoomIds)
                                        ->whereNotIn('admin_id', $blockUserIds)
                                        ->limit(2)
                                        ->get();

                                foreach ($rooms as $room) {
                                    if ($room->total_member < $setting->setRoomUsersLimit) {
                                       if(!in_array($room, $suggestedRooms) && (count($suggestedRooms) != 2)) {
                                        array_push($suggestedRooms, $room);
                                        }
                                    }
                                }
                            }


                        }
                    }


                    foreach ($suggestedRooms as $suggestedRoom) {
                        $roomUser = RoomUser::where('user_id', $request->my_user_id)->where('room_id', $suggestedRoom->id)->first();
                        if ($roomUser) {
                            $suggestedRoom->userRoomStatus = $roomUser->type;
                        } else {
                            $suggestedRoom->userRoomStatus = 0;
                        }
                    }

                } else {
                    $suggestedRooms = [];
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Fetch posts',
                    'data' => $fetchPosts,
                    'suggestedRooms' => $suggestedRooms,

                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Posts not Available',
                ]);
            }

        }
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ]);
    }

    public function addComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'post_id' => 'required',
            'desc' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = User::where('is_block', 0)->where('id', $request->user_id)->first();


        if ($user) {
            $post = Post::where('id', $request->post_id)->with(['user','content'])->first();


            if ($post) {
                $comment = new Comment();
                $comment->user_id = (int) $request->user_id;
                $comment->post_id = (int) $request->post_id;
                $comment->desc = $request->desc;
                $comment->save();

                $post->comments_count += 1;
                $post->save();

                $toUser = $post->user;

                if ($toUser->id != $request->user_id) {
                    if($toUser->is_push_notifications == Constants::pushNotification) {
                        $notificationDesc = $user->full_name . ' has commented: ' . $request->desc;
                        GlobalFunction::sendPushNotificationToUser($notificationDesc, $toUser->device_token, $toUser->device_type);
                    }
                }

                $comment->post = $post;

                if ($user->id != $post->user_id) {
                    $type = Constants::notificationTypeComment;

                    $savedNotification = new SavedNotification();
                    $savedNotification->my_user_id = (int) $post->user->id;
                    $savedNotification->user_id = (int) $request->user_id;
                    $savedNotification->post_id = (int) $request->post_id;
                    $savedNotification->type = $type;
                    $savedNotification->save();
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Comment Placed',
                    'data' => $comment
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Post Not Found',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ]);
        }
    }

    public function fetchComments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $fetchComment = Comment::where('post_id', $request->post_id)
                                ->with('user')
                                ->whereRelation('user', 'is_block', 0)
                                ->orderBy('id', 'DESC')
                                ->offset($request->start)
                                ->limit($request->limit)
                                ->get();
        return response()->json([
            'status' => true,
            'message' => 'Fetch Comments',
            'data' => $fetchComment,
        ]);
    }

    public function deleteComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'comment_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $comment = Comment::where('id', $request->comment_id)->where('user_id', $request->user_id)->first();

        if($comment) {

            $commentCount = Post::where('id', $comment->post_id)->first();
            $commentCount->comments_count -= 1;
            $commentCount->save();

            $deleteCommentFromSavedNotification = SavedNotification::where('user_id', $request->user_id)
                                                                    ->where('post_id', $comment->post_id)
                                                                    ->where('type', Constants::notificationTypeComment)
                                                                    ->get();
            $deleteCommentFromSavedNotification->each->delete();
            


            $comment->delete();


            return response()->json([
                'status' => true,
                'message' => 'Delete comment successfully',
                'data' => $comment
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Comment not found'
        ]);

    }

    public function likePost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = User::where('is_block', 0)
                    ->where('id', $request->user_id)
                    ->first();
        if ($user) {
            $post = Post::where('id', $request->post_id)->with(['user', 'content'])->first();
            if ($post) {
                $likeRecord = Like::where('user_id', $request->user_id)->where('post_id', $request->post_id)->first();
                if ($likeRecord) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Already Liked',
                    ]);
                } else {
                $like = new Like();
                $like->user_id = (int) $request->user_id;
                $like->post_id = (int) $request->post_id;
                $like->save();

                $post->likes_count += 1;
                $post->save();

                $postUser = $post->user;

                    if ($postUser->id != $request->user_id) {
                        if($postUser->is_push_notifications == 1) {
                            $notificationDesc = $user->full_name . ' has liked your post.';
                            GlobalFunction::sendPushNotificationToUser($notificationDesc, $postUser->device_token, $postUser->device_type);
                        }
                    }

                    if ($user->id != $post->user_id) {
                        $type = Constants::notificationTypeLike;

                        $savedNotification = new SavedNotification();
                        $savedNotification->my_user_id = (int) $post->user->id;
                        $savedNotification->user_id = (int) $request->user_id;
                        $savedNotification->post_id = (int) $request->post_id;
                        $savedNotification->type = $type;
                        $savedNotification->save();

                    }

                    $like->post = $post;

                    return response()->json([
                        'status' => true,
                        'message' => 'Post Liked',
                        'data' => $like,
                    ]);
                }
            } 
            else {
                return response()->json([
                    'status' => false,
                    'message' => 'Post Not Found',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ]);
        }
    }

    public function dislikePost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $user = User::where('is_block', 0)
                    ->where('id', $request->user_id)
                    ->first();
        if ($user) {
            $likedPost = Like::where('user_id', $request->user_id)->where('post_id', $request->post_id)->first();
            if ($likedPost) {
                $likeCount = Post::where('id', $request->post_id)->first();
                $likeCount->likes_count -= 1;
                $likeCount->save();

                $likedPost->delete();
 
                $userNotification = SavedNotification::where('post_id', $request->post_id)
                                                    ->where('user_id', $request->user_id)
                                                    ->where('type', Constants::notificationTypeLike)
                                                    ->get();
                $userNotification->each->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'Post Dislike',
                    'data' => $likedPost,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Post Already Dislike',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ]);
        }
    }

    public function reportPostList(Request $request)
    {
        $reportType = 1;
        $totalData = Report::where('type', $reportType)->count();
        $rows = Report::where('type', $reportType)->orderBy('id', 'DESC')->get();

        $result = $rows;

        $columns = [
            0 => 'id',
            1 => 'post_id',
            2 => 'reason',
            3 => 'desc',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        if (empty($request->input('search.value'))) {
            $result = Report::where('type', $reportType)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $result = Report::where('type', $reportType)
                ->Where('reason', 'LIKE', "%{$search}%")
                ->orWhere('desc', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = Report::where('type', $reportType)
                ->Where('reason', 'LIKE', "%{$search}%")
                ->orWhere('desc', 'LIKE', "%{$search}%")
                ->count();
        }
        $data = [];
        foreach ($result as $item) {

            $post = Post::where('id', $item->post_id)->first();
            
            $postContent = PostContent::where('post_id', $item->post_id)->get();
            $contentType = $postContent->count() == 0 ? 2 : $postContent->first()->content_type;
            $firstContent = $postContent->pluck('content');

            if ($item->desc == null) {
                $item->desc = 'Note: Post has no description';
            }

            if ($contentType == 0) {
                $viewPost = '<button type="button" class="btn btn-primary viewPost commonViewBtn" data-bs-toggle="modal" data-image=' . $firstContent . ' data-desc="' . $post->desc . '" rel="' . $item->id . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg> View Post</button>';
            } else if ($contentType == 1) {
                $viewPost = '<button type="button" class="btn btn-primary viewVideoPost commonViewBtn" data-bs-toggle="modal" data-image=' . $firstContent . ' data-desc="' . $post->desc . '" rel="' . $item->id . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-video"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg> View Post</button>';
            } else if ($contentType == 2)  {
                $viewPost = '<button type="button" class="btn btn-primary viewDescPost commonViewBtn" data-bs-toggle="modal" data-desc="' . $post->desc . '" rel="' . $item->id . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-type"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg> View Post</button>';
            }
            

            $rejectReport = '<a href="#" class="me-3 btn btn-orange px-4 text-white rejectReport d-flex align-items-center" rel=' . $item->id . ' data-tooltip="Reject Report" >' . __(' <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg> <span class="ms-2"> Reject </span>') . '</a>';
            $delete = '<a href="#" class="btn btn-danger px-4 text-white delete deletePost d-flex align-items-center" rel=' . $item->id . ' data-tooltip="Delete Post">' . __('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg> ') . '</a>';
            $action = '<span class="float-right d-flex">' . $rejectReport . $delete . ' </span>';

            $data[] = [
                $viewPost,
                $item->reason,
                $item->desc,
                $action
            ];
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

    public function reportPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'reason' => 'required',
            'desc' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }
        $post = Post::where('id', $request->post_id)
            ->get()
            ->first();

        if ($post != null) {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required',
                'reason' => 'required',
                'desc' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors()->all();
                $msg = $messages[0];
                return response()->json(['status' => false, 'message' => $msg]);
            }

            $reportType = 1;

            $report = new Report();
            $report->type = $reportType;
            $report->post_id = $request->post_id;
            $report->reason = $request->reason;
            $report->desc = $request->desc;
            $report->save();

            return response()->json([
                'status' => true,
                'message' => 'Report Added Successfully',
                'data' => $report,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Post Not Found',
            ]);
        }
    }

    public function deleteMyPost(Request $request)
    {
        $post = Post::where('id', $request->post_id)->where('user_id', $request->user_id)->first();
        $user = User::where('id', $request->user_id)->first();
        if ($user == null) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ]);
        }
        if ($post && $user) {
            $postComments = Comment::where('post_id', $request->post_id)->get();
            $postComments->each->delete();

            $postLikes = Like::where('post_id', $request->post_id)->get();
            $postLikes->each->delete();

            $postContents = PostContent::where('post_id', $request->post_id)->get();
            foreach ($postContents as $postContent) {
                GlobalFunction::deleteFile($postContent->content);
                GlobalFunction::deleteFile($postContent->thumb);
            }
            $postContents->each->delete();

            $userNotification = SavedNotification::where('post_id', $request->post_id)->get();
            $userNotification->each->delete();


            $post->delete();
            return response()->json([
                'status' => true,
                'message' => 'Post Delete Successfully',
                'data' => $post,
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Post Not Found',
        ]);
    }

    public function createStory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'type' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $story = new Story();
        $story->user_id = (int) $request->user_id;
        $story->duration = (Double) $request->duration;
        $story->type = (int) $request->type;
        if ($request->hasFile('content')) {
            $files = $request->file('content');
            $path = GlobalFunction::saveFileAndGivePath($files);
            $story->content = $path;
        }
        $story->save();

        return response()->json([
            'status' => true,
            'message' => 'Story Added Successfully',
            'data' => $story,
        ]);
    }

    public function viewStory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'story_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = User::where('is_block', 0)
                    ->where('id', $request->user_id)
                    ->first();
        if ($user) {

            $viewStory = Story::where('id', $request->story_id)->first();

            $viewStoryByUserIds = explode(',', $viewStory->view_by_user_ids);

            if ($viewStory) {

                if(in_array($request->user_id, $viewStoryByUserIds)) {

                    return response()->json([
                        'status' => true,
                        'message' => 'Story Viewed',
                        'data' => $viewStory,
                    ]);

                } else {

                    $viewStory->view_by_user_ids = $viewStory->view_by_user_ids . $request->user_id . ',';
                    $viewStory->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'Story Viewed',
                        'data' => $viewStory,
                    ]);
                }

            }
            return response()->json([
                'status' => false,
                'message' => 'Story not found',
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ]);

    }


    public function fetchStory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = User::where('id', $request->my_user_id)->first();


        if ($user) {

            $followingUsers = FollowingList::where('my_user_id', $request->my_user_id)->whereRelation('story', 'created_at', '>=', now()->subDay()->toDateTimeString()
            )->with('user')->whereRelation('user', 'is_block', 0)->get()->pluck('user');

            foreach ($followingUsers as $followingUser) {
                $story = Story::where('user_id', $followingUser->id)->where('created_at', '>=', now()->subDay()->toDateTimeString())->get();
                $followingUser->story = $story;
            }

            return response()->json([
                'status' => true,
                'message' => 'Story fetch Successfully',
                'data' => $followingUsers,
            ]);

        }

    }

    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uploadFile' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

         if ($request->hasFile('uploadFile')) {
                $file = $request->file('uploadFile');

                    $path = GlobalFunction::saveFileAndGivePath($file);

                    return response()->json([
                        'status' => true,
                        'message' => "Uploaded file path",
                        'data' => $path,
                    ]);

            }

    }

    // Web
    public function deletePostReport(Request $request)
    {
        $reports = Report::where('id', $request->report_id)->first();
        $deletePostReports = Report::where('post_id', $reports->post_id)->get();

        if ($deletePostReports) {
            $deletePostReports->each->delete();

            return response()->json([
                'status' => true,
                'message' => 'Report Delete Successfully',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Report Not Found',
            ]);
        }
    }

    public function deletePost(Request $request)
    {

        $report = Report::where('id', $request->report_id)->first();

        if ($report) {
            $postContents = PostContent::where('post_id', $report->post_id)->get();
            foreach ($postContents as $postContent) {
                GlobalFunction::deleteFile($postContent->content);
                GlobalFunction::deleteFile($postContent->thumb);
            }
            $postContents->each->delete();

            $post = Post::where('id', $report->post_id)->first();
            $post->delete();

            $postComments = Comment::where('post_id', $request->post_id)->get();
            $postComments->each->delete();

            $postLikes = Like::where('post_id', $request->post_id)->get();
            $postLikes->each->delete();

            $deleteReportRecords = Report::where('post_id', $report->post_id)->get();
            $deleteReportRecords->each->delete();

            $userNotification = SavedNotification::where('post_id', $request->post_id)->get();
            $userNotification->each->delete();

            $report->delete();

            return response()->json([
                'status' => true,
                'message' => 'Post Delete Successfully',
                'data' => $postContents,
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Report Not Found',
        ]);
    }

    public function fetchPostsByHashtag(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'tag' => 'required',
            'start' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = User::where('is_block', 0)
                    ->where('id', $request->user_id)
                    ->first();

        if ($user) {
            $blockUserIds = explode(',', $user->block_user_ids);

            $hashtag = Post::whereRelation('user', 'is_block', 0)
                ->whereRaw('find_in_set("' . $request->tag . '", tags)')
                ->whereNotIn('user_id', $blockUserIds)
                ->with(['content','user'])
                ->orderBy('id', 'DESC')
                ->offset($request->start)
                ->limit($request->limit)
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Fetch posts by hashtag successfully',
                'data' => $hashtag,
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ]);

    }

    public function fetchPostByPostId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'post_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $user = User::where('is_block', 0)
                    ->where('id', $request->my_user_id)
                    ->first();

        if ($user) {

            $blockUserIds = explode(',', $user->block_user_ids);

            $fetchPost = Post::where('id', $request->post_id)->with(['content','user'])
                ->whereRelation('user', 'is_block', 0)
                ->whereNotIn('user_id', $blockUserIds)
                ->first();

            if ($fetchPost) {
                $isPostLike = Like::where('user_id', $request->my_user_id)->where('post_id', $fetchPost->id)->first();
                if ($isPostLike) {
                    $fetchPost->is_like = 1;
                } else {
                    $fetchPost->is_like = 0;
                }


                return response()->json([
                    'status' => true,
                    'message' => 'Fetch posts',
                    'data' => $fetchPost,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Posts not Available',
                ]);
            }

        }
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ]);
    }

    public function viewPosts()
    {
        return view('viewPosts');
    }

    public function allPostsList(Request $request)
    {
        $totalData = Post::count();
        $rows = Post::orderBy('id', 'DESC')->get();

        $result = $rows;

        $columns = [
            0 => 'id',
            1 => 'Content',
            2 => 'Thumbnail',
            3 => 'Views',
            4 => 'likes',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;
        $searchValue = $request->input('search.value');

        $query = Post::query();

        if (!empty($searchValue)) {
            $query->whereHas('user', function ($q) use ($searchValue) {
                $q->where('full_name', 'LIKE', "%{$searchValue}%")
                ->orWhere('username', 'LIKE', "%{$searchValue}%");
            });
            $totalFiltered = $query->count(); // Count the total filtered posts
        }

        $result = $query->with('user') // Eager load the user relationship to avoid N+1 queries
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        foreach ($result as $item) {


            $postContent = PostContent::where('post_id', $item->id)->get();
            $contentType = $postContent->count() == 0 ? 2 : $postContent->first()->content_type;
            $firstContent = $postContent->pluck('content');

            if ($item->desc == null) {
                $item->desc = 'Note: Post has no description';
            }

            if ($contentType == 0) {
                $viewPost = '<button type="button" class="btn btn-primary viewPost commonViewBtn" data-bs-toggle="modal" data-image=' . $firstContent . ' data-desc="' . $item->desc . '" rel="' . $item->id . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg> View Post</button>';
            } else if ($contentType == 1) {
                $viewPost = '<button type="button" class="btn btn-primary viewVideoPost commonViewBtn" data-bs-toggle="modal" data-image=' . $firstContent . ' data-desc="' . $item->desc . '" rel="' . $item->id . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-video"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg> View Post</button>';
            } else if ($contentType == 2)  {
                $viewPost = '<button type="button" class="btn btn-primary viewDescPost commonViewBtn" data-bs-toggle="modal" data-desc="' . $item->desc . '" rel="' . $item->id . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-type"><polyline points="4 7 4 4 20 4 20 7"></polyline><line x1="9" y1="20" x2="15" y2="20"></line><line x1="12" y1="4" x2="12" y2="20"></line></svg> View Post</button>';
            }

            $userName = '<a href="./usersDetail/'.$item->user->id.'"> '. $item->user->username .' </a>';

            $delete = '<a href="#" class="btn btn-danger px-4 text-white delete deletePost d-flex align-items-center" rel=' . $item->id . ' data-tooltip="Delete Post">' . __('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg> ') . '</a>';
            $action = '<span class="float-right d-flex">' . $delete . ' </span>';

            $data[] = [
                $viewPost,
                $userName,
                $item->user->full_name,
                $item->comments_count,
                $item->likes_count,
                $item->created_at->format('d-m-Y'),
                $action
            ];
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

    public function deleteStory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'my_user_id' => 'required',
            'story_id' => 'required',
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors()->all();
            $msg = $messages[0];
            return response()->json(['status' => false, 'message' => $msg]);
        }

        $story = Story::where('id', $request->story_id)->where('user_id', $request->my_user_id)->first();

        if($story) {

            GlobalFunction::deleteFile($story->content);
            $story->delete();

            return response()->json([
                'status' => true,
                'message' => 'Story delete successfully',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Story not found'
        ]);
    }

    // CronJob start
    public function deleteStoryFromWeb()
    {
        $stories = Story::where('created_at', '<=', now()->subDay()->toDateTimeString())->get();

        if($stories) {
            foreach ($stories as $story) {
                GlobalFunction::deleteFile($story->content);
                $story->delete();
            }
        }
    }
    // CronJob End

    public function userStoryList(Request $request)
    {
    
        $twentyFourHoursAgo = Carbon::now()->subDay();

        $totalData = Story::where('created_at', '>=', $twentyFourHoursAgo)
                        ->where('created_at', '<=', Carbon::now())
                        ->where('user_id', $request->user_id)
                        ->count();

        $rows = Story::where('created_at', '>=', $twentyFourHoursAgo)
                    ->where('created_at', '<=', Carbon::now())
                    ->where('user_id', $request->user_id)
                    ->orderBy('id', 'DESC')
                    ->get();

        $result = $rows;

        $columns = [
            0 => 'id'
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $result = Story::where('created_at', '>=', $twentyFourHoursAgo)
                ->where('created_at', '<=', Carbon::now())
                ->where('user_id', $request->user_id)
                ->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                        // Add more conditions for searching other user fields if needed
                    });
                    // Add more conditions for searching other story fields if needed
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = $result->count(); // Count filtered result
        } else {
            $result = Story::where('created_at', '>=', $twentyFourHoursAgo)
                ->where('created_at', '<=', Carbon::now())
                ->where('user_id', $request->user_id)
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        }

        $data = [];


        foreach ($result as $item) {
            $contentType = $item->type;
            $contentURL = GlobalFunction::createMediaUrl($item->content);
            
            $timeAgo = Carbon::parse($item->created_at)->diffForHumans();
            
            $viewStory = ($contentType == 0) ? '<button type="button" class="btn btn-primary viewStory commonViewBtn" data-bs-toggle="modal" data-image="' . $contentURL . '" rel="' . $item->id . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg> View Story</button>'
                : '<button type="button" class="btn btn-primary viewStoryVideo commonViewBtn" data-bs-toggle="modal" data-image="' . $contentURL . '" rel="' . $item->id . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-video"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg> View Story</button>';
            
            $delete = '<a href="#" class="btn btn-danger px-4 text-white delete deleteStory d-flex align-items-center" rel="' . $item->id . '" data-tooltip="Delete Story">' . __('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg> ') . '</a>';
            $action = '<span class="float-right d-flex">' . $delete . ' </span>';

            $data[] = [
                $viewStory,
                $timeAgo,
                $action
            ];
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

    public function viewStories()
    {
        return view('viewStories');
    }

    public function deleteStoryFromAdmin(Request $request)
    {
        $story = Story::where('id', $request->story_id)->first();

        if($story) {

            GlobalFunction::deleteFile($story->content);
            $story->delete();

            return response()->json([
                'status' => true,
                'message' => 'Story delete successfully',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Story not found'
        ]);
    }

    
           
    public function allStoriesList(Request $request)
    {
        
        $twentyFourHoursAgo = Carbon::now()->subDay();

        $totalData = Story::where('created_at', '>=', $twentyFourHoursAgo)
                        ->where('created_at', '<=', Carbon::now())
                        ->count();

        $rows = Story::where('created_at', '>=', $twentyFourHoursAgo)
                    ->where('created_at', '<=', Carbon::now())
                    ->orderBy('id', 'DESC')
                    ->get();

        $result = $rows;

        $columns = [
            0 => 'id'
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $totalFiltered = $totalData;

        $searchValue = $request->input('search.value');

        $query = Story::where('created_at', '>=', $twentyFourHoursAgo)
                        ->where('created_at', '<=', Carbon::now());

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->whereHas('user', function ($q) use ($searchValue) {
                    $q->where('full_name', 'LIKE', "%{$searchValue}%");
                });
            });
        }

        $result = $query->with('user') // Eager load the user relationship if needed
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        ->get();

        if (!empty($searchValue)) {
            $totalFiltered = $result->count();
        }

        $data = [];


        foreach ($result as $item) {
            $userName = '<a href="usersDetail/'. $item->user_id .'">'.  $item->user->full_name .'</a>';
            $contentType = $item->type;
            $contentURL = GlobalFunction::createMediaUrl($item->content);
            
            $timeAgo = Carbon::parse($item->created_at)->diffForHumans();
            
            $viewStory = ($contentType == 0) ? '<button type="button" class="btn btn-primary viewStory commonViewBtn" data-bs-toggle="modal" data-image="' . $contentURL . '" rel="' . $item->id . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg> View Story</button>'
                : '<button type="button" class="btn btn-primary viewStoryVideo commonViewBtn" data-bs-toggle="modal" data-image="' . $contentURL . '" rel="' . $item->id . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-video"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg> View Story</button>';
            
            $delete = '<a href="#" class="btn btn-danger px-4 text-white delete deleteStory d-flex align-items-center" rel="' . $item->id . '" data-tooltip="Delete Story">' . __('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg> ') . '</a>';
            $action = '<span class="float-right d-flex">' . $delete . ' </span>';

            $data[] = [
                $viewStory,
                $userName,
                $timeAgo,
                $action
            ];
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



    public function test(Request $request)
    {
        $roomUser = RoomUser::where('room_id', $request->room_id)
            ->where(function($query){
                $query->where('type', 2)
                ->orWhere('type', 3);
            })->count();
        return response()->json([
            'status' => true,
            'message' => 'Room User',
            'data' => $roomUser,
        ]);
    }
}
