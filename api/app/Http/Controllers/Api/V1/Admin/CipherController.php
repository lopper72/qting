<?php
/**
 * 卡密管理
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Cipher;
use Illuminate\Http\Request;

class CipherController extends BaseController
{
    public function index()
    {
        $status = request()->input('status');
        $account_type = request()->input('account_type');
        $code = request()->input('code');
        $get_user_id = request()->input('get_user_id');
        $export = request()->input('export', 0);
        $model = new Cipher();
        if ($export) {
            $list = $model->where(function($query) use ($status, $account_type, $code, $get_user_id){
                        ($status !== null) AND $query->where('status', $status);
                        $account_type AND $query->where('account_type', $account_type);
                        $code AND $query->where('code', $code);
                        $get_user_id AND $query->where('get_user_id', $get_user_id);
                    })->orderBy('id','DESC')
                    ->get();
        } else {
            $list = $model->where(function($query) use ($status, $account_type, $code, $get_user_id){
                        ($status !== null) AND $query->where('status', $status);
                        $account_type AND $query->where('account_type', $account_type);
                        $code AND $query->where('code', $code);
                        $get_user_id AND $query->where('get_user_id', $get_user_id);
                    })->orderBy('id','DESC')
                    ->paginate($this->page_size);
        }
        foreach ($list as &$value) {
            $value['over_time'] AND $value['over_time'] = date('Y-m-d', $value['over_time']);
            $value['get_time'] AND $value['get_time'] = date('Y-m-d H:i:s', $value['get_time']);
        }
        return $this->success('成功', $list);
    }

    public function store(Request $request)
    {
        if (empty($request->num)) {
            return $this->error('卡密数量不能为空');
        }
        if (empty($request->account_type)) {
            return $this->error('兑换账户不能为空');
        }
        if (empty($request->amount)) {
            return $this->error('兑换额度不能为空');
        }

        $data = [];
        $data['user_id'] = $this->getUserId();
        $request->account_type AND $data['account_type'] = $request->account_type;
        $request->amount AND $data['amount'] = $request->amount;
        $request->over_time AND $data['over_time'] = strtotime($request->over_time);
        $data['status'] = 1;
        $data['updated_at'] = time();
        $data['created_at'] = time();

        $datas = [];
        for ($i = 1; $i <= $request->num; $i++) {
            $code = 'KM' . getRandNumber(0, 9, 9) . createRefcode();
            $data['code'] = strtoupper(md5($code));
            $datas[] = $data;
        }

        $model = new Cipher();
        $result = $model->insert($datas);
        if ($result) {
            return $this->success('添加成功');
        } else {
            return $this->error('添加失败');
        }
    }

    public function destroy($id)
    {
        $result = Cipher::where('id', $id)->update(['status' => 0]);
        if ($result) {
            return $this->success('删除成功', $result);
        } else {
            return $this->error('删除失败');
        }
    }

    public function batchDisable(Request $request)
    {
        $input = $request->toArray();
        if (!in_array($input['status'], [0, 1])) {
            return $this->error('状态不正确');
        }
        $result = Cipher::whereIn('id', $input['selection'])->update(['status' => $input['status']]);
        if ($result) {
            return $this->success('保存成功');
        } else {
            return $this->error('保存失败');
        }
    }
}
