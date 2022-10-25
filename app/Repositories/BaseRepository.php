<?php


namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

//仓库基类
class BaseRepository
{
    /**@var Model */
    protected $model;
    protected $modelName = '';//仓库类与模型命名不统一时使用

    public function __construct()
    {
        try {
            if (empty($this->modelName)) {
                $thisClassInfo = new \ReflectionClass($this);
                $name = $thisClassInfo->getShortName();
                $name = str_replace('Repository', '', $name);
                $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
                    return '_' . strtolower($matchs[0]);
                }, $name);
                $className = trim(preg_replace('/_{2,}/', '_', $dstr), '_');
                $className = 'model_' . $className;
                $this->model = app($className);
            } else {
                $this->model = app($this->modelName);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Desc: 新增一条或多条记录
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/9 17:41
     * ApiLink:
     *
     * @param  array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return $this->model->newQuery()->insertGetId($data);
    }

    /**
     * Desc: 新增一条或多条记录
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/9 17:41
     * ApiLink:
     *
     * @param  array $data
     * @return mixed
     */
    public function insertRows(array $data)
    {
        return $this->model->newQuery()->insert($data);
    }

    /**
     * Desc: 新增一条或多条记录（忽略插入失败）
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2022/06/06 17:41
     *
     * @param  array $data
     * @return mixed
     */
    public function insertRowsOrIgnore(array $data)
    {
        $tableName = $this->model->getTable();
        if (!empty($tableName) && !empty($data[0])) {

            $count = count($data);
            $columnNames = array_keys($data[0]);
            $columnCount = count($columnNames);

            $valuesDemoArr = array();
            for ($i = 0; $i < $columnCount; $i++) {
                $valuesDemoArr[$i] = "?";
            }
            $valuesDemo = "(" . implode(',', $valuesDemoArr) . ")";
            $sql = "INSERT IGNORE INTO {$tableName} (" . implode(',', $columnNames) . ")VALUE";
            for ($i = 0; $i < $count; $i++) {
                $sql .= $valuesDemo . ",";
            }

            //去除字符串最后一个逗号
            $sql = substr($sql, 0, -1);

            $values = array();
            foreach ($data as $v) {
                $valueTmp = array_values($v);
                $values = array_merge($values, $valueTmp);
            }
            $result = DB::insert($sql, $values);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 更新或创建
     * @param array $attributes
     * @param array $values
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->newQuery()->updateOrCreate($attributes, $values);
    }

    /**
     * Desc: 根据主键删除一条或多条记录
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/9 17:20
     * ApiLink: 接口url
     *
     * @param  int|array $ids
     * @return bool
     */
    public function delete($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        return $this->model->newQuery()->whereIn('id', $ids)->delete();
    }

    /**
     * Desc: 更新
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/9 17:46
     * ApiLink:
     *
     * @param  int|array $ids
     * @param  array $data
     * @return mixed
     */
    public function update($ids, $data)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $model = $this->model->newQuery();
        return $model->whereIn('id', $ids)->update($data);
    }

    /**
     * 更新通过case when
     * @param $data
     * @param array $option
     *            when_column   更新操作条件字段名称，默认主键【慎用】
     * @return int
     */
    public function updateCaseWhen($data, $option = array())
    {
        $updateCount = 0;

        if (!is_array($data) || empty($data)){
            return $updateCount;
        }

        if (empty($data[0])) {
            $data = array($data);
        }

        //默认更新过滤条件字段名称
        $whenColumn = "uuid";
        if (!empty($option['when_column'])) {
            $whenColumn = $option['when_column'];
        }
        $whenValues = array_column($data, $whenColumn);
        if (empty($whenValues)) {
            return $updateCount;
        }

        //case条件中sql数组
        $caseSqlArr = array();
        //sql绑定参数
        $bindings = array();
        //根据字段名称划分数据
        $columnData = array();
        foreach ($data as $v) {
            if (empty($v[$whenColumn])) {
                //过滤条件唯一值不能为空
                return $updateCount;
            }

            $whenValue = $v[$whenColumn];
            foreach ($v as $column => $value) {
                if ($column !== $whenColumn) {
                    if (!isset($columnData[$column])) {
                        $columnData[$column] = array();
                    }

                    if (!isset($columnData[$column][$value])) {
                        $columnData[$column][$value] = array();
                    }
                    $columnData[$column][$value][] = $whenValue;

                }
            }
        }

        if (empty($columnData)) {
            return $updateCount;
        }

        foreach ($columnData as $column => $columnInfo) {
            $caseSql = "`{$column}` = CASE ";
            foreach ($columnInfo as $value => $when) {
                $caseSql .= "WHEN `{$whenColumn}` IN ('" . implode("','", $when) . "') THEN ? ";
                $bindings[] = $value;
            }
            $caseSql .= "ELSE `{$column}` END ";
            $caseSqlArr[] = $caseSql;
        }

        $sql = "update `" . $this->model->getTable() . "` set ";
        $sql .= implode(',', $caseSqlArr);
        $sql .= "where {$whenColumn} in ('" . implode("','", $whenValues) . "')";

        $updateCount = DB::update($sql, $bindings);
        return $updateCount;
    }

