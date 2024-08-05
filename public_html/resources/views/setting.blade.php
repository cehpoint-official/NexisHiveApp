@extends('include.app')
@section('header')
<script src="{{ asset('asset/script/setting.js') }}"></script>
@endsection
@section('content')
<div class="row same-height-card">
   <div class="col-lg-3 col-md-3 col-sm-12">
      <div class="card">
         <div class="card-header">
            <div class="page-title w-100">
               <div class="d-flex align-items-center justify-content-between">
                  <h4 class="mb-0 fw-normal">{{ __('changeAppName') }}</h4>
               </div>
            </div>
         </div>
         <div class="card-body">
            <form id="changeAppNameForm" method="POST">
               <div class="form-group">
                  <label for="appName" class="form-label">{{ __('changeAppName') }}</label>
                  <input type="text" class="form-control" name="app_name" id="appName" required=""
                     value="{{ $setting->app_name }}">
               </div>
               <div class="modal-footer p-0">
                  <button type="button" class="btn"></button>
                  <button type="submit" class="btn theme-btn text-white">{{ __('changeAppName') }}</button>
               </div>
            </form>
         </div>
      </div>
   </div>
   <div class="col-lg-3 col-md-3 col-sm-12">
      <div class="card">
         <div class="card-header">
            <div class="page-title w-100">
               <div class="d-flex align-items-center justify-content-between">
                  <h4 class="mb-0 fw-normal">{{ __('setRoomUsersLimit') }}</h4>
               </div>
            </div>
         </div>
         <div class="card-body">
            <form id="setRoomUsersLimit" method="POST">
               <div class="form-group">
                  <label for="setRoomUsersLimit" class="form-label">{{ __('setRoomUsersLimit') }}</label>
                  <input type="number" class="form-control" name="setRoomUsersLimit" id="setRoomUsersLimit" required=""
                     value="{{ $setting->setRoomUsersLimit }}">
               </div>
               <div class="modal-footer p-0">
                  <button type="button" class="btn"></button>
                  <button type="submit" class="btn theme-btn text-white">{{ __('setRoomUsersLimit') }}</button>
               </div>
            </form>
         </div>
      </div>
   </div>
   <div class="col-lg-6 col-md-6 col-sm-12">
      <div class="card">
         <div class="card-header">
            <div class="page-title w-100">
               <div class="d-flex align-items-center justify-content-between">
                  <h4 class="mb-0 fw-normal">{{ __('changePassword') }}</h4>
               </div>
            </div>
         </div>
         <div class="card-body">
            <form id="changePasswordForm" method="POST">
               <div class="row">
                  <div class="col-lg-6 col-md-6 col-sm-12">
                     <div class="form-group">
                        <label for="appName" class="form-label">{{ __('oldPassword') }}</label>
                        <input type="password" class="form-control" name="user_password" id="userPassword" required="">
                        <div class="password-icon">
                           <i data-feather="eye"></i>
                           <i data-feather="eye-off"></i>
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-12">
                     <div class="form-group">
                        <label for="appName" class="form-label">{{ __('newPassword') }}</label>
                        <input type="password" class="form-control" name="new_password" id="newPassword" required="">
                        <div class="password-icon">
                           <i data-feather="eye" class="eye1"></i>
                           <i data-feather="eye-off" class="eye-off1"></i>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer p-0">
                  <button type="button" class="btn"></button>
                  <button type="submit" class="btn theme-btn text-white">{{ __('changePassword') }}</button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
<div class="row same-height-card">
   <div class="col-lg-12 col-md-12 col-sm-12">
      <div class="card">
         <div class="card-header">
            <div class="page-title w-100">
               <div class="d-flex align-items-center justify-content-between">
                  <h4 class="mb-0 fw-normal">{{ __('limits') }}</h4>
               </div>
            </div>
         </div>
         <div class="card-body">
            <form id="minuteLimitForm" method="POST">
               <div class="row">
                  <div class="col-lg-6 col-md-6 col-sm-12">
                     <div class="form-group">
                        <label for="minuteLimitInStories" class="form-label">{{ __('minuteLimitInCreatingStory') }}</label>
                        <input type="text" class="form-control" name="minute_limit_in_creating_story" id="minuteLimitInStories" required="" value="{{ $setting->minute_limit_in_creating_story }}">
                     </div>
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-12">
                     <div class="form-group">
                        <label for="minuteLimitInChoosingVideoForStory" class="form-label">{{ __('minuteLimitInChoosingVideoForStory') }}</label>
                        <input type="text" class="form-control" name="minute_limit_in_choosing_video_for_story" id="minuteLimitInChoosingVideoForStory" required=""
                           value="{{ $setting->minute_limit_in_choosing_video_for_story }}">
                     </div>
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-12">
                     <div class="form-group">
                        <label for="minuteLimitInChoosingVideoForPost" class="form-label">{{ __('minuteLimitInChoosingVideoForPost') }}</label>
                        <input type="text" class="form-control" name="minute_limit_in_choosing_video_for_post" id="minuteLimitInChoosingVideoForPost" required=""
                           value="{{ $setting->minute_limit_in_choosing_video_for_post }}">
                     </div>
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-12">
                     <div class="form-group">
                        <label for="uploadMaxPerPost" class="form-label">{{ __('maxImagesCanBeUploadedInOnePost') }}</label>
                        <input type="text" class="form-control" name="max_images_can_be_uploaded_in_one_post" id="uploadMaxPerPost" required="" value="{{ $setting->max_images_can_be_uploaded_in_one_post }}">
                     </div>
                  </div>
               </div>
               <div class="modal-footer p-0">
                  <button type="button" class="btn"></button>
                  <button type="submit" class="btn theme-btn text-white">{{ __('save') }}</button>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
