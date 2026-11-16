@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-facebook-comment-page', 'pageClass' => 'admin-facebook-comment-page'])
@section('page-title', 'Facebook Comment')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Facebook Comment - Settings', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px col row ap-facebook-comment-page d-flex flex-column flex-lg-row gap-3">
    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif -->

    <div class="card col-xl-6">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Facebook Comment Settings</h2></div>
        <div class="card-body">
            <form action="{{ route('admin.settings.facebook-comment.update') }}" method="POST" class="d-flex flex-column gap-3">
                @csrf
                <div class="form-group row align-items-center">
                    <label class="col-md-3 col-form-label m-0" for="facebook-comment-enabled">Enable Facebook Comments</label>
                    <div class="col-md-9">
                        <input type="hidden" name="facebook_comment_enabled" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" id="facebook-comment-enabled" type="checkbox" name="facebook_comment_enabled" value="1" @checked(filter_var($settings['facebook_comment_enabled'] ?? '0', FILTER_VALIDATE_BOOLEAN))>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label m-0" for="facebook-app-id">Facebook App ID</label>
                    <div class="col-md-9"><input id="facebook-app-id" type="text" name="facebook_app_id" class="form-control" value="{{ old('facebook_app_id', $settings['facebook_app_id'] ?? '') }}" placeholder="App ID for comments plugin"></div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label m-0" for="facebook-comment-num-posts">Number of Comments</label>
                    <div class="col-md-9"><input id="facebook-comment-num-posts" type="number" min="1" max="50" name="facebook_comment_num_posts" class="form-control" value="{{ old('facebook_comment_num_posts', $settings['facebook_comment_num_posts'] ?? '5') }}"></div>
                </div>
                <div class="text-right"><button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-save"></i> Save</button></div>
            </form>
        </div>
    </div>
    <div class="card col-xl-5">
        <div class="card-header bg-white">
            <h6 class="mb-0">Please be carefull when you are configuring Facebook Comment. For incorrect configuration you will not get comment section on your user-end site.</h6>
        </div>
        <div class="card-body">
            <ul class="mb-0 ps-3">
                <li class="mb-2">1. Login into your facebook page</li>
                <li class="mb-2">2. Find the About option of your facebook page.</li>
                <li class="mb-0">3. At the very bottom, you can find the “Facebook Page ID”.</li>
                <li class="mb-0">4. Go to Settings of your page and find the option of "Advance Messaging".</li>
                <li class="mb-0">5. Scroll down that page and you will get "white listed domain".</li>
                <li class="mb-0">6. Set your website domain name.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
