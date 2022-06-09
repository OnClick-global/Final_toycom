@foreach($products as $key=>$product)
    <tr>
        <td>{{$key+1}}</td>
        <td>
                                        <span class="d-block font-size-sm text-body">
                                             <a href="{{route('admin.product.view',[$product['id']])}}">
                                               @if(app()->getLocale() == 'ar') {{$product['name_ar']}} @else {{$product['name']}} @endif
                                             </a>
                                        </span>
        </td>
        <td>
            <div style="height: 100px; width: 100px; overflow-x: hidden;overflow-y: hidden">
                <img src="{{asset('storage/app/public/product')}}/{{json_decode($product['image'],true)[0]}}" style="width: 100px"
                     onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'">
            </div>
        </td>
        <td>
            @if($product['status']==1)
                <div style="padding: 10px;border: 1px solid;cursor: pointer"
                     onclick="location.href='{{route('admin.product.status',[$product['id'],0])}}'">
                    <span class="legend-indicator bg-success"></span>{{trans('messages.active')}}
                </div>
            @else
                <div style="padding: 10px;border: 1px solid;cursor: pointer"
                     onclick="location.href='{{route('admin.product.status',[$product['id'],1])}}'">
                    <span class="legend-indicator bg-danger"></span>{{trans('messages.disabled')}}
                </div>
            @endif
        </td>
        <td>{{$product['price']." ".\App\CentralLogics\Helpers::currency_symbol()}}</td>
        <td>
            <!-- Dropdown -->
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button"
                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    <i class="tio-settings"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item"
                       href="{{route('admin.product.edit',[$product['id']])}}">{{trans('messages.edit')}}</a>
                    <a class="dropdown-item" href="javascript:"
                       onclick="form_alert('product-{{$product['id']}}','Want to delete this item ?')">{{trans('messages.delete')}}</a>
                    <form action="{{route('admin.product.delete',[$product['id']])}}"
                          method="post" id="product-{{$product['id']}}">
                        @csrf @method('delete')
                    </form>
                </div>
            </div>
            <!-- End Dropdown -->
        </td>
    </tr>
@endforeach