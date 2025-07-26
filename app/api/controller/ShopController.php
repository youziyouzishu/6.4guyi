<?php

namespace app\api\controller;

use app\admin\model\ShopBanner;
use app\admin\model\ShopCart;
use app\admin\model\ShopClass;
use app\admin\model\ShopGoods;
use app\admin\model\ShopGoodsSku;
use app\admin\model\ShopOrder;
use app\admin\model\ShopOrderItem;
use app\admin\model\ShopOrderItemComment;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use support\Redis;
use support\Request;
use support\Response;
use Webman\RedisQueue\Client;

class ShopController extends Base
{
    /**
     * 商城轮播图
     * @param Request $request
     * @return \support\Response
     */
    function getBannerList(Request $request)
    {
        $banners = ShopBanner::orderByDesc('weight')->get();
        return $this->success('成功', $banners);
    }

    /**
     * 商城分类
     * @param Request $request
     * @return \support\Response
     */
    function getClassList(Request $request)
    {
        $class = ShopClass::orderByDesc('weight')->get();
        return $this->success('成功', $class);
    }

    /**
     * 商品列表
     * @param Request $request
     * @return \support\Response
     */
    function getGoodsList(Request $request)
    {
        $keyword = $request->input('keyword');
        $class_id = $request->input('class_id');
        $order = $request->input('order');#1综合  2销量 3价格升序 4价格降序
        $goods = ShopGoods::normal()
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->when(!empty($class_id), function ($query) use ($class_id) {
                return $query->where('class_id', $class_id);
            })
            ->where(function ($query) use ($order) {
                if ($order == 1) {
                    $query->orderByDesc('weight');
                }
                if ($order == 2) {
                    $query->orderByDesc('sales');
                }
                if ($order == 3) {
                    $query->orderBy('price');
                }
            })
            ->orderByDesc('weight')
            ->paginate()
            ->items();
        return $this->success('成功', $goods);
    }

    /**
     * 商品详情
     * @param Request $request
     * @return \support\Response
     */
    function getGoodsDetail(Request $request)
    {
        $id = $request->input('id');
        $goods = ShopGoods::normal()->with(['sku'])->find($id);
        return $this->success('成功', $goods);
    }

    /**
     * 添加购物车
     * @param Request $request
     * @return \support\Response
     */
    function addCart(Request $request)
    {
        $sku_id = $request->input('sku_id');
        $num = $request->input('num');
        $cart = ShopCart::where('user_id', $request->user_id)->where('sku_id', $sku_id)->first();
        if ($cart) {
            $cart->num += $num;
            $cart->save();
        } else {
            ShopCart::create([
                'user_id' => $request->user_id,
                'sku_id' => $sku_id,
                'num' => $num,
            ]);
        }
        return $this->success('成功');
    }

    /**
     * 编辑购物车
     * @param Request $request
     * @return \support\Response
     */
    function updateCart(Request $request)
    {
        $id = $request->input('id');
        $num = $request->input('num');
        $cart = ShopCart::find($id);
        if ($cart) {
            $cart->num = $num;
            $cart->save();
        }
        return $this->success('成功');
    }

    /**
     * 查看购物车
     * @param Request $request
     * @return \support\Response
     */
    function indexCart(Request $request)
    {
        $carts = ShopCart::where('user_id', $request->user_id)->with(['sku'])->get();
        return $this->success('成功', $carts);
    }


    /**
     * 删除购物车
     * @param Request $request
     * @return \support\Response
     */
    function delCart(Request $request)
    {
        $ids = $request->input('ids');
        ShopCart::destroy($ids);
        return $this->success('成功');
    }

    /**
     * 预创建订单
     * @param Request $request
     * @return Response
     */
    function preCreateOrder(Request $request)
    {
        $itemsInput = $request->input('items', []);
        //{
        //  "items": [
        //    { "sku_id": 101, "num": 2 },
        //    { "sku_id": 102, "num": 1 }
        //  ]
        //}
        if (empty($itemsInput)) {
            return $this->fail('请选择商品');
        }
        $total_freight = 0;
        $total_goods_price = 0;

        $items = [];

        foreach ($itemsInput as $item) {
            $sku = ShopGoodsSku::with('goods')->findOrFail($item['sku_id']);
            $num = intval($item['num']);

            if ($sku->stock < $num) {
                return $this->fail("【{$sku->goods->name}】库存不足");
            }
            $goods_amount = bcmul($sku->goods->discount_price, $num, 2);
            $freight = $sku->goods->freight;
            $pay_amount = bcadd((string)$goods_amount, (string)$freight, 2);
            $total_freight += $freight;
            $total_goods_price = bcadd((string)$total_goods_price, $goods_amount, 2);
            $items[] = [
                'sku_id' => $sku->id,
                'goods_id' => $sku->goods_id,
                'goods_name' => $sku->goods->name,
                'price' => $sku->goods->discount_price,
                'num' => $num,
                'goods_amount' => $goods_amount,
                'pay_amount' => $pay_amount,
                'freight' => $freight,
            ];
        }


        $total_price = bcadd((string)$total_goods_price, (string)$total_freight, 2);

        return $this->success('成功', [
            'total_goods_price' => $total_goods_price,
            'total_freight' => $total_freight,
            'total_pay_amount' => $total_price,
            'items' => $items,
        ]);
    }

