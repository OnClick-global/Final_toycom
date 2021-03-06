@extends('layouts.admin.app')

@section('title','Settings')

@push('css_or_js')

@endpush

@section('content')
    @if(UserCan('edit_settings','admin'))
        <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{trans('messages.restaurant')}} {{trans('messages.setup')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.update-setup')}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    @php($name=\App\Model\BusinessSetting::where('key','restaurant_name')->first()->value)
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.restaurant')}} {{trans('messages.name')}}</label>
                                <input type="text" name="restaurant_name" value="{{$name}}" class="form-control"
                                       placeholder="New Restaurant" required>
                            </div>
                        </div>
                        @php($currency_code=\App\Model\BusinessSetting::where('key','currency')->first()->value)
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.currency')}}</label>
                                <select name="currency" class="form-control js-select2-custom">
                                    @foreach(\App\Model\Currency::orderBy('currency_code')->get() as $currency)
                                        <option
                                            value="{{$currency['currency_code']}}" {{$currency_code==$currency['currency_code']?'selected':''}}>
                                            {{$currency['currency_code']}} ( {{$currency['currency_symbol']}} )
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @php($phone=\App\Model\BusinessSetting::where('key','phone')->first()->value)
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.phone')}}</label>
                                <input type="text" value="{{$phone}}"
                                       name="phone" class="form-control"
                                       placeholder="" required>
                            </div>
                        </div>
                        @php($whatsapp=\App\Model\BusinessSetting::where('key','watsapp')->first()->value)
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.whatsapp')}}</label>
                                <input type="number" value="{{$whatsapp}}"
                                       name="watsapp" class="form-control"
                                       placeholder="" required>
                            </div>
                        </div>
                        @php($email=\App\Model\BusinessSetting::where('key','email_address')->first()->value)
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.email')}}</label>
                                <input type="email" value="{{$email}}"
                                       name="email" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                        @php($address=\App\Model\BusinessSetting::where('key','address')->first()->value)
                        <div class="col-md-3 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.address')}}</label>
                                <input type="text" value="{{$address}}"
                                       name="address" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>

                        @php($mov=\App\Model\BusinessSetting::where('key','minimum_order_value')->first()->value)
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.lowest_value_order')}} ( {{\App\CentralLogics\Helpers::currency_symbol()}} )</label>
                                <input type="number" min="1" value="{{$mov}}"
                                       name="minimum_order_value" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            @php($sp=\App\Model\BusinessSetting::where('key','self_pickup')->first()->value)
                            <div class="form-group">
                                <label>{{trans('messages.self_pickup')}}</label><small style="color: red">*</small>
                                <div class="input-group input-group-md-down-break">
                                    <!-- Custom Radio -->
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1" name="self_pickup"
                                                   id="sp1" {{$sp==1?'checked':''}}>
                                            <label class="custom-control-label" for="sp1">{{trans('messages.on')}}</label>
                                        </div>
                                    </div>
                                    <!-- End Custom Radio -->

                                    <!-- Custom Radio -->
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="0" name="self_pickup"
                                                   id="sp2" {{$sp==0?'checked':''}}>
                                            <label class="custom-control-label" for="sp2">{{trans('messages.off')}}</label>
                                        </div>
                                    </div>
                                    <!-- End Custom Radio -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-12">
                            @php($delivery=\App\Model\BusinessSetting::where('key','delivery_charge')->first()->value)
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.delivery_value')}}</label>
                                <input type="number" min="1" max="10000" name="delivery_charge" value="{{$delivery}}"
                                       class="form-control" placeholder="100" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            @php($ev=\App\Model\BusinessSetting::where('key','email_verification')->first()->value)
                            <div class="form-group">
                                <label>{{trans('messages.email_verification')}}</label><small style="color: red">*</small>
                                <div class="input-group input-group-md-down-break">
                                    <!-- Custom Radio -->
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1" name="email_verification"
                                                   id="ev1" {{$ev==1?'checked':''}}>
                                            <label class="custom-control-label" for="ev1">{{trans('messages.on')}}</label>
                                        </div>
                                    </div>
                                    <!-- End Custom Radio -->

                                    <!-- Custom Radio -->
                                    <div class="form-control">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="0" name="email_verification"
                                                   id="ev2" {{$ev==0?'checked':''}}>
                                            <label class="custom-control-label" for="ev2">{{trans('messages.off')}}</label>
                                        </div>
                                    </div>
                                    <!-- End Custom Radio -->
                                </div>
                            </div>
                        </div>

                        @php($footer_text=\App\Model\BusinessSetting::where('key','footer_text')->first()->value)
                        <div class="col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.footer')}} {{trans('messages.text')}}</label>
                                <input type="text" value="{{$footer_text}}"
                                       name="footer_text" class="form-control" placeholder=""
                                       required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            @php($dinar_points=\App\Model\BusinessSetting::where('key','dinar_points')->first()->value)
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.dinar_points')}}</label>
                                <input type="number" min="1"  name="dinar_points" value="{{$dinar_points}}"
                                       class="form-control" placeholder="100" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            @php($points_dinar=\App\Model\BusinessSetting::where('key','points_dinar')->first()->value)
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.points_dinar')}}</label>
                                <input type="number"  name="points_dinar" value="{{$points_dinar}}"
                                       class="form-control" placeholder="100" required>
                            </div>
                        </div>
                    </div>

                    @php($logo=\App\Model\BusinessSetting::where('key','logo')->first()->value)
                    <div class="form-group">
                        <label>{{trans('messages.logo')}}</label><small style="color: red">* ( {{trans('messages.ratio')}} 3:1 )</small>
                        <div class="custom-file">
                            <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="custom-file-label" for="customFileEg1">{{trans('messages.choose')}} {{trans('messages.file')}}</label>
                        </div>
                        <hr>
                        <center>
                            <img style="height: 100px;border: 1px solid; border-radius: 10px;" id="viewer"
                                 onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                                 src="{{asset('storage/app/public/restaurant/'.$logo)}}" alt="logo image"/>
                        </center>
                    </div>
                    @php($main_image=\App\Model\BusinessSetting::where('key','main_image')->first()->value)
                    <div class="form-group">
                        <label>{{trans('messages.main_image')}}</label><small style="color: red">*</small>
                        <div class="custom-file">
                            <input type="file" name="main_image" id="customFileEg2" class="custom-file-input"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="custom-file-label" for="customFileEg2">{{trans('messages.choose')}} {{trans('messages.file')}}</label>
                        </div>
                        <hr>
                        <center>
                            <img style="height: 100px;border: 1px solid; border-radius: 10px;" id="viewer2"
                                 onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                                 src="{{asset('storage/app/public/restaurant/'.$main_image)}}" alt="logo image"/>
                        </center>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">{{trans('messages.submit')}}</button>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('script_2')
    <script>
        $("#customFileEg1").change(function () {
            readURL(this);
        });
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }




        $("#customFileEg2").change(function () {
            readURL2(this);
        });
        function readURL2(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer2').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
