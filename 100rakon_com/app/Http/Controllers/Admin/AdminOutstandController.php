<?php

namespace App\Http\Controllers\Admin;

use App\File;
use App\Http\Controllers\Controller;
use App\Outstand;
use App\OutstandCategory;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminOutstandController extends Controller
{
    use UploadTrait;
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
        $category = $request->category;

        if($searchText){
            $outstandData = Outstand::where([
                [function($query) use ($searchOption, $searchText){
                    $query->orWhere($searchOption, 'LIKE', '%'.$searchText.'%')->get();
                }]
            ])->paginate(10);
            $outstandData->appends(['search_option' => $searchOption, 'search_text'=>$searchText]);
        }

        if($state || $state === "0"){
            $outstandData = Outstand::where('state', $state)->latest()->paginate(10);
            $outstandData->appends(['state' => $state]);
        }

        if($category){
            $outstandData = Outstand::where('oscdx', $category)->latest()->paginate(10);
            $outstandData->appends(['category' => $category]);
        }

        if(!$searchText && !$state && $state !== "0" && !$category){
            $outstandData = Outstand::latest()->paginate(10);
        }

        $categories = OutstandCategory::all();

        return view('admin.outstands.outstand_list', compact(['outstandData', 'categories']))->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = OutstandCategory::all();

        return view('admin.outstands.outstand_create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = [
            'oscdx' => $request->category,
            'sequence' => $request->sequence,
            'title' => $request->title,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'content' => $request->content,
            'hit' => $request->hit,
            'state' => $request->state,
            'price_normal' => $request->price_normal,
            'delivery_origin_cost' => $request->delivery_origin_cost,
            'supply' => $request->supply,
            'need_delivery_info' => $request->need_delivery_info,
        ];

        $request->validate([
            'title' => 'required|string',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
            'content' => 'required|string',
            'hit' => 'numeric',
            'need_delivery_info' => 'boolean'
        ],
        [
            'title.required' => '?????? ????????? ??????????????????.',
            'title.string' => '????????????, ????????? ????????? ??? ????????????.',
            'name.required' => '???????????? ??????????????????.',
            'name.string' => '????????????, ????????? ????????? ??? ????????????.',
            'price.required' => '????????? ??????????????????.',
            'price.numeric' => '????????? ??????????????????.',
            'quantity.required' => '??????????????? ??????????????????.',
            'quantity.numeric' => '????????? ??????????????????.',
            'content.required' => '??????????????? ??????????????????.',
            'content.string' => '????????????, ????????? ????????? ??? ????????????.',
            'hit.numeric' => '????????? ??????????????????.',
        ]);

        $outstand = Outstand::create($data);

        if($request->hasFile('image')){
            $image = $this->imageUpload($request->file('image'), null, null, $outstand->osdx);
            $outstand->thumbnail()->create($image);
        }

        flash('??????????????? ?????????????????????.')->success();
        return redirect()->route('admin.outstands.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($osdx)
    {
        $outstandData = Outstand::where('osdx', $osdx)->first();

        return view('admin.outstands.outstand_show', compact('outstandData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($osdx)
    {
        $outstandData = Outstand::where('osdx', $osdx)->first();
        $categories = OutstandCategory::all();

        return view('admin.outstands.outstand_edit', compact(['outstandData', 'categories']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $osdx)
    {
        $data = [
            'oscdx' => $request->category,
            'sequence' => $request->sequence,
            'title' => $request->title,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'content' => $request->content,
            'hit' => $request->hit,
            'state' => $request->state,
            'price_normal' => $request->price_normal,
            'delivery_origin_cost' => $request->delivery_origin_cost,
            'supply' => $request->supply,
            'need_delivery_info' => $request->need_delivery_info,
        ];

        $request->validate([
            'title' => 'required|string',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
            'content' => 'required|string',
            'hit' => 'numeric',
            'need_delivery_info' => 'boolean'
        ],
        [
            'title.required' => '?????? ????????? ??????????????????.',
            'title.string' => '????????????, ????????? ????????? ??? ????????????.',
            'name.required' => '???????????? ??????????????????.',
            'name.string' => '????????????, ????????? ????????? ??? ????????????.',
            'price.required' => '????????? ??????????????????.',
            'price.numeric' => '????????? ??????????????????.',
            'quantity.required' => '??????????????? ??????????????????.',
            'quantity.numeric' => '????????? ??????????????????.',
            'content.required' => '??????????????? ??????????????????.',
            'content.string' => '????????????, ????????? ????????? ??? ????????????.',
            'hit.numeric' => '????????? ??????????????????.',
        ]);

        $outstand = Outstand::where('osdx', $osdx)->first();

        if($request->hasFile('image')){
            $image = $this->imageUpload($request->file('image'), null, null, $outstand->osdx);
            if(!$outstand->thumbnail){
                $outstand->thumbnail()->create($image);
            }else{
                if(Storage::disk('public')->exists($outstand->thumbnail->real_name)){
                    Storage::disk('public')->delete($outstand->thumbnail->real_name);
                }
                $outstand->thumbnail()->update($image);
            };
        }

        $outstand->update($data);

        flash('??????????????? ?????????????????????.')->success();
        return redirect()->route('admin.outstands.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($osdx)
    {
        $outstand = Outstand::where('osdx' ,$osdx)->first();

        if($outstand->thumbnail){
            if(Storage::disk('public')->exists($outstand->thumbnail->real_name)){
                Storage::disk('public')->delete($outstand->thumbnail->real_name);
            }
            $outstand->thumbnail->delete();
        };

        $outstand->delete();

        flash('??????????????? ?????????????????????.')->success();
        return redirect()->route('admin.outstands.index');
    }

    public function removeImage($fdx)
    {
        $image = File::where('fdx', $fdx)->first();

        if(Storage::disk('public')->exists($image->real_name)){
            Storage::disk('public')->delete($image->real_name);
        }

        $outstandId = $image->outstand->osdx;

        $image->delete();

        flash('???????????? ?????????????????????.')->success();
        return redirect()->route('admin.outstands.edit', ['osdx' => $outstandId]);
    }
}
