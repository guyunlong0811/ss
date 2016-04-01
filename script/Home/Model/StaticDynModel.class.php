<?php
namespace Home\Model;

use Think\Model;

class StaticDynModel extends BaseModel
{

    protected $connection = array(
        'db_type' => DB_STATIC_TYPE,
        'db_host' => DB_STATIC_HOST,
        'db_user' => DB_STATIC_USER,
        'db_pwd' => DB_STATIC_PWD,
        'db_port' => DB_STATIC_PORT,
        'db_name' => DB_STATIC_NAME,
        'db_charset' => DB_STATIC_CHARSET,
    );

    protected $autoCheckFields = false;
    private $mTable = array();
    private $mRow = array();

    private $mList = array(
        'cash' => array('where' => array('status' => 1,), 'order' => array('channel_id' => 'asc', 'cash_id' => 'asc',),),
    );
    private $mModel;
    private $mIndex;
    private $mField;

    //获取单条配置
    public function access($model, $index = null, $field = null)
    {
        $this->mModel = $model;
        $this->mIndex = $index;
        $this->mField = $field;
//        dump($this->mModel.'.'.$this->mIndex.'.'.$this->mField);
        //全部属性
        if (empty($this->mIndex) && empty($this->mField)) {
            $config = $this->getAll();
        } else {
            //当是k-v表时，特殊
            if ($model == 'params') {
                $this->mField = 'value';
                $this->mIndex = strtoupper($this->mIndex);
            }

            //单条属性
            if (is_null($this->mField)) {
                $config = $this->getRow($index);
            } else {
                //单个属性
                if (!is_array($this->mField)) {
                    $config = $this->getAttr();
                } else {
                    $config = $this->getRow();
                    foreach ($this->mField as $value){
                        $config = $config[$value];
                    }
                }
            }

        }

        if ($config === false) {
            C('G_DEBUG_DYNAMIC', $this->mModel . '.' . $this->mIndex . '.' . $this->mField);
            C('G_ERROR', 'config_dynamic_error');
            exit;
        }
        return $config;
    }

    //获取单条配置
    private function getAttr()
    {

        //获取全部参数
        if (!$config = $this->getRow()) {
            return false;
        }
        if (isset($config[$this->mField])) {
            return $config[$this->mField];
        } else {
            C('G_DEBUG_DYNAMIC', $this->mModel . '.' . $this->mIndex . '.' . $this->mField);
            C('G_ERROR', 'config_dynamic_error');
            return false;
        }

    }

    //获取单条配置
    private function getRow()
    {

        //在一次进程中缓存表数据
        if(!isset($this->mRow[$this->mModel][$this->mIndex])){

            //缓存数据
            $this->mRow[$this->mModel][$this->mIndex] = S(C('APC_PREFIX') . 'd_' . $this->mModel . ':' . $this->mIndex);

            //如果缓存中没有数据
            if (empty($this->mRow[$this->mModel][$this->mIndex])) {

                //获取整表数据
                if (!$config = $this->getAll()) {
                    return false;
                }

                //检查数据是否存在
                if (!isset($config[$this->mIndex])) {
                    C('G_DEBUG_DYNAMIC', $this->mModel . '.' . $this->mIndex . '.' . $this->mField);
                    C('G_ERROR', 'config_dynamic_error');
                    return false;
                }

                //存储缓存
                $this->mRow[$this->mModel][$this->mIndex] = $config[$this->mIndex];
                S(C('APC_PREFIX') . 'd_' . $this->mModel . ':' . $this->mIndex, $this->mRow[$this->mModel][$this->mIndex]);

            }

        }

        //返回
        return $this->mRow[$this->mModel][$this->mIndex];

    }

    //获取全部配置
    private function getAll()
    {
        //在一次进程中缓存表数据
        if(!isset($this->mTable[$this->mModel])){
            $this->mTable[$this->mModel] = S(C('APC_PREFIX') . 'd_' . $this->mModel);
        }

        //如果缓存中没有找到
        if (empty($this->mTable[$this->mModel])) {

            //从数据库获取数据
            $table = isset($this->mList[$this->mModel]['table']) ? 'd_' . $this->mList[$this->mModel]['table'] : 'd_' . $this->mModel;
            $where = isset($this->mList[$this->mModel]['where']) ? $this->mList[$this->mModel]['where'] : '1=1';
            $order = isset($this->mList[$this->mModel]['order']) ? $this->mList[$this->mModel]['order'] : array('index' => 'asc',);
            $all = $this->table($table)->where($where)->order($order)->select();
            if (!empty($all)) {
                foreach ($all as $value) {
                    switch ($this->mModel) {
                        //特殊情况
                        case 'cash':
                            $this->mTable[$this->mModel][$value['channel_id']][$value['cash_id']] = $value['goods_id'];
                            break;
                        case 'event_pt':
                            $this->mTable[$this->mModel][$value['pt_activity_id']] = $value['index'];
                            break;
                        //默认
                        default:
                            $this->mTable[$this->mModel][$value['index']] = $value;
                    }
                }
            }else{
                $this->mTable[$this->mModel] = array();
            }

            //存储缓存
            S(C('APC_PREFIX') . 'd_' . $this->mModel, $this->mTable[$this->mModel]);

        }

        return $this->mTable[$this->mModel];

    }

}