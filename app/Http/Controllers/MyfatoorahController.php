<?php

namespace App\Http\Controllers;

use App\User;
use Moyasar\Moyasar;
use App\CentralLogics\Helpers;
use App\Model\Currency;
use App\Model\Order;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Alnazer\KnetPayment\Knet;

class MyfatoorahController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function paywith(Request $request)
    {
        $order = Order::withoutGlobalScopes()->with(['details'])->where(['id' => session('order_id')])->first();
        $user = User::findOrFail(session('customer_id'));
        $root_url = $request->root();
        $path = 'https://api.myfatoorah.com/v2/ExecutePayment';
        $paymentMethodId = 1;
        $token = "bearer 0Tz_NJtzLMmoD0QX_4XQuUvBop2rSrm00msozj2ZnPWbwwth5iswOCMNgklSPDJw9KTR0EXFkytjjfeZDSiGHlMt6PTho503FZbq-ak3K1afPqpv9vd-tWZA08-ezVz2-1PTLYxbYuFDRc7UJoiSznTAhE7vQL3iA_wZfsNH4iUG1e9iJH4WjQB6326EcgJ-o8amefLrNbs66u7Us1CFMJXP_Wr4U_FmSDy-Bhj4AlQ2dqk2a_a5skkoXQFh41gxVJHiDTcLiKoKRezwSnKLQkHzilnWT_jA3Spyg7ZOzkB2ati6oUNgf3PpxqlPz3NBrO_xCdTnFbXKPH3brlBxbsHBNTKLq2q_oeXP0SxoVTjU6NWz12fmjD8novAMhyS-WgZqrz_ntqn0DVAcb-5UoHfyZqR18UlTDIULm9A4pIGeAQrpmjxwF5zHkIjlIEALYHiTBCtK6k90rWV8SFo7sRJqYR9JCXgNaLhHSVRBn9qeGv8xiIKujDiupSGHncRJWl0mm1Zw8xMeLtIvEQdXUHOPrZshH1GEpDoXW5Hk0Eplqp3u1LqBBDvaxHYQ3ZCxlGCZz39OlvvT6kdQwKHF4bY2gUClyNa-EVh846VxfwZU4DvDzL6hZ02eN1rzj8Wk87SNthvwoiPhrKYt0gSXF3e4JErTUPNbBXkwY3XTdw6aEDSsOwQK_Iv0OH4CX6rRQkOtuw";
        $headers = array(
            'Authorization:' . $token,
            'Content-Type:application/json'
        );
        $call_back_url = $root_url . "/myfatoorah-oncomplate?order_id=" . $order->id . "&user_id=" . $user->id;
        $error_url = $root_url . "/payment-fail";
        $fields = array(
            'paymentMethodId' => $paymentMethodId,
            "CustomerName" => $user->name,
            "NotificationOption" => "LNK",
            "InvoiceValue" => $order->order_amount,
            "CallBackUrl" => $call_back_url,
            "ErrorUrl" => $error_url,
            "Language" => "AR",
            "CustomerEmail" => $user->email
        );
        $payload = json_encode($fields);
        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, $path);
        curl_setopt($curl_session, CURLOPT_POST, true);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURLOPT_IPRESOLVE);
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);
        $result = curl_exec($curl_session);
        curl_close($curl_session);
        $result = json_decode($result);
        //dd($result);
        if ($result) {
            $order->payment_method = 'K-Net';
            $order->save();
            return redirect()->to($result->Data->PaymentURL);
        } else {
            print_r($request["errors"]);
        }
    }

    public function paywithvisa(Request $request)
    {
        $order = Order::withoutGlobalScopes()->with(['details'])->where(['id' => session('order_id')])->first();
        $user = User::findOrFail(session('customer_id'));
        $root_url = $request->root();
        $path = 'https://api.myfatoorah.com/v2/ExecutePayment';
        $paymentMethodId = 2;
        $token = "bearer 0Tz_NJtzLMmoD0QX_4XQuUvBop2rSrm00msozj2ZnPWbwwth5iswOCMNgklSPDJw9KTR0EXFkytjjfeZDSiGHlMt6PTho503FZbq-ak3K1afPqpv9vd-tWZA08-ezVz2-1PTLYxbYuFDRc7UJoiSznTAhE7vQL3iA_wZfsNH4iUG1e9iJH4WjQB6326EcgJ-o8amefLrNbs66u7Us1CFMJXP_Wr4U_FmSDy-Bhj4AlQ2dqk2a_a5skkoXQFh41gxVJHiDTcLiKoKRezwSnKLQkHzilnWT_jA3Spyg7ZOzkB2ati6oUNgf3PpxqlPz3NBrO_xCdTnFbXKPH3brlBxbsHBNTKLq2q_oeXP0SxoVTjU6NWz12fmjD8novAMhyS-WgZqrz_ntqn0DVAcb-5UoHfyZqR18UlTDIULm9A4pIGeAQrpmjxwF5zHkIjlIEALYHiTBCtK6k90rWV8SFo7sRJqYR9JCXgNaLhHSVRBn9qeGv8xiIKujDiupSGHncRJWl0mm1Zw8xMeLtIvEQdXUHOPrZshH1GEpDoXW5Hk0Eplqp3u1LqBBDvaxHYQ3ZCxlGCZz39OlvvT6kdQwKHF4bY2gUClyNa-EVh846VxfwZU4DvDzL6hZ02eN1rzj8Wk87SNthvwoiPhrKYt0gSXF3e4JErTUPNbBXkwY3XTdw6aEDSsOwQK_Iv0OH4CX6rRQkOtuw";
        $headers = array(
            'Authorization:' . $token,
            'Content-Type:application/json'
        );
        $call_back_url = $root_url . "/myfatoorah-oncomplate?order_id=" . $order->id . "&user_id=" . $user->id;
        $error_url = $root_url . "/payment-fail";
        $fields = array(
            'paymentMethodId' => $paymentMethodId,
            "CustomerName" => $user->name,
            "NotificationOption" => "LNK",
            "InvoiceValue" => $order->order_amount,
            "CallBackUrl" => $call_back_url,
            "ErrorUrl" => $error_url,
            "Language" => "AR",
            "CustomerEmail" => $user->email
        );
        $payload = json_encode($fields);
        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, $path);
        curl_setopt($curl_session, CURLOPT_POST, true);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_session, CURLOPT_IPRESOLVE, CURLOPT_IPRESOLVE);
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, $payload);
        $result = curl_exec($curl_session);
        curl_close($curl_session);
        $result = json_decode($result);
        //dd($result);
        if ($result) {
            $order->payment_method = 'K-Net';
            $order->save();
            return redirect()->to($result->Data->PaymentURL);
        } else {
            print_r($request["errors"]);
        }
    }

    public function oncomplate(Request $request)
    {
        $order = Order::withoutGlobalScopes()->where('id', $request->order_id)
            ->update(['order_status' => 'confirmed', 'payment_status' => 'paid','temp'=>0, 'transaction_reference' => $request['paymentid']]);
        if ($order) {
            return \redirect()->route('payment-success');
        } else {
            return \redirect()->route('payment-fail');
        }
    }

    public function error(Request $request)
    {
        return dd($request);
    }
    // public function getPaymentStatus(Request $request)
    // {
    //     if($request->status == "paid"){
    //         DB::table('orders')
    //             ->where('transaction_reference', $request->id)
    //             ->update(['order_status' => 'confirmed', 'payment_status' => 'paid', 'transaction_reference' => $request->id]);
    //         $order = Order::where('transaction_reference', $request->id)->first();
    //         if ($order->callback != null) {
    //             return redirect($order->callback . '/success');
    //         }else{
    //             return \redirect()->route('payment-success');
    //         }
    //     }
    //     $order = Order::where('transaction_reference', $payment_id)->first();
    //     if ($order->callback != null) {
    //         return redirect($order->callback . '/fail');
    //     }else{
    //         return \redirect()->route('payment-fail');
    //     }
    // }
//     public function oncomplate(Request $request,Order $order)
//     {
//         DB::table('orders')
//         ->where('id', $order->id)
//         ->update([
//             'transaction_reference' => $request->id,
//             'payment_method' => 'paypal',
//             'order_status' => 'failed',
//             'updated_at' => now()
//         ]);
//     }
}
