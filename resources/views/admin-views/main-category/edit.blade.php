@extends('layouts.admin.app')

@section('title',translate('messages.Update_Main_Category'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{dynamicAsset('public/assets/admin/img/category.png')}}" alt="">
                        </div>
                        <span>
                            {{translate('Update Main Category')}}
                        </span>
                    </h2>
                </div>
                {{-- <a href="{{route('admin.main-category.add')}}" class="btn btn--primary pull-right"><i class="tio-add-circle"></i> {{translate('messages.Add_New_Main_Category')}}</a> --}}
            </div>
        </div>
        <!-- End Page Header -->

        <div class="card resturant--cate-form">
            <div class="card-body">
                <form action="{{route('admin.main-category.update',[$mainCategory['id']])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                    @php($language = $language->value ?? null)
                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                    @if($language)
                        <ul class="nav nav-tabs mb-4">
                            <li class="nav-item">
                                <a class="nav-link lang_link  active" href="#" id="default-link">{{ translate('Default')}}</a>
                            </li>
                            @foreach(json_decode($language) as $lang)
                                <li class="nav-item">
                                    <a class="nav-link lang_link " href="#" id="{{$lang}}-link">{{\App\CentralLogics\Helpers::get_language_name($lang).'('.strtoupper($lang).')'}}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="row">
                        <div class="col-lg-6">
                            @if ($language)
                            <div class="form-group lang_form" id="default-form">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}}</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{ translate('Ex:_Main_Category_Name') }}"   maxlength="191" value="{{$mainCategory->name}}">
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                                @foreach(json_decode($language) as $lang)
                                    <div class="form-group d-none lang_form" id="{{$lang}}-form">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}} ({{strtoupper($lang)}})</label>
                                        <input id="name" type="text" name="name[]" class="form-control" placeholder="{{ translate('Ex:_Main_Category_Name') }}" maxlength="191" oninvalid="document.getElementById('en-link').click()">
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    </div>
                                @endforeach
                            @else
                                <div class="form-group">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('messages.name')}}</label>
                                    <input type="text" name="name[]" class="form-control" placeholder="{{ translate('Ex:_Main_Category_Name') }}"   maxlength="191" value="{{$mainCategory->name}}">
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex flex-column align-items-center gap-3">
                                <p class="mb-0">{{ translate('Main Category image') }}</p>

                                <div class="image-box">
                                    <label for="image-input" class="d-flex flex-column align-items-center justify-content-center h-100 cursor-pointer gap-2">
                                        <img class="upload-icon initial-10"
                                        src="{{dynamicAsset('public/assets/admin/img/upload-icon.png')}}" alt="Upload Icon">
                                        <span class="upload-text">{{ translate('Upload Image')}}</span>
                                        <img src="{{dynamicStorage('storage/app/public/main-category/').'/'.$mainCategory->image}}" alt="Preview Image" class="preview-image">
                                    </label>
                                    <button type="button" class="delete_image">
                                        <i class="tio-delete"></i>
                                    </button>
                                    <input type="file" id="image-input" name="image" accept="image/*" hidden>
                                </div>

                                <p class="opacity-75 max-w220 mx-auto text-center">
                                    {{ translate('Image format - jpg png jpeg gif Image Size -maximum size 2 MB Image Ratio - 1:1')}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="input-label" for="start_time">{{translate('messages.start_time')}}</label>
                                <input type="time" name="start_time" id="start_time" class="form-control" value="{{ $mainCategory->start_time ? (is_string($mainCategory->start_time) ? \Carbon\Carbon::parse($mainCategory->start_time)->format('H:i') : $mainCategory->start_time->format('H:i')) : '' }}">
                                <small class="text-muted">{{translate('messages.optional_time_range')}}</small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="input-label" for="end_time">{{translate('messages.end_time')}}</label>
                                <input type="time" name="end_time" id="end_time" class="form-control" value="{{ $mainCategory->end_time ? (is_string($mainCategory->end_time) ? \Carbon\Carbon::parse($mainCategory->end_time)->format('H:i') : $mainCategory->end_time->format('H:i')) : '' }}">
                                <small class="text-muted">{{translate('messages.optional_time_range')}}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group pt-2 mb-0">
                                <div class="btn--container justify-content-end">
                                    <!-- Static Button -->
                                    <button id="reset_btn" type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                    <!-- Static Button -->
                                    <button type="submit" class="btn btn--primary">{{translate('messages.update')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });
        $('#reset_btn').on('click',function (){

            $('.preview-image').attr('src', "{{dynamicAsset('public/assets/admin/img/aspect-1.png')}}");
            $('#image').val(null);
    });
    </script>
@endpush
