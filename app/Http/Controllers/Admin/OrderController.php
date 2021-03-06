<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use App\Model\TimeSlot;
use App\Model\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use ArPHP\I18N\Arabic;
use App\User;

class OrderController extends Controller
{
    function list($status) {
        if (session()->has('branch_filter') == false) {
            session()->put('branch_filter', 0);
        }

        Order::where(['checked' => 0])->update(['checked' => 1]);

        if (session('branch_filter') == 0) {
            if ($status != 'all') {
                $orders = Order::with(['customer', 'branch', 'time_slot'])
                    ->latest()->where(['order_status' => $status])
                    ->where(function($e){
                        if (session('user_filter') != 0) {
                            $e->where('user_id',session('user_filter'));
                        }
                    })
                    ->paginate(25);
            } else {
                $orders = Order::with(['customer', 'branch', 'time_slot'])
                            ->where(function($e){
                                if (session('user_filter') != 0) {
                                    $e->where('user_id',session('user_filter'));
                                }
                            })
                ->latest()->paginate(20);
            }
        } else {
            if ($status != 'all') {
                $orders = Order::with(['customer', 'branch', 'time_slot'])
                    ->latest()->where(['order_status' => $status, 'branch_id' => session('branch_filter')])
                    ->paginate(25);
            } else {
                $orders = Order::with(['customer', 'branch', 'time_slot'])->where(['branch_id' => session('branch_filter')])->latest()->paginate(20);
            }
        }
        // dd($orders);
        return view('admin-views.order.list', compact('orders', 'status'));

    }

    public function details($id)
    {
        $order = Order::with('details')->where(['id' => $id])->first();

//        return $order;
        if (isset($order)) {
            return view('admin-views.order.order-view', compact('order'));
        } else {
            Toastr::info('No more orders!');
            return back();
        }
    }

