@extends('layouts.admin.app')

@section('title',translate('messages.Rejected_Restaurant_List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')

    <div class="content container-fluid">

        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-filter-list"></i> {{ translate('messages.Rejected_Restaurant_List')}} </h1>
            <!-- Resturent List -->
                    <!-- Resturent Card Wrapper -->
        </div>
        <!-- End Page Header -->

        <div class="d-flex flex-wrap mb-4 __gap-15px">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <!-- Nav -->
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link "  aria-disabled="true" href="{{route('admin.restaurant.pending')}}">{{translate('messages.Pending_Requests')}}</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.restaurant.approved')}}"  >{{translate('Approved List')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{route('admin.restaurant.denied')}}"  >{{translate('messages.Rejected_Request')}}</a>
                    </li>
                    
                </ul>
                <!-- End Nav -->
            </div>
            <!--<div class="page-header-select-wrapper flex-grow-1">-->
            <!--    <div class="select-item">-->
                    <!-- Veg/NonVeg filter -->
            <!--        <select name="type"-->
            <!--                data-url="{{url()->full()}}" data-filter="type"-->
            <!--                data-placeholder="{{translate('messages.all')}}" class="form-control js-select2-custom set-filter">-->
            <!--            <option selected disabled>{{translate('messages.select_veg/non_veg')}}</option>-->
            <!--            <option value="all" {{$type=='all'?'selected':''}}>{{translate('messages.all')}}</option>-->
            <!--            @if ($toggle_veg_non_veg)-->
            <!--            <option value="veg" {{$type=='veg'?'selected':''}}>{{translate('messages.veg')}}</option>-->
            <!--            <option value="non_veg" {{$type=='non_veg'?'selected':''}}>{{translate('messages.non_veg')}}</option>-->
            <!--            @endif-->
            <!--        </select>-->

                <!-- End Veg/NonVeg filter -->
            <!--    </div>-->
            <!--    <div class="select-item">-->
                    <!-- Veg/NonVeg filter -->
            <!--        <select name="restaurant_model"-->
            <!--                data-url="{{url()->full()}}" data-filter="restaurant_model"-->
            <!--                data-placeholder="{{translate('messages.Business_Model')}}" class="form-control js-select2-custom set-filter">-->
            <!--            <option selected disabled>{{translate('messages.select_type')}}</option>-->
            <!--            <option value="all" {{$typ=='all'?'selected':''}}>{{translate('messages.all')}}</option>-->
            <!--            <option value="commission" {{$typ=='commission'?'selected':''}}>{{translate('messages.Commission')}}</option>-->
            <!--            <option value="subscribed" {{$typ=='subscribed'?'selected':''}}>{{translate('messages.Subscribed')}}</option>-->
            <!--            <option value="unsubscribed" {{$typ=='unsubscribed'?'selected':''}}>{{translate('messages.Unsubscribed')}}</option>-->

            <!--        </select>-->

                <!-- End Veg/NonVeg filter -->
            <!--    </div>-->
            <!--    @if(!isset(auth('admin')->user()->zone_id))-->
            <!--        <div class="select-item">-->
            <!--            <select name="zone_id" class="form-control js-select2-custom set-filter"-->
            <!--                    data-url="{{url()->full()}}" data-filter="zone_id">-->
            <!--                <option selected disabled>{{translate('messages.select_zone')}}</option>-->
            <!--                <option value="all">{{translate('messages.all_zones')}}</option>-->
            <!--                @foreach(\App\Models\Zone::orderBy('name')->get(['id','name']) as $z)-->
            <!--                    <option-->
            <!--                        value="{{$z['id']}}" {{isset($zone) && $zone->id == $z['id']?'selected':''}}>-->
            <!--                        {{$z['name']}}-->
            <!--                    </option>-->
            <!--                @endforeach-->
            <!--            </select>-->
            <!--        </div>-->
            <!--    @endif-->
            <!--</div>-->
        </div>








        <!-- Resturent List -->
        <div class="row gx-2 gx-lg-3 mt-3" >
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Card Header -->

                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h3 class="card-title">{{translate('messages.restaurants_list')}}
                                <span class="badge badge-soft-dark ml-2" id="itemCount">{{\App\Models\RestaurantPendingList::where("status","=","rejected")->where("confirm_status",0)->count();}}</span>
                            </h3>
                            <form class="my-2 ml-auto mr-sm-2 mr-xl-4 ml-sm-auto flex-grow-1 flex-grow-sm-0">
                                <!-- Search -->

                                <div class="input--group input-group input-group-merge input-group-flush">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                            placeholder="{{ translate('Ex: Phone or Email') }}" aria-label="{{translate('messages.search')}}" value="{{$search_value ?? ''}}">
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>

                                </div>
                                <!-- End Search -->
                            </form>
                        </div>
                    </div>
                    <!-- Card Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom resturant-list-table">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false

                               }'>
                            <thead class="thead-light">
                                     <tr>
                                       <th class="text-uppercase w-90px">{{translate('messages.sl')}}</th>
                                       <th class="initial-58">{{translate('messages.restaurant_info')}}</th>
                                       <th class="w-230px text-center">{{translate('Email')}} </th>
                                        <th class="w-230px text-center">{{translate('Phone')}} </th>
                                       <th class="w-130px">{{translate('Address')}}</th>
                                       <th class="w-100px">{{translate('messages.status')}}</th>
                                       <th class="text-center w-60px">{{translate('messages.action')}}</th>
                                   </tr>
                            </thead>

                            <tbody id="set-rows">
                                @php 
                                    $sno = 1;
                                @endphp
                                
                                @if(isset($restaurants) && count($restaurants) > 0)
                                        @foreach($restaurants as $val)
                                            <tr>
                                                  <td>
                                          {{$sno++;}}
                                    </td>
                                    <td>
                                          <span class="d-block owner--name text-center">
                                           {{!empty($val->f_name) ? $val->f_name : '';}} {{!empty($val->l_name) ? $val->l_name : '';}}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-block font-size-sm text-center">
                                            {{!empty($val->email) ? $val->email : '';}}
                                        </div>
                                    </td>
                                     <td>
                                        <div class="d-block font-size-sm text-center">
                                            {{!empty($val->mobile_no) ? $val->mobile_no : '';}}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-block font-size-sm text-start">
                                          {{!empty($val->address) ? $val->address : '';}}
                                          </div>
                                    </td>
                                    <td>
                                      @if($val->status == 'pending')
                                            <span class="badge badge-soft-danger">{{ translate('messages.pending') }}</span>
                                        @elseif($val->status == 'rejected')
                                            <span class="badge badge-soft-danger">{{ translate('Rejected') }}</span>
                                        @endif

                                    </td>
                                    <td>
                                         <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn request_alert"
                                               data-url="{{ route('admin.restaurant.status_change', ['id' => $val->id, 'status' => 'approved']) }}"
                                               data-message="{{ translate('messages.you_want_to_approve_this_application') }}"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('Approve') }}"
                                               href="javascript:;"> <i class="tio-done font-weight-bold"></i> </a>



                                                    
                                                <!--    <a class="btn btn-sm btn--danger btn-outline-danger action-btn request_alert"-->
                                                <!--data-toggle="tooltip" data-placement="top" title="{{translate('Deny')}}"-->
                                                <!--data-url="{{ route('admin.restaurant.status_change', ['id' => $val->id, 'status' => 'rejected']) }}" data-message="{{translate('messages.you_want_to_deny_this_application')}}"-->
                                                <!--    href="javascript:"><i-->
                                                <!--    class="tio-clear"></i></a>-->
                                        </div>
                                    </td>
                                            </tr>
                                        @endforeach
                                    @endif
                             
                            </tbody>
                        </table>
                        @if(count($restaurants) === 0)
                        <div class="empty--data">
                            <img src="{{dynamicAsset('/public/assets/admin/img/empty.png')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                        @endif
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- Resturent List -->
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $('.status_change_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            status_change_alert(url, message, event)
        })

        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '{{ translate('Are_you_sure?') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('no') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href=url;
                }
            })
        }
        $('.request_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            request_alert(url, message)
        })
        function request_alert(url, message) {
            Swal.fire({
                title: "{{translate('messages.are_you_sure_?')}}",
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: "{{translate('messages.no')}}",
                confirmButtonText: "{{translate('messages.yes')}}",
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('keyup', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>

@endpush
