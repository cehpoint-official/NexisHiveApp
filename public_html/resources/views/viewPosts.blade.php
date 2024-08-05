@extends('include.app')
@section('header')
    <script src="{{ asset('asset/script/post.js') }}"></script>
@endsection

@section('content')
    <section class="section">

        <div class="card">
            <div class="card-header">
                <div class="page-title w-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0 fw-normal d-flex align-items-center"> {{ __('allPosts') }} </h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                 <table class="table table-striped w-100" id="allPostsTable">
                    <thead>
                        <tr>
                            <th style="width: 150px"> {{ __('content') }} </th>
                            <th> {{ __('userName') }} </th>
                            <th> {{ __('fullname') }} </th>
                            <th> {{ __('comments') }} </th>
                            <th> {{ __('likes') }} </th>
                            <th> {{ __('createdAt') }} </th>
                            <th style="text-align: right; width: 200px;"> {{ __('action') }} </th>
                        </tr>
                    </thead>
                </table> 
            </div>
        </div>

    </section>

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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> {{ __('close') }}</button>
                </div>
            </div>
        </div>
    </div> 
@endsection
