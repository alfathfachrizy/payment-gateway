<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class paymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('demo-payment',[
            'tittle' => 'Payment Demo',
            'order' => $request->session()->get('demo_order')
        ]);
    }

    public function pay(Request $request)
    {
        $order = $request->session()->get('demo_order');
        if($order){
            if($order['status'] == 'paid'){
                return to_route('demo-payment.index');
            }

            return redirect()->away($order['payment_url']);
        }
        $ordercode = 'Demo-'.Str::uuid();
        $amaount = 10000;
        try {
            $this->configMidtrans();
            $payment = Snap::createTransaction([
                'transaction_details' => [
                    'order_id' => $ordercode,
                    'gross_amount' => $amaount
                ]
            ]);
        }catch(\Exception $e){
            return back()->with('error', $e->getMessage());
        }

        $request->session()->put('demo_order',[
            'order_code' => $ordercode,
            'amount' => $amaount,
            'status' => 'pending',
            'payment_url' => $payment->redirect_url
        ]);
        return redirect()->away($payment->redirect_url);
    }

    public function check(Request $request)
    {
        $order = $request->session()->get('demo_order');
        if(!$order){
            return to_route('demo-payment.index')
            ->with('message','buat transaksi terlebih dulu');
        }
        try {
            $result = Transaction::status($order['code']);
        }catch(\Exception $e){
            report($e);
            return to_route('demo-payment.index')->with('messages', 'erorrrrrrrrrrrr'());
        }

        $sameOrder = $result->order_id == $order['code'];
        $sameAmount = $result->gross_amount == $order['amount'];

        if (!$sameOrder || !$sameAmount) {
            return to_route('demo-payment.index')
            ->with('message', 'tidak valid');
        }
        $midtransStatus = $result->transaction_status ?? 'unknow';
        if ($midtransStatus === 'settlement'|| $midtransStatus === 'capture') {
            $order['status'] = 'paid';
            $request->session()->put('demo_order', $order);
        }
        return to_route('demo-payment.index')->with('message', 'transaksi status berhasil');
    }


    private function configMidtrans()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = false;
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