    /**
     * 创建订单
     * @param Request $request
     * @return Response
     */
    function createOrder(Request $request)
    {
        $itemsInput = $request->input('items', []);
        $mark = $request->input('mark');
        if (empty($itemsInput)) {
            return $this->fail('请选择商品');
        }
        $total_freight = 0;
        $total_goods_price = 0;

        $items = [];

        $ordersn = Pay::generateOrderSn();

        foreach ($itemsInput as $item) {
            $sku = ShopGoodsSku::with('goods')->findOrFail($item['sku_id']);
            $num = intval($item['num']);

            if ($sku->stock < $num) {
                return $this->fail("【{$sku->goods->name}】库存不足");
            }
            $goods_amount = bcmul($sku->goods->discount_price, $num, 2);
            $freight = $sku->goods->freight;
            $pay_amount = bcadd((string)$goods_amount, (string)$freight, 2);
            $total_freight += $freight;
            $total_goods_price = bcadd((string)$total_goods_price, $goods_amount, 2);
            $items[] = [
                'sku_id' => $sku->id,
                'goods_id' => $sku->goods_id,
                'goods_name' => $sku->goods->name,
                'price' => $sku->goods->discount_price,
                'num' => $num,
                'goods_amount' => $goods_amount,
                'pay_amount' => $pay_amount,
                'freight' => $freight,
            ];
        }


        $total_price = bcadd((string)$total_goods_price, (string)$total_freight, 2);

        //创建订单
        $order = ShopOrder::create([
            'user_id' => $request->user_id,
            'ordersn' => $ordersn,
            'total_pay_amount' => $total_price,
            'total_goods_amount' => $total_goods_price,
            'total_freight' => $total_freight,
            'mark' => $mark,
        ]);
        $order->items()->createMany($items);
        Client::send('job', ['order_id' => $order->id, 'event' => 'goods_order_expire'], 60 * 15);
        return $this->success('成功', $order);
    }


    /**
     * 支付
     * @param Request $request
     * @return Response
     */
    function pay(Request $request)
    {
        $ordersn = $request->input('ordersn');
        $pay_type = $request->input('pay_type');#支付方式:1=微信,2=余额

        $order = ShopOrder::where('ordersn', $ordersn)->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('请刷新订单列表');
        }