    /**
     * Desc: 获取一条记录
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/9 17:48
     * ApiLink:
     *
     * @param $where
     * @param  string|array $fields
     * @param array $orderBy 排序，[排序字段 =>   排序方式]，示例：['sort' => 'asc', 'id' => 'desc']
     * @return mixed
     */
    public function first($where, $fields = '*', $orderBy = array())
    {
        $query = $this->model->newQuery()->select($fields)->where($where);
        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $sortBy) {
                $query->orderBy($field, $sortBy);
            }
        }
        return $query->first();
    }

    /**
     * Desc: 获取一行记录某个字段的值
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/10 10:00
     * ApiLink:
     *
     * @param $where
     * @param $field
     * @param array $orderBy 排序，[排序字段 =>   排序方式]，示例：['sort' => 'asc', 'id' => 'desc']
     * @return mixed
     */
    public function value($where, $field, $orderBy = array())
    {
        $query = $this->model->newQuery()->where($where);
        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $sortBy) {
                $query->orderBy($field, $sortBy);
            }
        }
        return $query->value($field);
    }

    /**
     * Desc: 获取汇总
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2022/04/22 10:00
     * ApiLink:
     *
     * @param $where
     * @param $field
     * @return mixed
     */
    public function sum($where, $field)
    {
        return $this->model->newQuery()->where($where)->sum($field);
    }

    /**
     * Desc: 计数
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2022/04/22 10:00
     * ApiLink:
     *
     * @param $where
     * @param $field
     * @return mixed
     */
    public function count($where, $field = "*")
    {
        return $this->model->newQuery()->where($where)->count($field);
    }

    /**
     * Desc: 获取具有给定列值的数组
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/10 11:28
     * ApiLink:
     *
     * @param  array $where
     * @param  string $fields
     * @param  string $index
     * @param  array $orderBy 排序，[排序字段 =>   排序方式]，示例：['sort' => 'asc', 'id' => 'desc']
     * @return array
     */
    public function pluck($where, $fields = '*', $index = '', $orderBy = [])
    {
        if (is_array($fields) && empty($index)) {
            return [];
        }

        $model = $this->model->select($fields);
        foreach ($where as $k => $v) {
            if (is_array($v) && count($v) == 3 && strtolower($v[1]) == 'in') {
                $model = $model->whereIn($v[0], $v[2]);
                unset($where[$k]);
            }
        }
        $model = $model->where($where);

        if ($orderBy) {
            foreach ($orderBy as $field => $sortBy) {
                $model = $model->orderBy($field, $sortBy);
            }
        }

        if ((is_array($fields) && $fields != ['*']) || $fields == '*' || $fields == ['*']) {
            return $model->get()->keyBy($index)->toArray();
        } else {
            if ($index) {
                $model = $model->pluck($fields, $index);
            } else {
                $model = $model->pluck($fields);
            }

            return $model->toArray();
        }
    }

    /**
     * Desc: 查询多条数据，不分页
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/9 17:45
     * ApiLink:
     *
     * @param  array $where 传['fileld','in',[]] 表示走in查询
     * @param  string|array $fields
     * @param  array $orderBy 排序，[排序字段 => 排序方式]，示例：['id' => 'desc']
     * @return mixed
     */
    public function get($where = [], $fields = '*', $orderBy = [], $groupBy = '', $limit = 0)
    {

        $model = $this->model->newQuery()->select($fields);

        foreach ($where as $k => $v) {
            if (is_array($v) && count($v) == 3 && strtolower($v[1]) == 'in') {
                $model->whereIn($v[0], $v[2]);
                unset($where[$k]);
            }
        }

        $model = $model->where($where);

        if ($orderBy) {
            foreach ($orderBy as $field => $sortBy) {
                $model = $model->orderBy($field, $sortBy);
            }
        }

        if ($groupBy) {
            if (is_array($groupBy)) {
                foreach ($groupBy as $field) {
                    $model = $model->groupBy($field);
                }
            } else {
                $model = $model->groupBy($groupBy);
            }
        }

        if ($limit > 0) {
            $model = $model->limit($limit);
        }

        return $model->get();
    }

    /**
     * Desc: 查询多条数据，分页
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * Date: 2021/12/9 17:47
     * ApiLink:
     *
     * @param  array $where 传['fileld','in',[]] 表示走in查询
     * @param  string $fields
     * @param  int $pageSize
     * @param  array $orderBy 排序，[排序字段 => 排序方式]，示例：['sort' => 'asc', 'id' => 'desc']
     * @return mixed
     */
    public function paginate($where = [], $fields = '*', $pageSize = 10, $orderBy = [])
    {
        $model = $this->model->select($fields);
        foreach ($where as $k => $v) {
            if (is_array($v) && count($v) == 3 && strtolower($v[1]) == 'in') {
                $model->whereIn($v[0], $v[2]);
                unset($where[$k]);
            }
        }

        $model = $model->where($where);

        if ($orderBy) {
            foreach ($orderBy as $field => $sortBy) {
                $model = $model->orderBy($field, $sortBy);
            }
        }

        return $model->paginate($pageSize);
    }
}
