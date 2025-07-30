<?php

namespace app\api\controller;

use app\admin\model\FeedbackAnswer;
use app\admin\model\FeedbackQuestion;
use app\admin\model\ServiceOrder;
use app\api\basic\Base;
use support\Request;

class FeedbackController extends Base
{
    /**
     * 获取问卷问题及其选项
     */
    function questions(Request $request)
    {
        $questions = FeedbackQuestion::with(['option' => function ($query) {
            $query->orderBy('weight');
        }])
            ->orderBy('weight')
            ->get();
        return $this->success('成功', $questions);
    }

    /**
     * 提交问卷答案
     * @param Request $request
     * @return \support\Response
     */
    public function submit(Request $request)
    {
        $order_id = $request->input('order_id');
        $data = $request->input('answers', []);

        if (empty($data)) {
            return $this->fail('答案不能为空');
        }
        $order = ServiceOrder::find($order_id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        $answersToInsert = [];

        foreach ($data as $entry) {
            $questionId = $entry['question_id'] ?? null;
            $answer = $entry['answer'] ?? null;

            if (!$questionId || $answer === null) {
                continue; // 跳过无效项
            }

            // 检查问题是否存在
            $question = FeedbackQuestion::find($questionId);
            if (!$question) continue;
            // 普通单选/文本题
            $answersToInsert[] = [
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'question_id' => $questionId,
                'answer' =>  $answer,
            ];

        }
        foreach ($answersToInsert as $one) {
            FeedbackAnswer::create($one);
        }
        $order->is_assess = 1;
        $order->save();
        return $this->success('提交成功');
    }

}