        $pay_amount = $order->total_pay_amount;
        if ($pay_amount <= 0) {
            $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'goods']);
            $res = (new NotifyController())->balance($request);;
            $res = json_decode($res->rawBody());
            if ($res->code == 1) {
                return $this->fail($res->msg);
            }
            return $this->success('支付成功');
        } else {
            if ($pay_type == 1) {
                $result = Pay::pay($pay_type, $pay_amount, $ordersn, '购买商品', 'goods');
                return $this->success('唤醒微信', $result);
            } else {
                $user = $request->user();
                if ($user->money < $pay_amount) {
                    return $this->fail('余额不足');
                }
                $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'goods']);
                $res = (new NotifyController())->balance($request);;
                $res = json_decode($res->rawBody());
                if ($res->code == 1) {
                    return $this->fail($res->msg);
                }
                User::changeMoney(-$pay_amount, $user->id, '购买商品');
                return $this->success('支付成功');
            }
        }
    }

    /**
     * 获取订单列表
     * @param Request $request
     * @return Response
     */
    function getOrderList(Request $request)
    {
        $status = $request->input('status');#订单状态:0=全部,1=待付款,2=待发货,3=待收货,4=已完成,5=售后
        if (in_array($status, [0, 1, 2, 3, 4])) {
            $orders = ShopOrder::where('user_id', $request->user_id)
                ->when(!empty($status), function ($query) use ($status) {
                    if ($status == 1) {
                        $query->where('status', 0);
                    }
                    if ($status == 2) {
                        $query->where('status', 1);
                    }
                    if ($status == 3) {
                        $query->where('status', 3);
                    }
                    if ($status == 4) {
                        $query->where('status', 5);
                    }
                })
                ->orderBy('id', 'desc')
                ->paginate()
                ->items();
        } else {
            $orders = ShopOrder::where('user_id', $request->user_id)
                ->orderBy('id', 'desc')
                ->paginate()
                ->items();
        }

        return $this->success('成功', $orders);
    }

    /**
     * 删除订单
     * @param Request $request
     * @return Response
     */
    function delete(Request $request)
    {
        $id = $request->input('id');
        $order = ShopOrder::where('user_id', $request->user_id)->findOrFail($id);
        if (in_array($order->status, [0, 5])) {
            return $this->fail('订单状态异常');
        }
        $order->delete();
        return $this->success('成功');
    }

    /**
     * 查询快递
     * @param Request $request
     * @return \support\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function queryExpress(Request $request)
    {
        $waybill = $request->input('waybill');
        // 参数设置
        $key = 'EyZRxxQS9522';                        // 客户授权key
        $customer = '22FDF27268BE1066F45098821B88A4A6';                   // 查询公司编号
        $param = [
            'num' => $waybill
        ];
        $post_data = array();
        $post_data['customer'] = $customer;
        $post_data['param'] = json_encode($param, JSON_UNESCAPED_UNICODE);
        $sign = md5($post_data['param'] . $key . $post_data['customer']);
        $post_data['sign'] = strtoupper($sign);
        $url = 'https://poll.kuaidi100.com/poll/query.do';
        $client = new \GuzzleHttp\Client();
        $response = $client->post($url, [
            'form_params' => $post_data,
        ]);
        $result = $response->getBody()->getContents();
        $result = json_decode($result);
        $result = $result->data;
        return $this->success('成功', $result);
    }

    /**
     * 取消订单
     * @param Request $request
     * @return Response
     */
    function cancel(Request $request)
    {
        $id = $request->input('id');
        $order = ShopOrder::where('user_id', $request->user_id)->findOrFail($id);
        if ($order->status != 0) {
            return $this->fail('订单状态异常');
        }
        $order->status = 2;
        $order->save();
        return $this->success('成功');
    }

    /**
     * 确认收货
     * @param Request $request
     * @return Response
     */
    function confirm(Request $request)
    {
        $id = $request->input('id');
        $order = ShopOrder::where('user_id', $request->user_id)->findOrFail($id);
        if ($order->status != 3) {
            return $this->fail('订单状态异常');
        }
        $order->status = 4;#更改为待评价状态
        $order->save();
        $order->items->each(function (ShopOrderItem $item) {
            $item->status = 4;#子订单更改为待评价状态
            $item->save();
        });
        return $this->success('成功');
    }

    /**
     * 获取待评价列表
     * @param Request $request
     */
    function getWaitAssessList(Request $request)
    {
        $id = $request->input('id');
        $order = ShopOrder::where('user_id', $request->user_id)->findOrFail($id);
        if ($order->status != 4) {
            return $this->fail('订单状态异常');
        }
        // 直接查询状态为4的订单项，并预加载关联的 goods
        $items = $order->items()
            ->where('status', 4)
            ->with(['goods'])
            ->get();

        return $this->success('成功', $items);

    }

    /**
     * 评价
     * @param Request $request
     */
    function comment(Request $request)
    {
        $id = $request->input('id');#子订单id
        $score = $request->input('score');
        $content = $request->input('content');
        $images = $request->input('images');
        $anonymity = $request->input('anonymity');
        $item = ShopOrderItem::findOrFail($id);
        if ($item->status != 4) {
            return $this->fail('订单状态异常');
        }
        $item->status = 5;#改为评价完成
        $item->save();
        $item->comment()->create([
            'user_id' => $request->user_id,
            'order_id' => $item->order_id,
            'goods_id' => $item->goods_id,
            'score' => $score,
            'content' => $content,
            'images' => $images,
            'anonymity' => $anonymity,
        ]);
        //如果全部子订单都评价完成 主订单也改为评价完成
        if ($item->order->items->where('status', 4)->isEmpty()) {
            $item->order->status = 5;#改为订单完成
            $item->order->save();
        }
        return $this->success('成功');
    }


    /**
     * 申请售后
     * @param Request $request
     */
    function applyService(Request $request)
    {
        $id = $request->input('id');#item_id
        $refund_type = $request->input('refund_type');#类型:1=退货退款,2=换货
        $item = ShopOrderItem::findOrFail($id);
        if (!in_array($item->order->status, [3, 4, 5])) {
            return $this->fail('订单状态异常');
        }


    }
}
