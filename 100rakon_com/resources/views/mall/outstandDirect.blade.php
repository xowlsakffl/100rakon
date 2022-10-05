@extends('layouts.mall')
@section('content')
<div id="mall_basket">
    <div class="title"><div class="square"></div><div class="text">특별상품 바로구매</div></div>
    <div class="header">
        <div class="spec">제품</div>
        <div class="bar"></div>
        <div class="quantity">수량</div>
        <div class="bar"></div>
        <div class="delivery">배송</div>
        <div class="bar"></div>
        <div class="total">합계</div>
    </div>
    <form name="basket">
    <div class="item">
        <div class="item_thumb"><img src="<?=Storage::url('public/'.$outstand->thumbnail->real_name)?>" /></div>
        <div class="item_spec">
            <div class="item_title">{{ $outstand->title }}</div>
            <div class="item_price">
                <div class="discount">{{ number_format($outstand->price) }}</div>
                <div class="won">원</div>
                {{-- <div class="normals">{{ number_format($outstand->price_normal) }}</div> --}}
            </div>
            <input type="hidden" name="osdx[]" value="<?=$outstand->osdx?>" />
            <input type="hidden" name="price[]" value="<?=$outstand->price?>" />
        </div>
        <div class="item_quantity">
            <div class="basic_count_panel">
                <button type="button" onClick="quantity_minus(0)">-</button>
                <input type="text" name="quantity[]" value="{{ number_format($quantity) }}" readonly>
                <button type="button" onClick="quantity_plus(0)">+</button>
            </div>
        </div>
        <div class="item_delivery">
            <?php
            $delivery_origin_cost = $outstand->delivery_origin_cost;
            if($delivery_origin_cost == 0)
            {
                $delivery_cost_show = '무료';
            }
            else
            {
                $delivery_cost_show = number_format($delivery_origin_cost).'원';
            }
            ?>
            <div>
                {{ $delivery_cost_show }}
                @if($delivery_origin_cost == 0)
                    <input type="hidden" name="delivery_selected_policy[]" value="무료" />
                @else
                    <br/>
                    <select onChange="delivery_policy_change(0, this.value)">
                        <option value="택배선불">택배선불</option>
                        <option value="택배착불">택배착불</option>
                    <select>
                    <input type="hidden" name="delivery_selected_policy[]" value="택배선불" />
                @endif
            </div>
            <input type="hidden" name="delivery_origin_cost[]" value="<?=$delivery_origin_cost?>" />
            <input type="hidden" name="delivery_pay[]" value="<?=$delivery_origin_cost?>" />
            <input type="hidden" name="need_delivery_info" value="<?=$outstand->need_delivery_info?>" />
        </div>
        <div class="item_total">
            <div>
                <span id="id_item_toal0">
                    {{ number_format(($outstand->price * $quantity) + $delivery_origin_cost) }}원
                </span><br/>
            </div>
            <input type="hidden" name="item_amount[]" value="{{ (($outstand->price * $quantity) + $delivery_origin_cost) }}" />
        </div>
    </div>
    @csrf
    </form>

    <div class="decesion">
        <div class="total_amount">최종 결제 금액 : <span id="id_total_amount">0</span>원</div>
        <button type="button" class="cPointer" onClick="check_submit()">주문하기</button>
    </div>


<script>
var basketForm = $("form[name=basket]");
$(document).ready(function()
{
    // console.log(basketForm.serialize());
    calculate_total_amount();
})

// 폼 서브밋
function check_submit()
{
    if($("input[name='osdx[]']").length == 0)
    {
        alert("적어도 1개의 상품은 등록해 주시기 바랍니다.");
    }else{
        document.forms['basket'].method = "post";
        document.forms['basket'].action = "/outstand/order";
        document.forms['basket'].submit();
    }
}

// 최종 금액 산출
function calculate_total_amount()
{
    let itemLength = $("input[name='osdx[]']").length;
    let total_amount = 0;
    for(var i = 0; i < itemLength; i++)
    {
        total_amount = total_amount + parseInt($("input[name='item_amount[]']").eq(i).val());
    }
    $("#id_total_amount").html(gen.addComma(total_amount));
    console.log(gen.addComma(total_amount));
}

// 아이템 최종 금액 산출
function calculate_item_amount(index)
{
    let price = parseInt($("input[name='price[]']").eq(index).val());
    let quantity = parseInt($("input[name='quantity[]']").eq(index).val());
    let delivery_selected_policy = $("input[name='delivery_selected_policy[]']").eq(index).val();
    let delivery_origin_cost = parseInt($("input[name='delivery_origin_cost[]']").eq(index).val());
    let delivery_pay = parseInt($("input[name='delivery_pay[]']").eq(index).val());
    let item_amount = (price * quantity) + delivery_pay;
    // 택배비 지불 부분 점검
    if(delivery_selected_policy == "택배착불")
    {
        item_amount = (price * quantity);
    }

    let total_amount_text = gen.addComma(item_amount);
    $("input[name='item_amount[]']").eq(index).val(item_amount);
    $('#id_item_toal' + index).html(total_amount_text + '원');

    calculate_total_amount();
}

// 아이템 택배지불방법 변경
function delivery_policy_change(index, thisValue)
{
    $("input[name='delivery_selected_policy[]']").eq(index).val(thisValue);
    calculate_item_amount(index);
}

// 아이템 수량 더하기
function quantity_plus(index)
{
    let target = $("input[name='quantity[]']").eq(index);
    target.val(parseInt(target.val()) + 1);
    calculate_item_amount(index);
}

// 아이템 수량 빼기
function quantity_minus(index)
{
    let target = $("input[name='quantity[]']").eq(index);
    if(parseInt(target.val()) > 1)
    {
        target.val(parseInt(target.val()) - 1);
        calculate_item_amount(index);
    }
}
</script>
</div>
@endsection
