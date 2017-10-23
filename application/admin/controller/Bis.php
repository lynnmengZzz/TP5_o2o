<?php
/**
 * Created by PhpStorm.
 * User: if-information
 * Date: 2017/10/17
 * Time: 下午4:18
 */
namespace app\admin\controller;

use function PHPSTORM_META\type;
use think\Controller;

class Bis extends Controller
{
    private $obj;

    protected function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->obj = model('Bis');
    }

    public function index()
    {
        //显示status为1的商户信息
        $res = $this->obj->getBisByStatus(1);
        return $this->fetch('',[
            'bises' => $res
        ]);
    }
    public function apply()
    {
        $res = $this->obj->getBisByStatus(0);
        return $this->fetch('',[
            'bises' => $res
        ]);
    }
    public function dellist()
    {
        $res = $this->obj->getBisByStatus(-1);
        return $this->fetch('',[
            'bises' => $res
        ]);
    }
    public function detail()
    {
        $data = input('id',0,'intval');
        $res = $this->obj->get($data);
        //所属分类的获取
        $categories = model('Category')->getAllFirstNormalCategoried();
        //店铺位置信息的获取
        $localMes = model('Bislocation')->getMsgById($res['id']);
        //用户信息的获取
        $user = model('BisAccount')->getAccountById($res['id']);
        if ($user)
        {
            $userMes = $user;
        }
        //城市分类信息的获取
        $citys = model('City')->getNormalCitiesByParentId();
        //二级城市信息
        $idArray = explode(',',$res['city_path']);
        $city = model('City')->getNormalCitiesByParentId($res['city_id']);
        return $this->fetch('',[
            //总的Bis信息
            'Bis' => $res,
            //一级城市分类
            'Citys' => $citys,
            //二级城市分类
            'City' => $city,
            //二级城市Id
            'seCityId' => $idArray[1],
            //用户信息
            'userMes' => $user,
            //店铺位置信息
            'localMes' => $localMes,
            //所属分类
            'categories' => $categories
        ]);
    }
    //根据city_path获取二级城市id
//    public function getSeCityIdByCityPath($city_path)
//    {
//        if (empty($city_path))
//        {
//            return '';
//        }
//        //正则校验
//        if (preg_match('/,/',$city_path))
//        {
//            $idArray = explode(',',$city_path);
//            $se_city_id = $idArray[1];
//        }
//    }
    //动态获取二级城市
    public function secondCity()
    {
        $data = input('post.');
        $cities = model('City')->getNormalCitiesByParentId($data['parent_id']);
        if (!$cities)
        {
            return $this->result('',0,'失败');
        }
        else
        {
            return $cities;
        }
    }
    public function getcategories()
    {
        $parent_id = input('post.id',0,'intval');
        $res = model('Category')->getAllFirstNormalCategoried($parent_id);
        if (!$res)
        {
            return $this->result('',0,'获取失败');
        }
        else
        {
            return $this->result($res,1,'获取成功');
        }
    }
    //修改状态的方法
    public function status()
    {
        //获取id和status
        $id = input('id',0,'intval');
        $status = input('status',0,'intval');
        //修改bis bisAccount bisLocation
        $res_bis = $this->obj->save(['status' => $status],['id' => $id]);
        $res_location = model('BisLocation')->save(['status' => $status],['bis_id' => $id,'is_main' => 1]);
        $res_account = model('BisAccount')->save(['status' => $status],['bis_id' => $id,'is_main' => 1]);
        if ($res_bis && $res_location && $res_account)
        {
            $this->success('状态更新成功');
        }
        else
        {
            $this->error('状态更新失败');
        }
    }
}