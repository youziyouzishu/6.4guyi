<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use plugin\admin\app\common\Util;
use plugin\admin\app\model\AdminRole;
use support\Db;
use support\Request;
use support\Response;
use app\admin\model\Doctor;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 医师管理
 */
class DoctorController extends Crud
{

    /**
     * @var Doctor
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Doctor;
    }

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('doctor/index');
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order)->with(['classFirst', 'classSecond','vip']);
        return $this->doFormat($query, $format, $limit);
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
            $username = $request->post('username');
            $password = $request->post('password');
            $avatar = $request->post('avatar');
            $name = $request->post('name');
            $admin = Admin::where('username', $username)->first();
            if ($admin) {
                return $this->fail('用户名重复');
            }

            Db::connection('plugin.admin.mysql')->beginTransaction();
            try {
                $admin = Admin::create([
                    'avatar' => $avatar,
                    'username' => $username,
                    'password' => Util::passwordHash($password),
                    'nickname' => $name,
                ]);

                $admin_role = new AdminRole;
                $admin_role->admin_id = $admin->id;
                $admin_role->role_id = 3;
                $admin_role->save();
                $request->setParams('post',[
                    'admin_id' => $admin->id
                ]);
                $data = $this->insertInput($request);
                $id = $this->doInsert($data);
                Db::connection('plugin.admin.mysql')->commit();
            } catch (\Throwable $e) {
                Db::connection('plugin.admin.mysql')->rollback();
                return $this->fail('提交失败');
            }
            return $this->json(0, 'ok', ['id' => $id]);
        }
        return view('doctor/insert');
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
        return view('doctor/update');
    }

}
