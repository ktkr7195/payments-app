<?php
namespace App\Http\Controllers;

use App\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Shop;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $payments = Payment::query();
        $shops = Shop::all();

        // 名前でフィルタリング
        $name = $request->get('customer');
        if (!is_null($name)) {
            $payments->where('customer', 'like', "%{$name}%");
        }

        // 伝票番号でフィルタリング
        $order_no = $request->get('order_no');
        if (!is_null($order_no)) {
            $payments->where('order_no', $order_no);
        }

        // 値段でフィルタリング
        $price = $request->get('price');
        $price_operator = $request->get('price_operator') ?? '=';
        if (!is_null($price)) {
            $payments->where('price', $price_operator, $price);
        }

        // 自分が発行した伝票のみ表示する
        $self_issued_payments = $request->get('self_issued_payments') == 1;
        if ($self_issued_payments) {
            $payments->where('user_id', \Auth::user()->id);
        }

        // 今回のリクエストデータをセッションに保存
        $request->flash();

        return view('home')->with(['payments' => $payments->get(), 'shops' => $shops]);
    }
    /**
     * 特定のIDの支払い情報を表示する
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $payment = Payment::find($id);

        return view('detail')->with('payment', $payment);
    }
    /**
     * 特定のIDの支払い情報を削除する
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);
        $order_no = $payment->order_no;
        $payment->delete();

        \Session::flash('status', $order_no . 'を削除しました。');

        return redirect()->back();
    }

    public function create(Request $request)
    {
        //  必須項目をバリデーションする
        $request->validate([
           'customer' => 'required',
           'order_no' => 'required',
           'price' => 'required',
        ]);



        $payment= new Payment();
        $payment->customer= $request['customer'];
        $payment->order_no= substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 8);;
        $payment->price= $request['price'];
        $payment->note= $request['note'];
        $payment->method= $request['method'];;
        $payment->issue_date= Carbon::now();
        $payment->user_id= \Auth::user()->id; // user_idを指定することでそれに紐付いているuserにアクセスできる。detail.bladeで$payment->user->nameでidにひも付いているnameがゲットできる。
        $payment->shop_id= $request['shop_id']; // 上と同じ
        $payment->save();

        return redirect()->back();
    }
}