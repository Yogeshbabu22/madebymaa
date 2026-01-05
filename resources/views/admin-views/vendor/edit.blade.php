@extends('layouts.admin.app')

@section('title', translate('Update_restaurant_info'))
@push('css_or_js')
    <link rel="stylesheet" href="{{dynamicAsset('/public/assets/admin/css/intlTelInput.css')}}" />
@endpush
@section('content')
    <div class="content container-fluid initial-57">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-shop-outlined"></i>
                        {{ translate('messages.update_restaurant') }}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        @php
        // Parse delivery_time - format can be "10-30 min" or "10-30-min"
        $delivery_time_parts = explode('-', $restaurant->delivery_time);
        $delivery_time_start = isset($delivery_time_parts[0]) ? trim($delivery_time_parts[0]) : 10;

        // Handle second part which might be "30 min" or "30"
        if (isset($delivery_time_parts[1])) {
            $second_part = trim($delivery_time_parts[1]);
            // Check if it contains space (format: "30 min")
            if (strpos($second_part, ' ') !== false) {
                $parts = explode(' ', $second_part);
                $delivery_time_end = isset($parts[0]) ? trim($parts[0]) : 30;
                $delivery_time_type = isset($parts[1]) ? trim($parts[1]) : 'min';
            } else {
                // Format: "30" (no space, check if third part exists)
                $delivery_time_end = $second_part;
                $delivery_time_type = isset($delivery_time_parts[2]) ? trim($delivery_time_parts[2]) : 'min';
            }
        } else {
            $delivery_time_end = 30;
            $delivery_time_type = 'min';
        }

        // Ensure values are numeric
        $delivery_time_start = is_numeric($delivery_time_start) ? (int)$delivery_time_start : 10;
        $delivery_time_end = is_numeric($delivery_time_end) ? (int)$delivery_time_end : 30;
        $delivery_time_type = in_array($delivery_time_type, ['min', 'hours']) ? $delivery_time_type : 'min';
    @endphp
        @php
            $language = \App\Models\BusinessSetting::where('key','language')->first();
            $language = $language ? $language->value : null;
            $default_lang = str_replace('_', '-', app()->getLocale());
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        @endphp

        <form action="{{ route('admin.restaurant.update', [$restaurant['id']]) }}" method="post"
        class="js-validate" id="res_form" enctype="multipart/form-data">
                        @csrf
            <div class="row g-2">
                <div class="col-lg-6">
                    <div class="card shadow--card-2">
                        <div class="card-body">
                            @if($language)
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link">{{ translate('Default') }}</a>
                                </li>
                                @foreach (json_decode($language) as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                            href="#"
                                            id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                            @endif
                            <div class="lang_form" id="default-form">
                                <div class="form-group ">
                                <label class="input-label" for="exampleFormControlInput1">{{ translate('messages.restaurant_name') }} ({{translate('messages.default')}})</label>
                                    <input type="text" name="name[]" class="form-control"  placeholder="{{ translate('messages.Ex:_ABC_Company') }} " maxlength="191" value="{{$restaurant?->getRawOriginal('name')}}"  oninvalid="document.getElementById('en-link').click()">
                                </div>
                                <input type="hidden" name="lang[]" value="default">

                                <div>
                                    <label class="input-label" for="address">{{ translate('messages.restaurant_address') }} ({{translate('messages.default')}})</label>
                                    <textarea id="address" name="address[]" class="form-control h-70px" placeholder="{{ translate('messages.Ex:_House#94,_Road#8,_Abc_City') }} "  >{{$restaurant?->getRawOriginal('address')}}</textarea>
                                </div>
                            </div>


                                @if ($language)
                                @foreach(json_decode($language) as $lang)

                                @php
                                    $translate = [];
                                    if(isset($restaurant['translations']) && count($restaurant['translations'])){
                                    foreach($restaurant['translations'] as $t){
                                        if($t->locale == $lang && $t->key=="name"){
                                            $translate[$lang]['name'] = $t->value;
                                            }
                                        if($t->locale == $lang && $t->key=="address"){
                                            $translate[$lang]['address'] = $t->value;
                                            }
                                    }
                                    }
                                @endphp


                                <div class="d-none lang_form" id="{{$lang}}-form">

                                    <div class="form-group" >
                                        <label class="input-label" for="exampleFormControlInput1">{{ translate('messages.restaurant_name') }} ({{strtoupper($lang)}})</label>
                                        <input type="text" name="name[]" class="form-control"  placeholder="{{ translate('messages.Ex:_ABC_Company') }}  maxlength="191" value="{{isset($translate[$lang]['name']) ? $translate[$lang]['name'] : ''}}" oninvalid="document.getElementById('en-link').click()">
                                    </div>
                                    <input type="hidden" name="lang[]" value="{{$lang}}">

                                    <div>
                                        <label class="input-label" for="address">{{ translate('messages.restaurant_address') }} ({{strtoupper($lang)}})</label>
                                        <textarea id="address" name="address[]" class="form-control h-70px" placeholder="{{ translate('messages.Ex:_House#94,_Road#8,_Abc_City') }} "  >{{isset($translate[$lang]['address']) ? $translate[$lang]['address'] : ''}}</textarea>
                                    </div>
                                </div>
                                @endforeach

                            @endif

                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                <span>{{translate('Restaurant_Logo_&_Covers')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap flex-sm-nowrap __gap-12px">
                                <label class="__custom-upload-img mr-lg-5">

                                    <label class="form-label">
                                        {{ translate('logo') }} <span class="text--primary">({{ translate('1:1') }})</span>
                                    </label>
                                    <center>
                                        <img class="img--110 min-height-170px min-width-170px onerror-image" id="viewer"
                                             data-onerror-image="{{ dynamicAsset('public/assets/admin/img/upload.png') }}"
                                             src="{{ \App\CentralLogics\Helpers::onerror_image_helper(
                                            $restaurant->logo ?? '',
                                            dynamicStorage('storage/app/public/restaurant').'/'.$restaurant->logo ?? '',
                                            dynamicAsset('public/assets/admin/img/upload.png'),
                                            'restaurant/'
                                        ) }}"
                                             alt="logo image" />
                                    </center>
                                    <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                </label>

                                <label class="__custom-upload-img">

                                    <label class="form-label">
                                        {{ translate('Restaurant_Cover') }}  <span class="text--primary">({{ translate('2:1') }})</span>
                                    </label>
                                    <center>
                                        <img class="img--vertical min-height-170px min-width-170px onerror-image" id="coverImageViewer"
                                             data-onerror-image="{{ dynamicAsset('public/assets/admin/img/upload-img.png') }}"
                                             src="{{ \App\CentralLogics\Helpers::onerror_image_helper(
                                            $restaurant->cover_photo ?? '',
                                            dynamicStorage('storage/app/public/restaurant/cover').'/'.$restaurant->cover_photo ?? '',
                                            dynamicAsset('public/assets/admin/img/upload-img.png'),
                                            'restaurant/cover/'
                                        ) }}"
                                            alt="Fav icon" />
                                    </center>
                                    <input type="file" name="cover_photo" id="coverImageUpload"  class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                <span>{{translate('Restaurant_Info')}}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="input-label" for="tax">{{translate('messages.vat/tax (%)')}}</label>
                                    <input id="tax" type="number" name="tax" class="form-control h--45px"
                                        placeholder="{{ translate('messages.Ex:_100') }} " min="0" step=".01" required
                                        value="{{ $restaurant->tax }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative">
                                        <label class="input-label" for="tax">{{translate('Estimated_Delivery_Time_(_Min_&_Maximum_Time_)')}}</label>
                                        @php
                                            $timeTypeText = $delivery_time_type == 'min' ? translate('messages.minutes') : ($delivery_time_type == 'hours' ? translate('messages.hours') : $delivery_time_type);
                                        @endphp
                                        <input type="text" required id="time_view" value="{{$delivery_time_start}} {{ translate('messages.to') }} {{$delivery_time_end}} {{$timeTypeText}}"  class="form-control" readonly>
                                        <a href="javascript:void(0)" class="floating-date-toggler">&nbsp;</a>
                                        <span class="offcanvas"></span>
                                        <div class="floating--date" id="floating--date">
                                            <div class="card shadow--card-2">
                                                <div class="card-body">
                                                    <div class="floating--date-inner">
                                                        <div class="item">
                                                            <label class="input-label"
                                                                for="minimum_delivery_time">{{ translate('Minimum_Time') }}</label>
                                                            <input id="minimum_delivery_time" type="number" name="minimum_delivery_time" class="form-control h--45px" placeholder="{{ translate('messages.Ex :') }} 30"
                                                                pattern="^[0-9]{2}$" required min="1" value="{{$delivery_time_start ?? 10}}" >
                                                        </div>
                                                        <div class="item">
                                                            <label class="input-label"
                                                                for="maximum_delivery_time">{{ translate('Maximum_Time') }}</label>
                                                            <input id="maximum_delivery_time" type="number" name="maximum_delivery_time" class="form-control h--45px" placeholder="{{ translate('messages.Ex :') }} 60"
                                                                pattern="[0-9]{2}" required min="1" value="{{$delivery_time_end ?? 30}}">
                                                        </div>
                                                        <div class="item smaller">
                                                            <select name="delivery_time_type" id="delivery_time_type" class="custom-select">
                                                                <option value="min" {{$delivery_time_type=='min'?'selected':''}}>{{translate('messages.minutes')}}</option>
                                                                <option value="hours" {{$delivery_time_type=='hours'?'selected':''}}>{{translate('messages.hours')}}</option>
                                                                {{-- <option value="days" {{$delivery_time_type=='days'?'selected':''}}>{{translate('messages.days')}}</option> --}}
                                                            </select>
                                                        </div>
                                                        <div class="item smaller">
                                                            <button type="button" class="btn btn--primary deliveryTime">{{ translate('done') }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="cuisine">{{ translate('messages.cuisine') }}</label>
                                        <select name="cuisine_ids[]" id="cuisine" class="form-control h--45px min--45 js-select2-custom"
                                        multiple="multiple"  data-placeholder="{{ translate('messages.select_Cuisine') }}" >
                                    </option>
                                    @php
                                        $cuisine_array = \App\Models\Cuisine::where('status',1 )->get()->toArray();
                                        $selected_cuisine = isset($restaurant->cuisine) ? $restaurant->cuisine->pluck('id')->toArray() : [];
                                    @endphp
                                    @foreach ($cuisine_array as $cu)
                                        <option value="{{ $cu['id'] }}"
                                            {{ in_array($cu['id'], $selected_cuisine) ? 'selected' : '' }}>
                                            {{ $cu['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="input-label" for="choice_zones">{{ translate('messages.zone') }}
                                                <span data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.select_zone_for_map') }}"
                                                class="input-label-secondary"><img
                                                    src="{{ dynamicAsset('/public/assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ translate('messages.restaurant_lat_lng_warning') }}"></span>
                                                </label>
                                                <select name="zone_id" id="choice_zones"
                                            data-placeholder="{{ translate('messages.select_zone') }}"
                                            class="form-control h--45px js-select2-custom get_zone_data">
                                            @foreach (\App\Models\Zone::where('status',1 )->get(['id','name']) as $zone)
                                                @if (isset(auth('admin')->user()->zone_id))
                                                    @if (auth('admin')->user()->zone_id == $zone->id)
                                                        <option value="{{ $zone->id }}"
                                                            {{ $restaurant->zone_id == $zone->id ? 'selected' : '' }}>
                                                            {{ $zone->name }}</option>
                                                    @endif
                                                @else
                                                    <option value="{{ $zone->id }}"
                                                        {{ $restaurant->zone_id == $zone->id ? 'selected' : '' }}>
                                                        {{ $zone->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>



                                    <div class="form-group">
                                        <label class="input-label" for="latitude">{{ translate('messages.latitude') }}<span data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.This_point_marks_the_latitude_of_the_restaurant’s_location_on_the_map') }}"
                                                class="input-label-secondary"><img
                                                    src="{{ dynamicAsset('/public/assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ translate('messages.restaurant_lat_lng_warning') }}"></span></label>
                                        <input type="text" id="latitude" name="latitude" class="form-control h--45px disabled"
                                            placeholder="{{ translate('messages.Ex:_-94.22213') }}"  value="{{ $restaurant->latitude }}"required readonly>
                                    </div>
                                    <div class="form-group mb-md-0">
                                        <label class="input-label" for="longitude">{{ translate('messages.longitude') }}
                                                <span data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('messages.This_point_marks_the_longitude_of_the_restaurant’s_location_on_the_map') }}"
                                                class="input-label-secondary"><img
                                                    src="{{ dynamicAsset('/public/assets/admin/img/info-circle.svg') }}"
                                                    alt="{{ translate('messages.restaurant_lat_lng_warning') }}"></span>
                                                </label>
                                        <input type="text" name="longitude" class="form-control h--45px disabled" placeholder="{{ translate('messages.Ex:_103.344322') }} "
                                            id="longitude" value="{{ $restaurant->longitude }}"  required readonly>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input id="pac-input" class="controls rounded initial-8" title="{{translate('messages.search_your_location_here')}}" type="text" placeholder="{{translate('messages.search_here')}}"/>
                                    <div style="height: 370px !important" id="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h4 class="card-title m-0 d-flex align-items-center"> <span class="card-header-icon mr-2"><i class="tio-user"></i></span> <span>{{ translate('messages.owner_info') }}</span></h4>
                        </div>
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="f_name">{{ translate('messages.first_name') }}</label>
                                        <input id="f_name" type="text" name="f_name" class="form-control h--45px"
                                            placeholder="{{ translate('messages.Ex:_Jhon') }} "
                                            value="{{ $restaurant->vendor->f_name }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="l_name">{{ translate('messages.last_name') }}</label>
                                        <input id="l_name" type="text" name="l_name" class="form-control h--45px"
                                            placeholder="{{ translate('messages.Ex:_Doe') }} "
                                            value="{{ $restaurant->vendor->l_name }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="phone">{{ translate('messages.phone') }}</label>
                                        <input id="phone" type="tel" name="phone" class="form-control h--45px" placeholder="{{ translate('messages.Ex:_+9XXX-XXX-XXXX') }} "
                                        value="{{ $restaurant->phone }}"  required>
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
                            <input type="text" class="form-control" name="tags"  value="@foreach($restaurant->tags as $c) {{$c->tag.','}} @endforeach" placeholder="Enter tags" data-role="tagsinput">
                        </div>
                    </div>
                </div>
                @if(isset($mainCategories) && $mainCategories && count($mainCategories) > 0)
                <div class="col-lg-12">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <span class="card-header-icon mr-2"><i class="tio-date-range"></i></span>
                                <span>{{ translate('messages.Availability') }}</span>
                            </h5>
                            <div class="d-flex align-items-center gap-2">
                                <label class="mb-0">Toggle All Categories:</label>
                                <button type="button" class="btn btn-sm btn-primary" id="toggleAllCategories">Enable All</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="availability_data" id="availability_data" value="{{ $restaurant->new_available_times ?? '' }}">

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Main Category</th>
                                            @foreach($days as $day)
                                                <th class="text-center">{{ $day }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mainCategories as $mainCategory)
                                            <tr data-category-id="{{ $mainCategory->id }}" data-category-name="{{ strtolower($mainCategory->name) }}">
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <strong>{{ $mainCategory->name }}</strong>
                                                        <label class="toggle-switch toggle-switch-sm toggle-category-all-days" for="toggle_all_days_{{ $mainCategory->id }}">
                                                            <input type="checkbox"
                                                                   class="toggle-switch-input toggle-category-all-days-input"
                                                                   id="toggle_all_days_{{ $mainCategory->id }}"
                                                                   data-category-id="{{ $mainCategory->id }}">
                                                            <span class="toggle-switch-label">
                                                                <span class="toggle-switch-indicator"></span>
                                                            </span>
                                                        </label>
                                                        <small class="text-muted">All Days</small>
                                                    </div>
                                                </td>
                                                @foreach($days as $day)
                                                    @php
                                                        $dayLower = strtolower($day);
                                                        $categoryNameLower = strtolower($mainCategory->name);
                                                        $isChecked = isset($existingAvailability[$dayLower][$categoryNameLower]['available']) && $existingAvailability[$dayLower][$categoryNameLower]['available'];
                                                    @endphp
                                                    <td class="text-center">
                                                        <label class="toggle-switch toggle-switch-sm" for="toggle_{{ $mainCategory->id }}_{{ $dayLower }}">
                                                            <input type="checkbox"
                                                                   class="toggle-switch-input availability-toggle"
                                                                   id="toggle_{{ $mainCategory->id }}_{{ $dayLower }}"
                                                                   data-category-id="{{ $mainCategory->id }}"
                                                                   data-category-name="{{ $categoryNameLower }}"
                                                                   data-day="{{ $dayLower }}"
                                                                   {{ $isChecked ? 'checked' : '' }}>
                                                            <span class="toggle-switch-label">
                                                                <span class="toggle-switch-indicator"></span>
                                                            </span>
                                                        </label>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h4 class="card-title m-0 d-flex align-items-center"><span class="card-header-icon mr-2"><i class="tio-user"></i></span> <span>{{ translate('messages.account_info') }}</span></h4>
                        </div>
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="email">{{ translate('messages.email') }}</label>
                                        <input id="email" type="email" name="email" class="form-control h--45px" placeholder="{{ translate('messages.Ex:_Jhone@company.com') }} "
                                        value="{{ $restaurant->email }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="js-form-message form-group">
                                        <label class="input-label"
                                            for="signupSrPassword">{{ translate('messages.password') }}
                                            <span class="input-label-secondary ps-1" data-toggle="tooltip" title="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"><img src="{{dynamicAsset('public/assets/admin/img/info-circle.svg')}}" alt="{{ translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters') }}"></span>

                                        </label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="password"
                                                id="signupSrPassword"
                                                placeholder="{{ translate('messages.Ex:_8+_Character') }} ({{ translate('messages.Leave_blank_to_keep_current_password') }})"
                                                aria-label="{{translate('messages.password_length_8+')}}"
                                                data-msg=""
                                                data-hs-toggle-password-options='{
                                                                                    "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                                                                    "defaultClass": "tio-hidden-outlined",
                                                                                    "showClass": "tio-visible-outlined",
                                                                                    "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                                                                    }'>
                                            <div class="js-toggle-password-target-1 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="js-form-message form-group">
                                        <label class="input-label"
                                            for="signupSrConfirmPassword">{{ translate('messages.confirm_password') }}</label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="confirmPassword"
                                                id="signupSrConfirmPassword"
                                                placeholder="{{ translate('messages.Ex:_8+ Character') }} ({{ translate('messages.Leave_blank_to_keep_current_password') }})"
                                                aria-label="{{translate('messages.password_length_8+')}}"
                                                data-msg=""
                                                data-hs-toggle-password-options='{
                                                                                        "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                                                                        "defaultClass": "tio-hidden-outlined",
                                                                                        "showClass": "tio-visible-outlined",
                                                                                        "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                                                        }'>
                                            <div class="js-toggle-password-target-2 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-2 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container justify-content-end mt-3">
                <button id="reset_btn" type="button" class="btn btn--reset">{{translate('messages.reset')}}</button>
                <button type="submit" class="btn btn--primary h--45px"><i class="tio-save"></i> {{ translate('messages.save_information') }}</button>
            </div>
        </form>

    </div>

@endsection

@push('script_2')

    <script src="{{ dynamicAsset('public/assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script
            src="https://maps.googleapis.com/maps/api/js?key={{ \App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value }}&libraries=places&v=3.45.8">
    </script>
<script>
    "use strict";
    $(document).on('ready', function() {
        $('.offcanvas').on('click', function(){
            $('.offcanvas, .floating--date').removeClass('active')
        })
        $('.floating-date-toggler').on('click', function(){
            $('.offcanvas, .floating--date').toggleClass('active')
        })
    });

        $('#res_form').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        // Update availability data when toggles change
        function updateAvailabilityData() {
            const availability = {};
            const mainCategories = @json($mainCategories ?? []);
            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            // Initialize day structure
            days.forEach(day => {
                availability[day] = {};
            });

            // Populate availability data by day
            days.forEach(day => {
                mainCategories.forEach(category => {
                    const toggleId = `toggle_${category.id}_${day}`;
                    const toggle = document.getElementById(toggleId);
                    const isAvailable = toggle && toggle.checked;
                    const categoryName = category.name.toLowerCase();

                    // Use category name as key, not ID
                    availability[day][categoryName] = {
                        available: isAvailable,
                        main_category_id: category.id
                    };
                });
            });

            const availabilityJson = JSON.stringify(availability);
            document.getElementById('availability_data').value = availabilityJson;

            // console.log('Availability data updated:', availabilityJson); // Commented out for production
        }

        // Toggle all categories button
        document.addEventListener('DOMContentLoaded', function() {
            const toggleAllBtn = document.getElementById('toggleAllCategories');
            if (toggleAllBtn && typeof updateAvailabilityData === 'function') {
                let allEnabled = false;

                // Function to update "All Days" toggle for a category
                function updateCategoryAllDaysToggle(categoryId) {
                    const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
                    if (row) {
                        const dayToggles = row.querySelectorAll('.availability-toggle');
                        let allDaysEnabled = true;
                        dayToggles.forEach(toggle => {
                            if (!toggle.checked) {
                                allDaysEnabled = false;
                            }
                        });
                        const categoryAllDaysToggle = document.getElementById(`toggle_all_days_${categoryId}`);
                        if (categoryAllDaysToggle) {
                            categoryAllDaysToggle.checked = allDaysEnabled;
                        }
                    }
                }

                toggleAllBtn.addEventListener('click', function() {
                    allEnabled = !allEnabled;
                    const toggles = document.querySelectorAll('.availability-toggle');
                    toggles.forEach(toggle => {
                        toggle.checked = allEnabled;
                    });
                    toggleAllBtn.textContent = allEnabled ? 'Disable All' : 'Enable All';
                    updateAvailabilityData();

                    // Update all "All Days" toggles
                    const mainCategoriesList = @json($mainCategories ?? []);
                    mainCategoriesList.forEach(category => {
                        updateCategoryAllDaysToggle(category.id);
                    });
                });

                // Toggle all days for a specific category
                document.querySelectorAll('.toggle-category-all-days-input').forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const categoryId = this.getAttribute('data-category-id');
                        const row = document.querySelector(`tr[data-category-id="${categoryId}"]`);
                        if (row) {
                            const dayToggles = row.querySelectorAll('.availability-toggle');
                            const shouldEnable = this.checked;

                            dayToggles.forEach(dayToggle => {
                                dayToggle.checked = shouldEnable;
                            });
                            updateAvailabilityData();
                        }
                    });
                });

                // Individual toggle change
                document.querySelectorAll('.availability-toggle').forEach(toggle => {
                    toggle.addEventListener('change', function() {
                        const categoryId = this.getAttribute('data-category-id');
                        updateAvailabilityData();
                        // Update "All Days" toggle for this category
                        if (categoryId) {
                            updateCategoryAllDaysToggle(categoryId);
                        }
                    });
                });

                // Initialize "All Days" toggles based on current state
                const mainCategoriesList = @json($mainCategories ?? []);
                mainCategoriesList.forEach(category => {
                    updateCategoryAllDaysToggle(category.id);
                });

                // Initialize availability data on page load
                if (typeof updateAvailabilityData === 'function') {
                    updateAvailabilityData();
                }
            }
        });

        // Disable validation for password fields when empty
        $(document).on('ready', function() {
            // Wait a bit for HSValidation to initialize
            setTimeout(function() {
                $('#signupSrPassword, #signupSrConfirmPassword').on('input change', function() {
                    var password = $('#signupSrPassword').val();
                    var confirmPassword = $('#signupSrConfirmPassword').val();

                    if (!password || password.trim() === '') {
                        // Remove validation rules when empty
                        $(this).removeAttr('required').removeAttr('pattern');
                        $('#signupSrConfirmPassword').removeAttr('required').removeAttr('pattern');
                    }
                });
            }, 500);
        });

        // Ensure delivery time fields have values before submission
        function ensureDeliveryTimeFields() {
            var minTime = $('#minimum_delivery_time').val();
            var maxTime = $('#maximum_delivery_time').val();
            var timeType = $('#delivery_time_type').val();

            // If fields are empty, set default values
            if (!minTime || minTime === '') {
                $('#minimum_delivery_time').val('10');
                minTime = '10';
            }
            if (!maxTime || maxTime === '') {
                $('#maximum_delivery_time').val('30');
                maxTime = '30';
            }
            if (!timeType || timeType === '') {
                $('#delivery_time_type').val('min');
                timeType = 'min';
            }

            // Ensure max is greater than min
            if (parseInt(maxTime) <= parseInt(minTime)) {
                $('#maximum_delivery_time').val(parseInt(minTime) + 10);
            }
        }

        // Override form submission to handle password validation
        var formSubmitted = false;
        $('#res_form').on('submit', function(e) {
            // Prevent double submission
            if (formSubmitted) {
                return false;
            }

            // Ensure delivery time fields have values
            ensureDeliveryTimeFields();

            // Update availability data
            if (typeof updateAvailabilityData === 'function') {
                updateAvailabilityData();
            }

            // Handle password validation manually
            var password = $('#signupSrPassword').val();
            var confirmPassword = $('#signupSrConfirmPassword').val();

            // If password is empty, bypass validation and submit directly
            if (!password || password.trim() === '') {
                // Clear password fields to avoid server-side validation
                $('#signupSrPassword').val('');
                $('#signupSrConfirmPassword').val('');

                // Remove validation classes and errors
                $('#signupSrPassword, #signupSrConfirmPassword').removeClass('is-invalid invalid');
                $('.invalid-feedback').remove();

                // Temporarily remove js-validate class to bypass HSValidation
                var form = $('#res_form');
                var hasValidateClass = form.hasClass('js-validate');

                if (hasValidateClass) {
                    form.removeClass('js-validate');

                    // Submit form manually
                    formSubmitted = true;
                    form[0].submit();

                    return false; // Prevent default submission
                }
            } else {
                // If password is filled, ensure passwords match
                if (password !== confirmPassword) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#signupSrConfirmPassword').addClass('is-invalid');
                    toastr.error('{{ translate("messages.password_mismatch") }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    return false;
                }
            }

            // Allow normal form submission
            formSubmitted = true;
    });

    $(function() {
        $("#coba").spartanMultiImagePicker({
            fieldName: 'identity_image[]',
            maxCount: 5,
            rowHeight: '120px',
            groupClassName: 'col-lg-2 col-md-4 col-sm-4 col-6',
            maxFileSize: '',
            placeholderImage: {
                image: '{{ dynamicAsset('public/assets/admin/img/400x400/img2.jpg') }}',
                width: '100%'
            },
            dropFileLabel: "Drop Here",
            onAddRow: function(index, file) {

            },
            onRenderedPreview: function(index) {

            },
            onRemoveRow: function(index) {

            },
            onExtensionErr: function(index, file) {
                toastr.error('{{ translate('messages.please_only_input_png_or_jpg_type_file') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function(index, file) {
                toastr.error('{{ translate('messages.file_size_too_big') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        });
    });


        $("#customFileEg1").change(function() {
            readURL(this, 'viewer');
        });

        $("#coverImageUpload").change(function() {
            readURL(this, 'coverImageViewer');
        });
        $('#res_form').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        let myLatlng = {
            lat: {{ $restaurant->latitude }},
            lng: {{ $restaurant->longitude }}
        };
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 10,
            center: myLatlng,
        });
        var zonePolygon = null;
        let infoWindow = new google.maps.InfoWindow({
            content: "{{  translate('Click_the_map_to_get_Lat/Lng!') }}",
            position: myLatlng,
        });
        var bounds = new google.maps.LatLngBounds();

        function initMap() {
            // Create the initial InfoWindow.
            new google.maps.Marker({
                position: {
                    lat: {{ $restaurant->latitude }},
                    lng: {{ $restaurant->longitude }}
                },
                map,
                title: "{{ $restaurant->name }}",
            });
            infoWindow.open(map);
            // Create the search box and link it to the UI element.
            const input = document.getElementById("pac-input");
            //console.log(input);
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            let markers = [];
            searchBox.addListener("places_changed", () => {
                        const places = searchBox.getPlaces();
                        if (places.length == 0) {
                        return;
                        }
                        // Clear out the old markers.
                        markers.forEach((marker) => {
                        marker.setMap(null);
                        });
                        markers = [];
                        // For each place, get the icon, name and location.
                        const bounds = new google.maps.LatLngBounds();
                        places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        const icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25),
                        };
                        // Create a marker for each place.
                        markers.push(
                            new google.maps.Marker({
                            map,
                            icon,
                            title: place.name,
                            position: place.geometry.location,
                            })
                        );
                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                        });
                        map.fitBounds(bounds);
                    });
        }

        initMap();

    $('.get_zone_data').on('change',function (){
        let id = $(this).val();
        get_zone_data(id);
    })


    function get_zone_data(id){
        $.get({
                url: '{{ url('/') }}/admin/zone/get-coordinates/' + id,
                dataType: 'json',
                success: function(data) {
                    if (zonePolygon) {
                        zonePolygon.setMap(null);
                    }

                    zonePolygon = new google.maps.Polygon({
                        paths: data.coordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: 'white',
                        fillOpacity: 0,
                    });
                    zonePolygon.setMap(map);
                    // map.setCenter(data.center);


                    bounds = new google.maps.LatLngBounds();
                            zonePolygon.getPaths().forEach(function(path) {
                                path.forEach(function(latlng) {
                                    bounds.extend(latlng);
                                });
                            });
                            map.fitBounds(bounds);


                    google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                            position: mapsMouseEvent.latLng,
                            content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2),
                        });
                        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                        var coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });
                },
            });
            }

        $(document).on('ready', function() {
            var id = $('#choice_zones').val();
            $.get({
                url: '{{ url('/') }}/admin/zone/get-coordinates/' + id,
                dataType: 'json',
                success: function(data) {
                    if (zonePolygon) {
                        zonePolygon.setMap(null);
                    }
                    zonePolygon = new google.maps.Polygon({
                        paths: data.coordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: 'white',
                        fillOpacity: 0,
                    });
                    zonePolygon.setMap(map);
                    zonePolygon.getPaths().forEach(function(path) {
                        path.forEach(function(latlng) {
                            bounds.extend(latlng);
                            map.fitBounds(bounds);
                        });
                    });
                    map.setCenter(data.center);
                    google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                            position: mapsMouseEvent.latLng,
                            content: JSON.stringify(mapsMouseEvent.latLng.toJSON(),
                                null, 2),
                        });
                        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null,
                            2);
                        var coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });
                },
            });
        });

        $(document).on('ready', function() {
            get_zone_data({{ $restaurant->zone_id }});
        });
		$("#vendor_form").on('keydown', function(e){
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        })

        $('#reset_btn').click(function(){
            $('#name').val("{{ $restaurant->name }}");
            $('#tax').val("{{ $restaurant->tax }}");
            $('#address').val("{{ $restaurant->address }}");
            $('#minimum_delivery_time').val("{{ explode('-', $restaurant->delivery_time)[0] }}");
            $('#maximum_delivery_time').val("{{ explode('-', $restaurant->delivery_time)[1] }}");
            $('#viewer').attr('src', "{{ dynamicStorage('storage/app/public/restaurant/' . $restaurant->logo) }}");
            $('#customFileEg1').val(null);
            $('#coverImageViewer').attr('src', "{{ dynamicStorage('storage/app/public/restaurant/cover/' . $restaurant->cover_photo) }}");
            $('#coverImageUpload').val(null);
            $('#choice_zones').val({{$restaurant->zone_id}}).trigger('change');
            $('#f_name').val("{{ $restaurant->vendor->f_name }}");
            $('#l_name').val("{{ $restaurant->vendor->l_name }}");
            $('#phone').val("{{ $restaurant->phone }}");
            $('#email').val("{{ $restaurant->email }}");


        })

    $('.deliveryTime').click(function(){
        var min = $("#minimum_delivery_time").val();
        var max = $("#maximum_delivery_time").val();
        var typeValue = $("#delivery_time_type").val();
        var typeText = typeValue === 'min' ? '{{ translate("messages.minutes") }}' : (typeValue === 'hours' ? '{{ translate("messages.hours") }}' : typeValue);
        $("#floating--date").removeClass('active');
        $("#time_view").val(min+' {{ translate("messages.to") }} '+max+' '+typeText);

    })
    </script>
@endpush
