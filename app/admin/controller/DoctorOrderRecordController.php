<?php

namespace app\admin\controller;

use app\admin\model\DoctorOrder;
use app\admin\model\DoctorOrderRecord;
use app\admin\model\DoctorOrderRecordMedicine;
use app\admin\model\Medicine;
use app\admin\model\MedicineWeight;
use app\api\service\Pay;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 处方 
 */
class DoctorOrderRecordController extends Crud
{
    
    /**
     * @var DoctorOrderRecord
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new DoctorOrderRecord;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('doctor-order-record/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $medicine_id = $request->input('medicine_id');
            $weight_id = $request->input('weight_id');
            $order_id = $request->input('order_id');
            if (empty($medicine_id[0] )|| empty($weight_id[0])){
                return $this->fail('请添加处方信息');
            }
            $order = DoctorOrder::find($order_id);
            if ($order->status != 3){
                return $this->fail('此订单未完成');
            }
            $mergedArray = [];

            $pay_amount = 0;
            $ordersn = Pay::generateOrderSn();
            foreach ($medicine_id as $index => $id) {
                $medicine = Medicine::find($id);
                $weigh = MedicineWeight::find($weight_id[$index]);
                $pay_amount += $medicine->price * $weigh->weight;
                $mergedArray[] = array(
                    'medicine_id' => $medicine->id,
                    'weight_id' => $weigh->id,
                    'price' => $medicine->price,
                );
            }

            $request->setParams('post',[
                'pay_amount' => $pay_amount,
                'ordersn' => $ordersn,
            ]);
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            foreach ($mergedArray as $item){
                DoctorOrderRecordMedicine::create([
                    'record_id' => $id,
                    'medicine_id' => $item['medicine_id'],
                    'weight_id' => $item['weight_id']
                ]);
            }
            return $this->json(0, 'ok', ['id' => $id]);
        }
        return view('doctor-order-record/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('doctor-order-record/update');
    }

}
