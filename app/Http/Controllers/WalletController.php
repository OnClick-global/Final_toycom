<?php

namespace App\Http\Controllers;

use App\Model\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Model\Order;
use App\User;
use Illuminate\Support\Facades\Redirect;
use Brian2694\Toastr\Facades\Toastr;

class WalletController extends Controller
{
    public function pay(Request $request)
    {
        $order = Order::with(['details'])->where(['id' => session('order_id')])->first();
        if($order){
            $user  = User::find($order->user_id);
            return view('wallet',compact('order','user'));
        }else{
            return \redirect()->route('payment-fail');
        }
    }
    public function exchange(Request $request)
    {
        $user  = User::find($request->user);
        $order = Order::with(['details'])->where(['id' => $request->order])->first();
        $points_dinar = BusinessSetting::where('key', 'points_dinar')->first()->value;  
        return view('exchange',compact('order','user','points_dinar'));
    }
    public function Doexchange(Request $request)
    {
        $user = User::find($request->user);
        if ($user->my_points >= $request->points) {
            $points_dinar = BusinessSetting::where('key', 'points_dinar')->first()->value;
            $money = $request->points / $points_dinar;    //ex.  700/100 = 7 dinar
            $user->my_points = $user->my_points - $request->points;  //ex.  700 - 700 = 0 points
            $user->my_money = $user->my_money + $money;  // ex.  0 + 7 = 7 dinar
            $user->save();
            Toastr::success('تم تحويل النقاط بنجاح يمكنك الدفع الأن');
            return Redirect::back();            
        }else{
            Toastr::error('نقاطك لا تكفى');
            return Redirect::back();          
        }
    }
    public function paynow(Request $request)
    {
        DB::table('orders')
            ->where('id', session('order_id'))
            ->update(['order_status' => 'confirmed', 'payment_status' => 'paid', 'transaction_reference' => 'wallet']);      
        $order = Order::with(['details'])->where(['id' => session('order_id')])->first();
        $user = User::find($order->user_id);
        if ($user->my_money >= $order->order_amount) {
            $user->my_money = $user->my_money-$order->order_amount;
            $user->save();
            return \redirect()->route('payment-success');
        }else{
            return \redirect()->route('payment-fail');
        }
    }
}