<div class="row same-height-card">
   <div class="col-lg-6 col-md-6 col-sm-12">
      <div class="card">
         <div class="card-header">
            <div class="page-title w-100">
               <div class="d-flex align-items-center justify-content-between">
                  <h4 class="mb-0 fw-normal">{{ __('documentType') }}</h4>
                  <button type="button" class="btn theme-bg theme-btn text-white" data-bs-toggle="modal"
                     data-bs-target="#documentTypeModal">
                  {{ __('addDocumentType') }}
                  </button>
               </div>
            </div>
         </div>
         <div class="card-body">
            <table class="table table-striped w-100" id="documentTypeTable">
               <thead>
                  <tr>
                     <th>{{ __('documentType') }}</th>
                     <th width="250px" style="text-align: right">{{ __('action') }}</th>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
   <div class="col-lg-6 col-md-6 col-sm-12">
      <div class="card">
         <div class="card-header">
            <div class="page-title w-100">
               <div class="d-flex align-items-center justify-content-between">
                  <h4 class="mb-0 fw-normal">{{ __('reportReason') }}</h4>
                  <button type="button" class="btn theme-bg theme-btn text-white" data-bs-toggle="modal"
                     data-bs-target="#reportReasonModal">
                  {{ __('addReportReason') }}
                  </button>
               </div>
            </div>
         </div>
         <div class="card-body">
            <table class="table table-striped w-100" id="reportReasonTable">
               <thead>
                  <tr>
                     <th>{{ __('reportReason') }}</th>
                     <th width="250px" style="text-align: right">{{ __('action') }}</th>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
</div>
<!-- Document Type Modal -->
<div class="modal fade" id="documentTypeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h1 class="modal-title fs-5 fw-normal" id="exampleModalLabel">{{ __('addDocumentType') }}</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="addDocumentTypeForm" method="post">
            <div class="modal-body">
               <div class="form-group">
                  <label for="title" class="form-label">{{ __('title') }}</label>
                  <input type="text" class="form-control" name="title" id="documentType" required="">
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
               <button type="submit" class="btn theme-btn text-white">{{ __('save') }}</button>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- Edit Document Type Modal -->
<div class="modal fade" id="editDocumentTypeModal" tabindex="-1" aria-labelledby="exampleModalLabel"
   aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h1 class="modal-title fs-5 fw-normal" id="exampleModalLabel">{{ __('editDocumentType') }}</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="editDocumentTypeForm" method="post">
            <input type="hidden" name="" id="documentTypeId">
            <div class="modal-body">
               <div class="form-group">
                  <label for="title" class="form-label">{{ __('title') }}</label>
                  <input type="text" class="form-control" name="title" id="editDocumentType" required="">
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
               <button type="submit" class="btn theme-btn text-white">{{ __('save') }}</button>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- Report Reason Modal -->
<div class="modal fade" id="reportReasonModal" tabindex="-1" aria-labelledby="exampleModalLabel"
   aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h1 class="modal-title fs-5 fw-normal" id="exampleModalLabel">{{ __('addReportReason') }}</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="reportReasonForm" method="post">
            <div class="modal-body">
               <div class="form-group">
                  <label for="title" class="form-label">{{ __('title') }}</label>
                  <input type="text" class="form-control" name="title" id="reportReason" required="">
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
               <button type="submit" class="btn theme-btn text-white">{{ __('save') }}</button>
            </div>
         </form>
      </div>
   </div>
</div>
<!-- Edit Report Reason Modal -->
<div class="modal fade" id="editReportReasonModal" tabindex="-1" aria-labelledby="exampleModalLabel"
   aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h1 class="modal-title fs-5 fw-normal" id="exampleModalLabel">{{ __('editReportReason') }} </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="editReportReasonForm" method="post">
            <input type="hidden" name="" id="reportReasonId">
            <div class="modal-body">
               <div class="form-group">
                  <label for="title" class="form-label">{{ __('title') }}</label>
                  <input type="text" class="form-control" name="title" id="editReportReason"
                     required="">
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
               <button type="submit" class="btn theme-btn text-white">{{ __('save') }}</button>
            </div>
         </form>
      </div>
   </div>
</div>
@endsection