    public function search(Request $request)
    {

        $key = explode(' ', $request['search']);
        $orders = Order::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('order_status', 'like', "%{$value}%")
                    ->orWhere('transaction_reference', 'like', "%{$value}%");
            }
        })->latest()->paginate(2);

        return response()->json([
            'view' => view('admin-views.order.partials._table', compact('orders'))->render(),
        ]);
    }
    public function date_search(Request $request)
    {
        $dateData = ($request['dateData']);

        $orders = Order::where(['delivery_date' => $dateData])->latest()->paginate(10);
        // $timeSlots = $orders->pluck('time_slot_id')->unique()->toArray();
        // if ($timeSlots) {

        //     $timeSlots = TimeSlot::whereIn('id', $timeSlots)->get();
        // } else {
        //     $timeSlots = TimeSlot::orderBy('id')->get();

        // }
        // dd($orders);

        return response()->json([
            'view' => view('admin-views.order.partials._table', compact('orders'))->render(),
            // 'timeSlot' => $timeSlots
        ]);

    }
    public function time_search(Request $request)
    {

        $orders = Order::where(['time_slot_id' => $request['timeData']])->where(['delivery_date' => $request['dateData']])->get();
        // dd($orders)->toArray();

        return response()->json([
            'view' => view('admin-views.order.partials._table', compact('orders'))->render(),
        ]);

    }

    public function status(Request $request)
    {
        $order = Order::find($request->id);
        if ($order['delivery_man_id'] == null && $request->order_status == 'out_for_delivery') {
            Toastr::warning('Please assign delivery man first!');
            return back();
        }

        
        if ($request->order_status == 'returned' || $request->order_status == 'failed' || $request->order_status == 'canceled') {
            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 1) {
                    $product = Product::find($detail['product_id']);
                    $type = json_decode($detail['variation'])[0]->type;
                    $var_store = [];
                    foreach (json_decode($product['variations'], true) as $var) {
                        if ($type == $var['type']) {
                            $var['stock'] += $detail['quantity'];
                        }
                        array_push($var_store, $var);
                    }
                    Product::where(['id' => $product['id']])->update([
                        'variations'  => json_encode($var_store),
                        'total_stock' => $product['total_stock'] + $detail['quantity'],
                    ]);
                    OrderDetail::where(['id' => $detail['id']])->update([
                        'is_stock_decreased' => 0,
                    ]);
                }
            }
        } else {

            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 0) {
                    $product = Product::find($detail['product_id']);

                    //check stock
                    foreach ($order->details as $c) {
                        $product = Product::find($c['product_id']);
                        $type = json_decode($c['variation'])[0]->type;
                        foreach (json_decode($product['variations'], true) as $var) {
                            if ($type == $var['type'] && $var['stock'] < $c['quantity']) {
                                Toastr::error('Stock is insufficient!');
                                return back();
                            }
                        }
                    }

                    $type = json_decode($detail['variation'])[0]->type;
                    $var_store = [];
                    foreach (json_decode($product['variations'], true) as $var) {
                        if ($type == $var['type']) {
                            $var['stock'] -= $detail['quantity'];
                        }
                        array_push($var_store, $var);
                    }
                    Product::where(['id' => $product['id']])->update([
                        'variations'  => json_encode($var_store),
                        'total_stock' => $product['total_stock'] - $detail['quantity'],
                    ]);
                    OrderDetail::where(['id' => $detail['id']])->update([
                        'is_stock_decreased' => 1,
                    ]);
                }
            }
        }

        $order->order_status = $request->order_status;
        $order->save();
        $fcm_token = $order->customer->cm_firebase_token;
        $value = Helpers::order_status_update_message($request->order_status);
        try {
            if ($value) {
                $data = [
                    'title'       => 'Order',
                    'description' => $value,
                    'order_id'    => $order['id'],
                    'image'       => '',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }
        } catch (\Exception $e) {
            Toastr::warning('Push notification failed!');
        }
        if ($order->order_status != $request->order_status) {
            $user         = User::find($order->user_id);
            $dinar_points = BusinessSetting::where('key', 'dinar_points')->first()->value;
            $user->my_points = $user->my_points + intval(round($order->order_amount*$dinar_points));  //ex.  700 - 700 = 0 points
            $user->save();    
            Toastr::success('User Points updated!');
        }
        Toastr::success('Order status updated!');
        return back();
    }

    public function add_delivery_man($order_id, $delivery_man_id)
    {
        if ($delivery_man_id == 0) {
            return response()->json([], 401);
        }
        $order = Order::find($order_id);
        $order->delivery_man_id = $delivery_man_id;
        $order->save();

        $fcm_token = $order->delivery_man->fcm_token;
        $value = Helpers::order_status_update_message('del_assign');
        try {
            if ($value) {
                $data = [
                    'title'       => 'Order',
                    'description' => $value,
                    'order_id'    => $order['id'],
                    'image'       => '',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }
        } catch (\Exception $e) {

        }

        Toastr::success('Order deliveryman added!');
        return response()->json([], 200);
    }

    public function payment_status(Request $request)
    {
        $order = Order::find($request->id);
        if ($request->payment_status == 'paid' && $order['transaction_reference'] == null && $order['payment_method'] != 'cash_on_delivery') {
            Toastr::warning('Add your payment reference code first!');
            return back();
        }
        $order->payment_status = $request->payment_status;
        $order->save();
        Toastr::success('Payment status updated!');
        return back();
    }

    public function update_shipping(Request $request, $id)
    {
        $request->validate([
            'contact_person_name'   => 'required',
            'address_type'          => 'required',
            'contact_person_number' => 'required',
            'address'               => 'required',
        ]);

        $address = [
            'contact_person_name'   => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'address_type'          => $request->address_type,
            'address'               => $request->address,
            'longitude'             => $request->longitude,
            'latitude'              => $request->latitude,
            'created_at'            => now(),
            'updated_at'            => now(),
        ];

        DB::table('customer_addresses')->where('id', $id)->update($address);
        Toastr::success('Payment status updated!');
        return back();
    }
    public function update_time_slot(Request $request)
    {
        if ($request->ajax()) {
            $order = Order::find($request->id);
            $order->time_slot_id = $request->timeSlot;
            $order->save();
            $data = $request->timeSlot;

            return response()->json($data);
        }
    }
    public function printCard(Request $request)
    {

        $card = OrderDetail::find($request->id);
        $Arabic = new Arabic('Glyphs');
        $body = $Arabic->utf8Glyphs($card->message_body);
        $from = $Arabic->utf8Glyphs($card->message_from);
        $to   = $Arabic->utf8Glyphs($card->message_to);

        $img = Image::make(public_path('003.png'));
        // write text at position
        $img->text($body, 290, 230, function($font) {
            $font->file(public_path('DejaVuSans.ttf'));
            $font->size(13);
            //$font->color('#fdf6e3');
            $font->align('right');
            $font->valign('top');
            //$font->angle(45);
        });
        $img->text($from, 200, 325, function($font) {
            $font->file(public_path('DejaVuSans.ttf'));
            $font->size(13);
            //$font->color('#fdf6e3');
            $font->align('right');
            $font->valign('top');
            //$font->angle(45);
        });
        $img->text($to, 200, 370, function($font) {
            $font->file(public_path('DejaVuSans.ttf'));
            $font->size(13);
            //$font->color('#fdf6e3');
            $font->align('right');
            $font->valign('top');
            //$font->angle(45);
        });
        $img->save('public/NewCard.png');
        return url('public/NewCard.png');
    }
    public function update_deliveryDate(Request $request)
    {
        if ($request->ajax()) {
            $order = Order::find($request->id);
            $order->delivery_Date = $request->deliveryDate;
            $order->save();
            $data = $request->deliveryDate;

            return response()->json($data);
        }
    }

    public function generate_invoice($id)
    {
        $order = Order::where('id', $id)->first();
        return view('admin-views.order.invoice-2', compact('order'));
    }

    public function add_payment_ref_code(Request $request, $id)
    {
        Order::where(['id' => $id])->update([
            'transaction_reference' => $request['transaction_reference'],
        ]);

        Toastr::success('Payment reference code is added!');
        return back();
    }

    public function branch_filter($id)
    {
        session()->put('branch_filter', $id);
        return back();
    }
    public function user_filter($id)
    {
        session()->put('user_filter', $id);
        return back();
    }
}
