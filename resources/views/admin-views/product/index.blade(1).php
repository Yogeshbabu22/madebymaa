@extends('layouts.admin.app')

@section('title', translate('Add_New_Food'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ dynamicAsset('public/assets/admin/css/tags-input.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{ translate('messages.Add_New_Food') }}</h1>
                </div>
            </div>
        </div>

        <!-- End Page Header -->
        <form action="javascript:" method="post" id="food_form" enctype="multipart/form-data">
            @csrf
            <div class="row g-2">
                <div class="col-lg-6">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-body pb-0">
                            @php($language = \App\Models\BusinessSetting::where('key', 'language')->first())
                            @php($language = $language->value ?? null)
                            @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                @if ($language)
                                    <ul class="nav nav-tabs mb-4">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active"
                                                href="#"
                                                id="default-link">{{ translate('Default') }}</a>
                                        </li>
                                        @foreach (json_decode($language) as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link "
                                                    href="#"
                                                    id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                        </div>
                        <!--@if ($language)-->
                        <!--    <div class="card-body">-->

                        <!--        <div class="lang_form"-->
                        <!--        id="default-form">-->


                        <!--        <div class="form-group">-->
                        <!--            <label class="input-label"-->
                        <!--                for="default_name">{{ translate('messages.name') }}-->
                        <!--                ({{ translate('Default') }}) <span class="form-label-secondary text-danger"-->
                        <!--                data-toggle="tooltip" data-placement="right"-->
                        <!--                data-original-title="{{ translate('messages.Required.')}}"> *-->
                        <!--                </span>-->
                        <!--            </label>-->
                        <!--            <input type="text" name="name[]" id="default_name"-->
                        <!--                class="form-control"-->
                        <!--                placeholder="{{ translate('messages.new_food') }}"-->

                        <!--                oninvalid="document.getElementById('en-link').click()">-->
                        <!--        </div>-->
                        <!--        <input type="hidden" name="lang[]" value="default">-->
                        <!--        <div class="form-group mb-0">-->
                        <!--            <label class="input-label"-->
                        <!--                for="exampleFormControlInput1">{{ translate('messages.short_description') }}-->
                        <!--                ({{ translate('Default') }}) <span class="form-label-secondary text-danger"-->
                        <!--                data-toggle="tooltip" data-placement="right"-->
                        <!--                data-original-title="{{ translate('messages.Required.')}}"> *-->
                        <!--                </span></label>-->
                        <!--            <textarea type="text" name="description[]" class="form-control ckeditor min-height-154px"></textarea>-->
                        <!--        </div>-->
                        <!--    </div>-->

                        <!--        @foreach (json_decode($language) as $lang)-->
                        <!--        <div class="d-none lang_form"-->
                        <!--        id="{{ $lang }}-form">-->
                        <!--                <div class="form-group">-->
                        <!--                    <label class="input-label"-->
                        <!--                        for="{{ $lang }}_name">{{ translate('messages.name') }}-->
                        <!--                        ({{ strtoupper($lang) }})-->
                        <!--                    </label>-->
                        <!--                    <input type="text" name="name[]" id="{{ $lang }}_name"-->
                        <!--                        class="form-control"-->
                        <!--                        placeholder="{{ translate('messages.new_food') }}"-->
                        <!--                        oninvalid="document.getElementById('en-link').click()">-->
                        <!--                </div>-->
                        <!--                <input type="hidden" name="lang[]" value="{{ $lang }}">-->
                        <!--                <div class="form-group mb-0">-->
                        <!--                    <label class="input-label"-->
                        <!--                        for="exampleFormControlInput1">{{ translate('messages.short_description') }}-->
                        <!--                        ({{ strtoupper($lang) }})</label>-->
                        <!--                    <textarea type="text" name="description[]" class="form-control ckeditor min-height-154px"></textarea>-->
                        <!--                </div>-->
                        <!--            </div>-->
                        <!--        @endforeach-->
                        <!--    </div>-->
                        <!--@else-->
                        <!--    <div class="card-body">-->
                        <!--        <div id="default-form">-->
                        <!--            <div class="form-group">-->
                        <!--                <label class="input-label"-->
                        <!--                    for="exampleFormControlInput1">{{ translate('messages.name') }}-->
                        <!--                    ({{ translate('Default') }})</label>-->
                        <!--                <input type="text" name="name[]" class="form-control"-->
                        <!--                    placeholder="{{ translate('messages.new_food') }}" >-->
                        <!--            </div>-->
                        <!--            <input type="hidden" name="lang[]" value="default">-->
                        <!--            <div class="form-group mb-0">-->
                        <!--                <label class="input-label"-->
                        <!--                    for="exampleFormControlInput1">{{ translate('messages.short_description') }}</label>-->
                        <!--                <textarea type="text" name="description[]" class="form-control ckeditor min-height-154px"></textarea>-->
                        <!--            </div>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--@endif-->
                        
                                              @if ($language)
    <div class="card-body">
        <div class="lang_form" id="default-form">
            <div class="form-group">
                <label class="input-label" for="default_name">
                    {{ translate('messages.name') }} ({{ translate('Default') }}) 
                    <span class="form-label-secondary text-danger" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Required.') }}"> * </span>
                </label>
                <input type="text" name="name[]" id="default_name" class="form-control" placeholder="{{ translate('messages.new_food') }}" oninvalid="document.getElementById('en-link').click()">
            </div>
            <input type="hidden" name="lang[]" value="default">
            
            <div class="form-group mb-0">
                <label class="input-label" for="exampleFormControlInput1">
                    {{ translate('messages.short_description') }} ({{ translate('Default') }}) 
                    <span class="form-label-secondary text-danger" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Required.') }}"> * </span>
                </label>
                <textarea type="text" name="description[]" class="form-control ckeditor min-height-154px"></textarea>
            </div>
            
            <!-- Ingredients Field as Input Text -->
            <div class="form-group mb-0">
                <label class="input-label" for="default_ingredients">
                    {{ translate('messages.ingredients') }} ({{ translate('Default') }}) 
                    <span class="form-label-secondary text-danger" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.Required.') }}"> * </span>
                </label>
                <input type="text" name="ingredients[]" id="default_ingredients" class="form-control" placeholder="{{ translate('messages.enter_ingredients') }}">
            </div>
        </div>

        @foreach (json_decode($language) as $lang)
            <div class="d-none lang_form" id="{{ $lang }}-form">
                <div class="form-group">
                    <label class="input-label" for="{{ $lang }}_name">
                        {{ translate('messages.name') }} ({{ strtoupper($lang) }})
                    </label>
                    <input type="text" name="name[]" id="{{ $lang }}_name" class="form-control" placeholder="{{ translate('messages.new_food') }}" oninvalid="document.getElementById('en-link').click()">
                </div>
                <input type="hidden" name="lang[]" value="{{ $lang }}">
                
                <div class="form-group mb-0">
                    <label class="input-label" for="exampleFormControlInput1">
                        {{ translate('messages.short_description') }} ({{ strtoupper($lang) }})
                    </label>
                    <textarea type="text" name="description[]" class="form-control ckeditor min-height-154px"></textarea>
                </div>

                <!-- Ingredients Field for Other Languages as Input Text -->
                <div class="form-group mb-0">
                    <label class="input-label" for="{{ $lang }}_ingredients">
                        {{ translate('messages.ingredients') }} ({{ strtoupper($lang) }})
                    </label>
                    <input type="text" name="ingredients[]" id="{{ $lang }}_ingredients" class="form-control" placeholder="{{ translate('messages.enter_ingredients') }}">
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card-body">
        <div id="default-form">
            <div class="form-group">
                <label class="input-label" for="exampleFormControlInput1">
                    {{ translate('messages.name') }} ({{ translate('Default') }})
                </label>
                <input type="text" name="name[]" class="form-control" placeholder="{{ translate('messages.new_food') }}">
            </div>
            <input type="hidden" name="lang[]" value="default">
            <div class="form-group mb-0">
                <label class="input-label" for="exampleFormControlInput1">
                    {{ translate('messages.short_description') }}
                </label>
                <textarea type="text" name="description[]" class="form-control ckeditor min-height-154px"></textarea>
            </div>

            <!-- Ingredients Field for Default Language as Input Text -->
            <div class="form-group mb-0">
                <label class="input-label" for="default_ingredients">
                    {{ translate('messages.ingredients') }} ({{ translate('Default') }})
                </label>
                <input type="text" name="ingredients[]" id="default_ingredients" class="form-control" placeholder="{{ translate('messages.enter_ingredients') }}">
            </div>
        </div>
    </div>
@endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow--card-2 border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center gap-3">
                                <p class="mb-0">{{ translate('Food_Image') }}</p>

                                <div class="image-box">
                                    <label for="image-input" class="d-flex flex-column align-items-center justify-content-center h-100 cursor-pointer gap-2">
                                    <img width="30" class="upload-icon" src="{{asset('public/assets/admin/img/upload-icon.png')}}" alt="Upload Icon">
                                    <span class="upload-text">{{ translate('Upload Image')}}</span>
                                    <img src="#" alt="Preview Image" class="preview-image">
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
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-dashboard-outlined"></i>
                                </span>
                                <span> {{ translate('Restaurants_&_Category_Info') }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-sm-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlSelect1">{{ translate('messages.restaurant') }} <span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Required.')}}"> *
                                            </span><span
                                                class="input-label-secondary"></span></label>
                                        <select name="restaurant_id" id="restaurant_id"
                                            data-placeholder="{{ translate('messages.select_restaurant') }}"
                                            class="js-data-example-ajax form-control"
                                            oninvalid="this.setCustomValidity('{{ translate('messages.please_select_restaurant') }}')">

                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="super_category_ids">{{ translate('messages.main_category') }}<span
                                                class="input-label-secondary" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('messages.select_multiple_main_categories') }}"><img
                                                    src="{{ dynamicAsset('/public/assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ translate('messages.select_multiple_main_categories') }}"></span></label>
                                        <select name="super_category_ids[]" id="super_category_ids"
                                            class="form-control js-select2-custom" multiple="multiple">
                                            @foreach ($mainCategories as $mainCategory)
                                                <option value="{{ $mainCategory['id'] }}">{{ $mainCategory['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlSelect1">{{ translate('messages.category') }}<span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Required.')}}"> *
                                            </span></label>
                                        <select name="category_id" id="category_id"
                                            class="form-control js-select2-custom get-request"
                                            oninvalid="this.setCustomValidity('Select Category')">
                                            <option value="" selected disabled>
                                                {{ translate('Select_Category') }}</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category['id'] }}">{{ $category['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlSelect1">{{ translate('messages.sub_category') }}<span
                                                class="input-label-secondary" data-toggle="tooltip"
                                                data-placement="right"
                                                data-original-title="{{ translate('messages.category_required_warning') }}"><img
                                                    src="{{ dynamicAsset('/public/assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ translate('messages.category_required_warning') }}"></span></label>
                                        <select name="sub_category_id" id="sub-categories"
                                            class="form-control js-select2-custom">
                                            <option value="" selected disabled>
                                                {{ translate('Select_Sub_Category') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.food_type') }}<span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Required.')}}"> *
                                            </span></label>
                                        <select name="veg" id="veg"
                                            class="form-control js-select2-custom">
                                            <option value="" selected disabled>
                                                {{ translate('Select_Preferences') }}</option>
                                            <option value="0">{{ translate('messages.non_veg') }}</option>
                                            <option value="1">{{ translate('messages.veg') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3" id="halal">
                                    <div class="form-check mb-0 p-4">
                                        <input class="form-check-input" name="is_halal" type="checkbox" value="1" id="flexCheckDefault" checked>
                                        <label class="form-check-label" for="flexCheckDefault">
                                            {{ translate('messages.Is_It_Halal') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-dashboard-outlined"></i>
                                </span>
                                <span>{{ translate('messages.addon') }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <label class="input-label"
                                for="exampleFormControlSelect1">{{ translate('Select_Add-on') }}<span
                                    class="input-label-secondary" data-toggle="tooltip"
                                    data-placement="right"
                                    data-original-title="{{ translate('messages.The_selected_addon’s_will_be_displayed_in_this_food_details') }}"><img
                                        src="{{ dynamicAsset('/public/assets/admin/img/info-circle.svg') }}"
                                        alt="{{ translate('messages.The_selected_addon’s_will_be_displayed_in_this_food_details') }}"></span></label>
                            <select name="addon_ids[]" class="form-control border js-select2-custom"
                                multiple="multiple" id="add_on">

                            </select>
                        </div>
                    </div>
                </div>
                
                
                
                
                
                
                
                
                <!--<div class="col-lg-6">-->
                <!--    <div class="card shadow--card-2 border-0">-->
                <!--        <div class="card-header">-->
                <!--            <h5 class="card-title">-->
                <!--                <span class="card-header-icon mr-2"><i class="tio-date-range"></i></span>-->
                <!--                <span>{{ translate('messages.Availability') }}</span>-->
                <!--            </h5>-->
                <!--        </div>-->
                <!--        <div class="card-body">-->
                <!--            <div class="row g-2">-->
                <!--                <div class="col-sm-6">-->
                <!--                    <div class="form-group mb-0">-->
                <!--                        <label class="input-label"-->
                <!--                            for="exampleFormControlInput1">{{ translate('messages.available_time_starts') }}<span class="form-label-secondary text-danger"-->
                <!--                            data-toggle="tooltip" data-placement="right"-->
                <!--                            data-original-title="{{ translate('messages.Required.')}}"> *-->
                <!--                            </span></label>-->
                <!--                        <input type="time" name="available_time_starts" class="form-control"-->
                <!--                            id="available_time_starts"-->
                <!--                            placeholder="{{ translate('messages.Ex:_10:30_am') }} " required>-->
                <!--                    </div>-->
                <!--                </div>-->
                <!--                <div class="col-sm-6">-->
                <!--                    <div class="form-group mb-0">-->
                <!--                        <label class="input-label"-->
                <!--                            for="exampleFormControlInput1">{{ translate('messages.available_time_ends') }}<span class="form-label-secondary text-danger"-->
                <!--                            data-toggle="tooltip" data-placement="right"-->
                <!--                            data-original-title="{{ translate('messages.Required.')}}"> *-->
                <!--                            </span></label>-->
                <!--                        <input type="time" name="available_time_ends" class="form-control"-->
                <!--                            id="available_time_ends" placeholder="5:45 pm" required>-->
                <!--                    </div>-->
                <!--                </div>-->
                <!--            </div>-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->
                <div class="col-lg-6">
    <div class="card shadow--card-2 border-0">
        <div class="card-header">
            <h5 class="card-title">
                <span class="card-header-icon mr-2"><i class="tio-date-range"></i></span>
                <span>{{ translate('messages.Availability') }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12">
                    <label class="input-label">Monday</label>
                    <div id="monday-slots">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="monday_start[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="monday_end[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger" onclick="removeSlot(this)">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSlot('monday')">+</button>
                </div>

                <div class="col-12">
                    <label class="input-label">Tuesday</label>
                    <div id="tuesday-slots">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="tuesday_start[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="tuesday_end[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger" onclick="removeSlot(this)">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSlot('tuesday')">+</button>
                </div>

                <div class="col-12">
                    <label class="input-label">Wednesday</label>
                    <div id="wednesday-slots">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="wednesday_start[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="wednesday_end[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger" onclick="removeSlot(this)">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSlot('wednesday')">+</button>
                </div>

                <div class="col-12">
                    <label class="input-label">Thursday</label>
                    <div id="thursday-slots">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="thursday_start[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="thursday_end[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger" onclick="removeSlot(this)">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSlot('thursday')">+</button>
                </div>

                <div class="col-12">
                    <label class="input-label">Friday</label>
                    <div id="friday-slots">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="friday_start[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="friday_end[]" class="form-control" >
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger" onclick="removeSlot(this)">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSlot('friday')">+</button>
                </div>

                <div class="col-12">
                    <label class="input-label">Saturday</label>
                    <div id="saturday-slots">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="saturday_start[]" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="saturday_end[]" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger" onclick="removeSlot(this)">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSlot('saturday')">+</button>
                </div>

                <div class="col-12">
                    <label class="input-label">Sunday</label>
                    <div id="sunday-slots">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="sunday_start[]" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group mb-0">
                                    <input type="time" name="sunday_end[]" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger" onclick="removeSlot(this)">X</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSlot('sunday')">+</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function addSlot(day) {
        var container = document.getElementById(day + '-slots');
        var slot = document.createElement('div');
        slot.className = 'row g-2 mb-2';
        slot.innerHTML = `
            <div class="col-sm-5">
                <div class="form-group mb-0">
                    <input type="time" name="${day}_start[]" class="form-control" required>
                </div>
            </div>
            <div class="col-sm-5">
                <div class="form-group mb-0">
                    <input type="time" name="${day}_end[]" class="form-control" required>
                </div>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger" onclick="removeSlot(this)">X</button>
            </div>
        `;
        container.appendChild(slot);
    }

    function removeSlot(button) {
        button.parentElement.parentElement.remove();
    }
</script>



                
                
                
                
                
                
                
                
                
                <div class="col-lg-12">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2"><i class="tio-dollar-outlined"></i></span>
                                <span>{{ translate('Price_Information') }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.price') }}<span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Required.')}}"> *
                                            </span></label>
                                        <input type="number" min="0" max="999999999999.99"
                                            step="0.01" value="1" name="price" class="form-control"
                                            placeholder="{{ translate('messages.Ex:_100') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.discount_type') }}

                                        </label>
                                        <select name="discount_type" class="form-control js-select2-custom">
                                            <option value="percent">{{ translate('messages.percent').' (%)' }}</option>
                                            <option value="amount">{{ translate('messages.amount').' ('.\App\CentralLogics\Helpers::currency_symbol().')'  }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="exampleFormControlInput1">{{ translate('messages.discount') }}
                                            <span class="form-label-secondary text-danger"
                                            data-toggle="tooltip" data-placement="right"
                                            data-original-title="{{ translate('messages.Required.')}}"> *
                                            </span>
                                            <span class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('Currently_you_need_to_manage_discount_with_the_Restaurant.') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                        </label>
                                        <input type="number" min="0" max="999999999999999"
                                            value="0" name="discount" class="form-control"
                                            placeholder="{{ translate('messages.Ex:_100') }} ">
                                    </div>
                                </div>
                                <div class="col-md-3" id="maximum_cart_quantity">
                                    <div class="form-group mb-0">
                                        <label class="input-label"
                                            for="maximum_cart_quantity">{{ translate('messages.Maximum_Purchase_Quantity_Limit') }}
                                            <span
                                            class="input-label-secondary text--title" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('If_this_limit_is_exceeded,_customers_can_not_buy_the_food_in_a_single_purchase.') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                        </label>
                                        <input type="number"  placeholder="{{ translate('messages.Ex:_10') }}" class="form-control" name="maximum_cart_quantity" min="0" id="cart_quantity">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-header flex-wrap">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2">
                                    <i class="tio-canvas-text"></i>
                                </span>
                                <span>{{ translate('messages.food_variations') }}</span>
                            </h5>
                            <a class="btn text--primary-2" id="add_new_option_button">
                                {{ translate('add_new_variation') }}
                                <i class="tio-add"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-12">
                                    <div id="add_new_option">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2"><i class="tio-label"></i></span>
                                <span>{{ translate('tags') }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <input type="text" class="form-control" name="tags" placeholder="Enter tags" data-role="tagsinput">
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="btn--container justify-content-end">
                        <button type="reset" id="reset_btn"
                            class="btn btn--reset">{{ translate('messages.reset') }}</button>
                        <button type="submit"
                            class="btn btn--primary">{{ translate('messages.submit') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection


@push('script_2')
    <script src="{{ dynamicAsset('public/assets/admin') }}/js/tags-input.min.js"></script>
    <script src="{{ dynamicAsset('public/assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script src="{{dynamicAsset('public/assets/admin')}}/js/view-pages/product-index.js"></script>
    <script>
        "use strict";
        $(document).ready(function() {
            $("#add_new_option_button").click(function(e) {
                $('#empty-variation').hide();
                count++;
                let add_option_view = `
                    <div class="__bg-F8F9FC-card view_new_option mb-2">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <label class="form-check form--check">
                                    <input id="options[` + count + `][required]" name="options[` + count + `][required]" class="form-check-input" type="checkbox">
                                    <span class="form-check-label">{{ translate('Required') }}</span>
                                </label>
                                <div>
                                    <button type="button" class="btn btn-danger btn-sm delete_input_button"
                                        title="{{ translate('Delete') }}">
                                        <i class="tio-add-to-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-xl-4 col-lg-6">
                                    <label for="">{{ translate('name') }}&nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.')}}"> *
                                </span></label>
                                    <input required name=options[` + count +
                    `][name] class="form-control new_option_name" type="text" data-count="`+
                    count +`">
                                </div>

                                <div class="col-xl-4 col-lg-6">
                                    <div>
                                        <label class="input-label text-capitalize d-flex align-items-center"><span class="line--limit-1">{{ translate('messages.selcetion_type') }} </span>
                                        </label>
                                        <div class="resturant-type-group px-0">
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input show_min_max" data-count="`+count+`" type="radio" value="multi"
                                                name="options[` + count + `][type]" id="type` + count +
                    `" checked
                                                >
                                                <span class="form-check-label">
                                                    {{ translate('Multiple Selection') }}
                    </span>
                </label>

                <label class="form-check form--check mr-2 mr-md-4">
                    <input class="form-check-input hide_min_max" data-count="`+count+`" type="radio" value="single"
                    name="options[` + count + `][type]" id="type` + count +
                    `"
                                                >
                                                <span class="form-check-label">
                                                    {{ translate('Single Selection') }}
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-6">
        <div class="row g-2">
            <div class="col-6">
                <label for="">{{ translate('Min') }}</label>
                                            <input id="min_max1_` + count + `" required  name="options[` + count + `][min]" class="form-control" type="number" min="1">
                                        </div>
                                        <div class="col-6">
                                            <label for="">{{ translate('Max') }}</label>
                                            <input id="min_max2_` + count + `"   required name="options[` + count + `][max]" class="form-control" type="number" min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="option_price_` + count + `" >
                                <div class="bg-white border rounded p-3 pb-0 mt-3">
                                    <div  id="option_price_view_` + count + `">
                                        <div class="row g-3 add_new_view_row_class mb-3">
                                            <div class="col-md-4 col-sm-6">
                                                <label for="">{{ translate('Option_name') }} &nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.')}}"> *
                                </span></label>
                                                <input class="form-control" required type="text" name="options[` +
                    count +
                    `][values][0][label]" id="">
                                            </div>
                                            <div class="col-md-4 col-sm-6">
                                                <label for="">{{ translate('Additional_price') }} &nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.')}}"> *
                                </span></label>
                                                <input class="form-control" required type="number" min="0" step="0.01" name="options[` +
                    count + `][values][0][optionPrice]" id="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3 p-3 mr-1 d-flex "  id="add_new_button_` + count +
                    `">
                                        <button type="button" class="btn btn--primary btn-outline-primary add_new_row_button" data-count="`+
                    count +`">{{ translate('Add_New_Option') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;

                $("#add_new_option").append(add_option_view);
            });
        });

        function add_new_row_button(data) {
            count = data;
            countRow = 1 + $('#option_price_view_' + data).children('.add_new_view_row_class').length;
            let add_new_row_view = `
            <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-sm-0">
                <div class="col-md-4 col-sm-5">
                        <label for="">{{ translate('Option_name') }} &nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.')}}"> *
                                </span></label>
                        <input class="form-control" required type="text" name="options[` + count + `][values][` +
                countRow + `][label]" id="">
                    </div>
                    <div class="col-md-4 col-sm-5">
                        <label for="">{{ translate('Additional_price') }} &nbsp;<span class="form-label-secondary text-danger"
                                data-toggle="tooltip" data-placement="right"
                                data-original-title="{{ translate('messages.Required.')}}"> *
                                </span></label>
                        <input class="form-control"  required type="number" min="0" step="0.01" name="options[` +
                count +
                `][values][` + countRow + `][optionPrice]" id="">
                    </div>
                    <div class="col-sm-2 max-sm-absolute">
                        <label class="d-none d-sm-block">&nbsp;</label>
                        <div class="mt-1">
                            <button type="button" class="btn btn-danger btn-sm deleteRow"
                                title="{{ translate('Delete') }}">
                                <i class="tio-add-to-trash"></i>
                            </button>
                        </div>
                </div>
            </div>`;
            $('#option_price_view_' + data).append(add_new_row_view);

        }

        $('#restaurant_id').on('change', function () {
            let route = '{{ url('/') }}/admin/restaurant/get-addons?data[]=0&restaurant_id=';
            let restaurant_id = $(this).val();
            let id = 'add_on';
            getRestaurantData(route,restaurant_id, id);

        });
        function getRestaurantData(route, restaurant_id, id) {
            $.get({
                url: route + restaurant_id,
                dataType: 'json',
                success: function(data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        $('.get-request').on('change', function () {
            let route = '{{ url('/') }}/admin/food/get-categories?parent_id='+$(this).val();
            let id = 'sub-categories';
            getRequest(route, id);
        });

        function getRequest(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function(data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }

        $(document).on('ready', function() {
            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
            
            // Initialize super category dropdown with multiple selection
            $('#super_category_ids').select2({
                placeholder: '{{ translate('messages.select_main_categories') }}',
                allowClear: true,
                multiple: true
            });
        });
        $('.js-data-example-ajax').select2({
            ajax: {
                url: '{{ url('/') }}/admin/restaurant/get-restaurants',
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                __port: function(params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });

        $('#food_form').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.food.store') }}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#loading').hide();
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{ translate('messages.product_added_successfully') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function() {
                            location.href =
                                '{{ route('admin.food.list') }}';
                        }, 2000);
                    }
                }
            });
        });

        $('#reset_btn').click(function() {
            $('#restaurant_id').val(null).trigger('change');
            $('#category_id').val(null).trigger('change');
            $('#categories').val(null).trigger('change');
            $('#sub-veg').val(0).trigger('change');
            $('#add_on').val(null).trigger('change');
            $('#viewer').attr('src', "{{ dynamicAsset('public/assets/admin/img/upload.png') }}");
        })
    </script>
@endpush
