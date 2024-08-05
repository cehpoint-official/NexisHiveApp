@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/user.js') }}"></script>
    <script src="{{ asset('asset/script/env.js') }}"></script>
@endsection

@section('content')
    <section class="section">

        <div class="card" >
            <div class="card-header" id="reloadContent">
                <div class="page-title w-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0 fw-normal d-flex align-items-center">
                            {{ $user->full_name }}
                            @if ($user->is_verified == 2)
                                <img src="{{ asset('asset/image/verified.svg') }}" alt="verified" class="verified-badge">
                            @endif
                        </h4>
                        <div class="card-header-right d-flex align-items-center">
                            @if ($user->is_verified != 2)
                                <div class="verify-badge">
                                    <a href="#" class="btn btn-primary px-4 text-white verifyUser" rel="{{ $user->id }}" data-tooltip="Verify user">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        <span class="ms-2"> {{__('verifyUser')}} </span>
                                    </a>
                                </div>
                            @endif

                            @if ($user->is_block == 0)
                                <div class="User-badge">
                                    <a href="#" class="ms-3 btn btn-danger px-4 text-white blockUserBtn" rel="{{ $user->id }}" data-tooltip="Block user">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg>
                                        <span class="ms-2"> {{__('blockUser')}}  </span>
                                    </a>
                                </div>
                            @else
                                <div class="User-badge">
                                    <a href="#" class="ms-3 btn btn-primary px-4 text-white unblockUserBtn" rel="{{ $user->id }}" data-tooltip="Block user">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                                        <span class="ms-2">{{__('unblockUser')}}  </span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="editProfileForm" method="POST">
                    <input type="hidden" id="user_id" value="{{ $user->id }}">
                    <div class="profileDetailCard row">
                        <div class="col-lg-4">
                            <div class="profileDetailImages">
                                <div class="form-group w-100"> 
                                    <div class="avatar-upload">
                                        <div class="avatar-edit">
                                            <input type='file' name="background_image" id="imageUpload" accept=".png, .jpg, .jpeg" />
                                            <label for="imageUpload" class="btn btn-success">
                                                {{__('edit')}} 
                                            </label>
                                        </div>
                                        <div class="avatar-preview">
                                            @if ($user->background_image != null)
                                            <div id="imagePreview" style="background-image: url('../storage/{{ $user->background_image }}')"></div>
                                            @else
                                            <div id="imagePreview" style="background-image: url(../asset/image/default.png)"></div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="profilePicture">
                                        <div class="profilePictureMain">
                                            <div class="profile-edit">
                                                <input type='file' name="profile" id="profileImageUpload" accept=".png, .jpg, .jpeg" />
                                                <label for="profileImageUpload" class="btn btn-success" style="padding: 4px 20px;">
                                                    {{__('edit')}} 
                                                </label>
                                            </div>
                                            <div class="profile-preview">
                                                @if ($user->profile != null)
                                                <div id="imagePreviewProfile" style="background-image: url('../storage/{{ $user->profile }}')"></div>
                                                @else
                                                <div id="imagePreviewProfile" style="background-image: url({{ asset('public/asset/image/default.png') }})"></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="container-fluid p-0" id="userDetailReload">
                                <div class="row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label> {{ __('identity') }}</label>
                                            <input type="text" name="identity" class="form-control" readonly
                                                value="{{ $user->identity }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label> {{ __('username') }}</label>
                                            <input type="text" name="username" class="form-control" value="{{ $user->username }}" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label> {{ __('fullname') }}</label>
                                            <input type="text" name="full_name" class="form-control" value="{{ $user->full_name }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label> {{ __('bio') }}</label>
                                            <input type="text" name="bio" class="form-control" value="{{ $user->bio }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="align-items-end justify-content-between"> 
                                            <div class="otherDetails">
                                                <!-- <h5 class="fw-normal">
                                                    {{ __('Other details') }}
                                                </h5> -->
                                                <ul>
                                                    @if ($user->followers != null)
                                                        <li> {{ __('totalFollowers') }} : {{ $user->followers }} </li>
                                                    @else
                                                        <li>{{ __('totalFollowers') }} : 0 </li>
                                                    @endif

                                                    @if ($user->following != null)
                                                        <li>{{ __('totalFollowing') }} : {{ $user->following }} </li>
                                                    @else
                                                        <li>{{ __('totalFollowing') }} : 0 </li>
                                                    @endif

                                                    @if ($user->device_type == 0)
                                                        <li>{{ __('deviceType') }} : Android </li>
                                                    @else
                                                        <li>{{ __('deviceType') }} : iOS </li>
                                                    @endif
                                                </ul>
                                            </div>
                                           
                                        </div>
                                        <div class="w-auto">
                                            <div class="text-left">
                                                <button type="submit" class="btn btn-success">{{ __('saveChanges') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="my-3 card-tab">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item " role="presentation">
                    <button class="nav-link active" id="allUserPostsTab" data-bs-toggle="tab"
                        data-bs-target="#allUserPostsTab-pane" type="button" role="tab"
                        aria-controls="allUserPostsTab-panel" aria-selected="true"> {{ __('posts')}} </button>
                </li>
                <li class="nav-item " role="presentation">
                    <button class="nav-link" id="userStoryTab" data-bs-toggle="tab"
                        data-bs-target="#userStoryTab-pane" type="button" role="tab"
                        aria-controls="userStoryTab-panel" aria-selected="true"> {{ __('stories')}} </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="userRoomsOwnTab" data-bs-toggle="tab"
                    data-bs-target="#userRoomsOwnTab-pane" type="button" role="tab"
                    aria-controls="userRoomsOwnTab-pane" aria-selected="false">  {{ __('roomsOwn')}} </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link " id="userRoomInTab" data-bs-toggle="tab"
                        data-bs-target="#userRoomInTab-pane" type="button" role="tab"
                        aria-controls="userRoomInTab-panel" aria-selected="true">  {{ __('roomsIn')}} </button>
                </li>
            </ul>
        </div>

         <div class="tab-content" id="myTabContent">
            <div class="tab-pane show active" id="allUserPostsTab-pane" role="tabpanel" tabindex="0">
                <div class="card">
                    <div class="card-header">
                        <div class="page-title w-100">
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0 fw-normal d-flex align-items-center"> {{ __('userPosts') }} </h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped w-100" id="userPostTable">
                            <input type="hidden" name="" id="userId" value="{{$user->id}}">
                            <thead>
                                <tr>
                                    <th style="width: 150px"> {{ __('content') }} </th>
                                    <th> {{ __('comments') }} </th>
                                    <th> {{ __('likes') }} </th>
                                    <th> {{ __('createdAt') }} </th>
                                    <th style="text-align: right; width: 200px;"> {{ __('action') }} </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
             <div class="tab-pane" id="userStoryTab-pane" role="tabpanel" tabindex="0">
               <div class="card">
                    <div class="card-header">
                        <div class="page-title w-100">
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0 fw-normal d-flex align-items-center"> {{ __('userStory') }} </h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped w-100" id="userStoryTable">
                            <thead>
                                <tr>
                                    <th width="100px"> {{ __('content')}} </th>
                                    <th width="100px"> {{ __('time')}} </th>
                                    <th width="250px" style="text-align: right"> {{ __('action')}} </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="userRoomsOwnTab-pane" role="tabpanel" tabindex="0">
               <div class="card">
                    <div class="card-header">
                        <div class="page-title w-100">
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0 fw-normal d-flex align-items-center"> {{ __('roomsOwn') }} </h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped w-100" id="userRoomsOwnTable">
                            <input type="hidden" name="" id="userId" value="{{$user->id}}">
                            <thead>
                                <tr>
                                    <th width="100px"> {{ __('roomImage')}} </th>
                                    <th> {{ __('title')}} </th> 
                                    <th> {{ __('totalMembers')}} </th>
                                    <th> {{ __('joinRequestEnable')}} </th>
                                    <th> {{ __('private')}} </th>
                                    <th width="250px" style="text-align: right"> {{ __('action')}} </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="userRoomInTab-pane" role="tabpanel" tabindex="0">
                <div class="card">
                    <div class="card-header">
                        <div class="page-title w-100">
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0 fw-normal d-flex align-items-center"> {{ __('roomsIn') }} </h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped w-100" id="userRoomInTable">
                            <input type="hidden" name="" id="userId" value="{{$user->id}}">
                            <thead>
                                <tr>
                                    <th style="width: 150px"> {{ __('roomImage') }} </th>
                                    <th> {{ __('roomName') }} </th> 
                                    <th> {{ __('myType') }} </th>
                                    <th style="text-align: right; width: 200px;"> {{ __('action') }} </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

      <!-- View Story Modal -->
     <div class="modal fade" id="viewStoryModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-normal" id="exampleModalLabel">{{ __('viewStory') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group position-relative m-0">
                        <label> {{ __('story') }}</label>
                        <img src="" alt="" srcset="" id="story_content" class="img-fluid story_content_view">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                </div>
            </div>
        </div>
    </div>

     <!-- View Story Modal -->
     <div class="modal fade" id="viewStoryVideoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-normal" id="exampleModalLabel">{{ __('viewStoryVideo') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group position-relative m-0">
                        <label class="form-label"> {{ __('story') }}</label>
                        <video controls id="story_content_video" class="img-fluid story_content_view">
                           
                        </video>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                </div>
            </div>
        </div>
    </div>

     <!-- View Post Modal -->
     <div class="modal fade" id="viewPostModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5 fw-normal" id="exampleModalLabel">{{ __('viewPost') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label> {{ __('postDescription') }}</label>
                        <p id="postDesc"> </p>
                    </div>
                    <hr>
                    <div class="form-group position-relative">
                        <label> {{ __('post') }}</label>
                        <div class="swiper-container mySwiper">
                            <div id="post_contents">
                            </div>
                        </div>
                        <div class="swiper-button swiper-button-prev"></div>
                        <div class="swiper-button swiper-button-next"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                </div>
            </div>
        </div>
    </div>

        <!-- View Post Desc Modal -->
        <div class="modal fade" id="viewPostDescModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5 fw-normal" id="exampleModalLabel">{{ __('viewPost') }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-0">
                            <label> {{ __('postDescription') }}</label>
                            <p class="m-0" id="postDesc1"> </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').css('background-image', 'url('+e.target.result +')');
            $('#imagePreview').hide();
            $('#imagePreview').fadeIn(650);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$("#imageUpload").change(function() {
    readURL(this);
});

function readURL1(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreviewProfile').css('background-image', 'url('+e.target.result +')');
            $('#imagePreviewProfile').hide();
            $('#imagePreviewProfile').fadeIn(650);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
$("#profileImageUpload").change(function() {
    readURL1(this);
});
           
        </script>
@endsection
