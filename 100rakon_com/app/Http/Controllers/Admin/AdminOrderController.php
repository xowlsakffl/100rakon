<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Order;
use App\OrderItem;
use App\OrderHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $state = $request->state;
        $searchOption = $request->search_option;
        $searchText = $request->search_text;

        if($searchText){
            $orderData = Order::where([
                [function($query) use ($searchOption, $searchText){
                    $query->orWhere($searchOption, 'LIKE', '%'.$searchText.'%')->get();
                }]
            ])->paginate(10);
            $orderData->appends(['search_option' => $searchOption, 'search_text'=>$searchText]);
        }

        if($state || $state === "0"){
            $orderData = Order::where('state', $state)->latest()->paginate(10);
            $orderData->appends(['state' => $state]);
        }

        if(!$searchText && !$state && $state !== "0"){
            $orderData = Order::latest()->paginate(10);
        }

        return view('admin.orders.order_list', compact('orderData'))->with('i', (request()->input('page', 1) - 1) * 5);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($odx)
    {
        $orderData = Order::where('odx', $odx)->first();
        $orderItems = OrderItem::where('odx', $odx)->paginate(10);
        $orderHistories = OrderHistory::where('odx', $odx);

        return view('admin.orders.order_show', compact(['orderData', 'orderItems', 'orderHistories']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($odx)
    {
        $orderData = Order::where('odx', $odx)->first();

        return view('admin.orders.order_edit', compact('orderData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $odx)
    {
        $request->validate([
            'order_number' => 'required',
            'total_amount' => 'numeric',
            'use_point' => 'numeric',
            'pay_amount' => 'numeric',
        ],
        [
            'order_number.required' => '?????? ????????? ??????????????????.',
            'total_amount.numeric' => '????????? ??????????????????.',
            'use_point.numeric' => '????????? ??????????????????.',
            'pay_amount.numeric' => '????????? ??????????????????.',
        ]);

        $data = request()->except(['_token', '_method', 'old_state']);
        $credentials = array_filter($data);
        Order::where('odx', $odx)->update($credentials);

        //???????????? ??? ???????????? ??????
        if($request['state'] != $request['old_state'])
        {
            $history['odx'] = $request['odx'];
            $history['kind'] = '??????';
            $history['content'] = "[".Auth::user()->name."]?????? [".Order::getStateText($request['state'])."]??? ????????????";
            OrderHistory::create($history);
        }

        flash('????????? ?????????????????????.')->success();
        return redirect()->route('admin.orders.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($odx)
    {
        $order = Order::where('odx' ,$odx);
        $order->update(['state' => 0]);
        $order->delete();

        //???????????? ??????
        $history['odx'] = $odx;
        $history['kind'] = '??????';
        $history['content'] = "[".Auth::user()->name."]?????? [".Order::getStateText(0)."]??? ????????????";
        OrderHistory::create($history);

        flash('????????? ?????????????????????.')->success();
        return redirect()->route('admin.orders.index');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function historyCreate(Request $request)
    {
        $request = request()->all();

        $history['odx'] = $request['odx'];
        $history['kind'] = '??????';
        $history['content'] = "[".Auth::user()->name."]?????? ?????? : ".$request['content'];
        OrderHistory::create($history);

        flash('?????? ????????? ?????????????????????.')->success();
        return redirect('/admin/orders/'.$request['odx']);
    }
}
