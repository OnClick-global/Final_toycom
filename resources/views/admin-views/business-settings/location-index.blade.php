@extends('layouts.admin.app')

@section('title','Settings')

@push('css_or_js')

@endpush

@section('content')
    @if(UserCan('edit_settings','admin'))
        <div class="content container-fluid">
    @php($branch_count=\App\Model\Branch::count())
    <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{trans('messages.location')}} {{trans('messages.coverage')}} {{trans('messages.setup')}}</h1>
                    <span class="badge badge-soft-danger" style="text-align: left">
                        This location setup is for your Main branch. Carefully set your restaurant location and coverage area. If you want to ignore the coverage area then keep the input box empty.<br>
                        You can ignore this when you have only the default branch and you don't want coverage area.
                    </span>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.business-settings.update-location')}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    @php($data=\App\Model\Branch::first())
                    <div class="row">
                        {{--<div class="col-md-12 col-12">
                            <label class="toggle-switch d-flex align-items-center mb-3" for="customSwitch1">
                                <input type="checkbox" name="status" class="toggle-switch-input" value="1" id="customSwitch1" {{$data['status']==1?'checked':''}}>
                                <span class="toggle-switch-label">
                                <span class="toggle-switch-indicator"></span>
                              </span>
                                <span class="toggle-switch-content">
                                <span class="d-block">Status</span>
                              </span>
                            </label>
                        </div>--}}
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.latitude')}}</label>
                                <input type="text" value="{{$data['latitude']}}"
                                       name="latitude" class="form-control"
                                       placeholder="Ex : -94.22213" {{$branch_count>1?'required':''}}>
                            </div>
                        </div>

                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="exampleFormControlInput1">{{trans('messages.longitude')}}</label>
                                <input type="text" value="{{$data['longitude']}}"
                                       name="longitude" class="form-control"
                                       placeholder="Ex : 103.344322" {{$branch_count>1?'required':''}}>
                            </div>
                        </div>

                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label class="input-label" for="">
                                    <i class="tio-info-outined"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       title="This value is the radius from your restaurant location, and customer can order food inside  the circle calculated by this radius."></i>
                                    {{trans('messages.coverage')}} ( {{trans('messages.km')}} )
                                </label>
                                <input type="number" value="{{$data['coverage']}}"
                                       name="coverage" class="form-control" placeholder="Ex : 3" {{$branch_count>1?'required':''}}>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">{{trans('messages.update')}}</button>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('script_2')
    <script>
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
    </script>
@endpush
