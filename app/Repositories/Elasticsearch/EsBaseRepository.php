<?php
/**
 * EsBaseRepository.php
 * ==============================================
 * Copy right 2015-2022  by https://www.tianmtech.com/
 * ----------------------------------------------
 * This is not a free software, without any authorization is not allowed to use and spread.
 * ==============================================
 * @desc: es基类
 * @author: zhanglinxiao <xuwenjie@tianmtech.cn>
 * @date: 2022/09/07
 * @version: v2.0.0
 * @since: 2022/09/07 15:56
 */

namespace App\Repositories\Elasticsearch;

use App\Exceptions\BasicException;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class EsBaseRepository
{
    /**
     * @var Client
     */
    protected static $client;

    /**
     * 索引名称
     * @var string
     */
    protected $index;

    /**
     * 是否开启es使用
     * @var int 0-开启；1-不开启；
     */
    protected $enable;

    /**
     * 索引名称（完整）
     * @var string
     */
    private $fullIndex;

    /**
     * 属性
     * @var array
     */
    protected $properties = array();

    /**
     * 分片数
     * @var int
     */
    protected $numberOfShards = 6;

    /**
     * 副本数
     * @var int
     */
    protected $numberOfReplicas = 1;


    /**
     * 是否开启es使用
     * @return int 0-开启；1-不开启；
     */
    public function isEnable()
    {
        $globalEnable = env("ELASTICSEARCH_ENABLE", 1);
        if ($globalEnable == 1) {
            if (isset($this->enable) && $this->enable == 0) {
                $enable = 0;
            } else {
                $enable = 1;
            }
        } else {
            $enable = 0;
        }
        return $enable;
    }

    /**
     * 获取es host
     * @return mixed
     */
    public function getHost()
    {
        return env("ELASTICSEARCH_HOST");
    }

    /**
     * 获取索引名称
     * @return mixed
     * @throws BasicException
     */
    public function getIndex()
    {
        try {
            if (empty($this->fullIndex)) {
                if (empty($this->index)) {
                    //未定义索引名称，则取对应数据库表名
                    $thisClassInfo = new \ReflectionClass($this);
                    $name = $thisClassInfo->getShortName();
                    $name = str_replace('Repository', '', $name);
                    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
                        return '_' . strtolower($matchs[0]);
                    }, $name);
                    $className = trim(preg_replace('/_{2,}/', '_', $dstr), '_');
                    $className = 'model_' . $className;
                    $this->model = app($className);

                    $index = $this->model->getTable();
                } else {
                    $index = $this->index;
                }

                //前缀
                if (!empty(env("ELASTICSEARCH_PREFIX"))) {
                    $this->setFullIndex(env("ELASTICSEARCH_PREFIX") . $index);
                } else {
                    $this->setFullIndex($index);
                }
            }
        } catch (\Throwable $e) {
            throw new BasicException($e->getCode(), $e->getMessage());
        }

        return $this->fullIndex;
    }


    /**
     * 获取client
     * @return Client
     */
    public function getClient()
    {
        if (is_null(self::$client)) {
            $host = $this->getHost();
            $client = ClientBuilder::create()->setHosts(array($host))->build();
            self::$client = $client;
        }
        return self::$client;
    }

    /**
     * 插入单条数据
     * @param array $data
     * @return int|mixed
     * @throws BasicException
     */
    public function insert(array $data)
    {
        $data = $this->formatData($data);
        if (empty($data['id'])) {
            Throw new BasicException(11001, 'es插入id不能为空');
        }
        $id = $data['id'];
        $params = [
            'index' => $this->getIndex(),
            'id' => $data['id'],
            'body' => $data
        ];

        $response = $this->getClient()->create($params);
        if (!empty($response['result']) && $response['result'] = "created") {
            return $id;
        } else {
            return 0;
        }
    }

    /**
     * 批量插入多条数据
     * @param array $data
     * @return int|mixed
     * @throws BasicException
     */
    public function insertRows(array $data)
    {
        $data = $this->formatData($data);
        return $this->bulkSave($data, 'create');
    }

    /**
     * 批量保存（index，create，update，delete）
     * @param $data
     * @param string $action
     * @return array
     * @throws BasicException
     */
    public function bulkSave($data, $action = "index")
    {
        if (!empty($data)) {
            $body = $this->bulkBody($data, $action);
            $params = [
                'index' => $this->getIndex(),
                'body' => $body
            ];
            $response = $this->getClient()->bulk($params);
            return $this->bulkReturn($response);
        } else {
            return array();
        }
    }

    /**
     * 批量保存（循环使用index）
     * @param $data
     * @return array
     * @throws BasicException
     */
    public function batchSave($data)
    {
        $result = array();
        if (!empty($data)) {
            if (empty($data[0])) {
                $data = array($data);
            }
            $data = $this->formatData($data);
            foreach ($data as $v) {
                $params = array(
                    'index' => $this->getIndex(),
                    'id' => $v['id'],
                    'body' => $v,
                );

                $result[] = $this->getClient()->index($params);
            }
        }

        return $result;
    }

    /**
     * 生成bulk的body
     * @param $data
     * @param string $action
     * @return string
     * @throws BasicException
     */
    protected function bulkBody($data, $action = "index")
    {
        $data = $this->formatData($data);
        $allBodyArr = array();
        $index = $this->getIndex();
        foreach ($data as $v) {
            //body数组
            $bodyArr = array();
            //操作信息
            $actionParams = array();
            if (!empty($v['id'])) {
                $actionParams['_id'] = $v['id'];
            }
            $actionParams['_index'] = $index;

            $head = array(
                $action => $actionParams
            );
            $bodyArr[] = json_encode($head);
            if (!empty($v)) {
                if (is_array($v)) {
                    $bodyArr[] = json_encode($v);
                } else if (is_string($v)) {
                    $bodyArr[] = $v;
                }
            }

            $body = implode("\n", $bodyArr);
            $body .= "\n";

            $allBodyArr[] = $body;
        }
        $allBody = implode("\n", $allBodyArr);
        $allBody .= "\n";
        return $allBody;
    }

    /**
     * 处理bulk的返回结果
     * @param $response
     * @param string $action
     * @return string
     */
    protected function bulkReturn($response)
    {
        return $response;
    }

    /**
     * 删除记录（删除document）
     * @param $ids
     * @return int
     * @throws BasicException
     */
    public function delete($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        try {
            $params = array(
                'index' => $this->getIndex(),
                'body' => array(
                    'query' => array(
                        "terms" => array(
                            "id" => $ids
                        )
                    )
                ),
            );
            $this->getClient()->deleteByQuery($params);
            return 1;
        } catch (\Throwable $e) {
            throw new BasicException($e->getCode(), $e->getMessage());
        }
        return 0;

    }

    /**
     * 更新操作
     * @param $id
     * @param $data
     * @return int
     * @throws BasicException
     */
    public function update($id, $data)
    {
        $data = $this->formatData($data);
        try {
            $params = array(
                'index' => $this->getIndex(),
                'id' => $id,
                'body' => array(
                    'doc' => $data
                ),
            );
            $this->getClient()->update($params);
            return 1;
        } catch (\Throwable $e) {
            throw new BasicException($e->getCode(), $e->getMessage());
        }
        return 0;
    }

    /**
     * 搜索
     *
     * @param $body
     * @return array
     * @throws BasicException
     */
    public function search($body)
    {
        $params = array(
            'index' => $this->getIndex(),
            'body' => $body
        );

        try {
            $rlt = $this->getClient()->search($params);
        } catch (\Throwable $e) {
            $rlt = array();
            throw new BasicException($e->getCode(), $e->getMessage());
        }

        return $rlt;
    }

    /**
     * 获取列表
     * @param array $where
     * @param array $fields
     * @param array $orderBy
     * @param string $groupBy
     * @param int $limit
     * @return array
     * @throws BasicException
     */
    public function get($where = [], $fields = array('*'), $orderBy = [], $groupBy = '', $limit = 1000)
    {
        $body = $this->buildSearchBody(array(
            'where' => $where,
            'fields' => $fields,
            'order_by' => $orderBy,
            'group_by' => $groupBy,
            'limit' => $limit,
        ));

        $rlt = $this->search($body);

        //处理搜索结果数据
        $list = $this->getSearchList($rlt);

        return $list;
    }

    /**
     * 获取单条信息
     * @param array $where
     * @param array $fields
     * @param array $orderBy
     * @param string $groupBy
     * @param int $limit
     * @return array
     * @throws BasicException
     */
    public function first($where = [], $fields = array('*'), $orderBy = [])
    {
        $body = $this->buildSearchBody(array(
            'where' => $where,
            'fields' => $fields,
            'order_by' => $orderBy,
            'limit' => 1,
        ));

        $rlt = $this->search($body);

        //处理搜索结果数据
        $list = $this->getSearchList($rlt);
        if (!empty($list[0])) {
            $info = $list[0];
        } else {
            $info = array();
        }
        return $info;
    }

    /**
     * @desc: 获取合计值
     * @param array $where
     * @param string $field
     * @return int
     * @throws BasicException
     * @author xuwj <xuwenjie@tianmtech.cn>
     * @datetime 2022/9/9 10:16
     */
    public function sum(array $where = [], string $field = '')
    {
        if (empty($field)) {
            return 0;
        }
        $body = $this->buildSearchBody(
            [
                'where' => $where,
                'filed' => $field,
                'size' => 0,
                'limit' => 1,
            ],
            [
                'aggs' => [
                    'sum_salary' => [
                        'sum' => [
                            'field' => $field,
                        ],
                    ],
                ],
            ]
        );

        $rlt = $this->search($body);

        //处理搜索结果数据
        return $this->getSearchSum($rlt);
    }


    /**
     * 获取列表（分页）
     * @param array $where
     * @param array $fields
     * @param int $page
     * @param int $pageSize
     * @param array $orderBy
     * @param string $groupBy
     * @param int $limit
     * @return array
     * @throws BasicException
     */
    public function paginate($where = [], $fields = array('*'), $page, $pageSize = 10, $orderBy = [])
    {
        $body = $this->buildSearchBody(array(
            'where' => $where,
            'fields' => $fields,
            'order_by' => $orderBy,
            'limit' => $pageSize,
            'page' => $page,
        ), array(
            'track_total_hits' => true
        ));

        $rlt = $this->search($body);

        //处理搜索结果数据
        $list = $this->getSearchList($rlt);
        $total = $this->getSearchTotal($rlt);

        return array(
            'total' => $total,
            'list' => $list,
        );
    }


    /**
     * 构建search的body
     * @param $params
     * @param array $option
     *          track_total_hits    是否获取真正总条数:true-获取；false-不获取；
     * @return array
     */
    protected function buildSearchBody($params, $option = array())
    {
        $body = array();
        //过滤条件
        if (isset($params['where'])) {
            $query = $this->getQuery($params['where']);
            if (!empty($query)) {
                $body['query'] = $query;
            }
        }

        //字段
        if (isset($params['fields'])) {
            $body["_source"] = $params['fields'];
        }

        //排序
        if (!empty($params['order_by'])) {
            $body["sort"] = $params['order_by'];
        }

        //聚合
        if (!empty($params['group_by'])) {
//            $body["aggs"] = array(
//                'group_by_key'=>array(
//                    'terms'=>array(
//                        'field'=>$params['group_by'].".keyword"
//                    )
//                )
//            );
        }

        //条数
        if (!empty($params['limit'])) {
            $body['size'] = $params['limit'];
        } else {
            $body['size'] = 20;
        }

        //页码
        if (!empty($params['page'])) {
            $body['from'] = ($params['page'] - 1) * $body['size'];
        }

        //其他配置选项
        if (!empty($option)) {
            $body = array_merge($body, $option);
        }


        return $body;
    }

    /**
     * 获取search查询列表
     * @param $rlt
     * @return array
     */
    protected function getSearchList($rlt)
    {
        $list = array();
        if (!empty($rlt['hits']['hits'])) {
            $hit = $rlt['hits']['hits'];
            $list = array_column($hit, "_source");
        }
        return $list;
    }

    /**
     * 获取search查询总数
     * @param $rlt
     * @return int
     */
    protected function getSearchTotal($rlt)
    {
        $total = 0;
        if (isset($rlt['hits']['total']['value'])) {
            $total = (int)$rlt['hits']['total']['value'];
        } elseif (isset($rlt['hits']['total'])) {
            $total = (int)$rlt['hits']['total'];
        }
        return $total;
    }

    /**
     * @desc: 获取search 字段合计值
     * @param $rlt
     * @return int
     * @author xuwj <xuwenjie@tianmtech.cn>
     * @datetime 2022/9/9 10:15
     */
    protected function getSearchSum($rlt)
    {
        $sum = 0;
        if (isset($rlt['aggregations']['sum_salary'])) {
            $sum = (int)($rlt['aggregations']['sum_salary']['value'] ?? 0);
        }

        return $sum;
    }

    /**
     * 通过$where 转换成过$query
     * @param $where
     *      案例1：array("id"=>1)
     *      案例2：array(array("id","=",1))
     *      案例3：array(array("id","!=",1))
     *      案例4：array(array("or",array(
     *          array("id",1),
     *          array(
     *              array("id",2),
     *              array("id",3),
     *          ),
     *      )))
     * @return array
     */
    protected function getQuery($where)
    {
        $query = array();

        //表单时数组
        $exprArr = array(
            '>' => array(
                'expr' => 'range',
                'sign' => 'gt',
            ),

            '>=' => array(
                'expr' => 'range',
                'sign' => 'gte',
            ),

            '<' => array(
                'expr' => 'range',
                'sign' => 'lte',
            ),

            '<=' => array(
                'expr' => 'range',
                'sign' => 'lte',
            ),
        );

        //属性
        $properties = $this->getProperties();
        if (!empty($where) && is_array($where)) {
            $mustList = array();
            foreach ($where as $k => $v) {
                $shouldList = array();
                if (is_array($v)) {
                    if (isset($v[0]) && isset($v[1]) && $v[0] == "or" && is_array($v[1])) {
                        if (is_array($v[1])) {
                            //判断是否包含二级数组
                            foreach ($v[1] as $v2) {
                                if (isset($v2[0]) && is_array($v2[0])) {
                                    $shouldList[] = $this->getQuery($v2);
                                } else {
                                    $shouldList[] = $this->getQuery(array($v2));
                                }
                            }
                        }
                    } else if (isset($v[0]) && isset($v[1]) && isset($v[2])) {
                        $column = $v[0];
                        $operator = $v[1];
                        $value = $v[2];
                        if (!empty($exprArr[$operator])) {
                            $exprInfo = $exprArr[$operator];
                            $mustList[] = array(
                                $exprInfo['expr'] => array(
                                    $column => array(
                                        $exprInfo['sign'] => $value
                                    )
                                )
                            );
                        } else {
                            if ($operator == "=") {//等号
                                $mustList[] = array(
                                    'match' => array(
                                        $column => $value
                                    )
                                );
                            } else if ($operator == "!=") {//不等号
                                $mustList[] = array(
                                    "bool" => array(
                                        "must_not" => array(
                                            'term' => array(
                                                $column => $value
                                            )
                                        )
                                    )
                                );
                            } else if ($operator == "like") {//模糊匹配
                                $val = str_replace("%", "*", $value);
                                $mustList[] = array(
                                    'wildcard' => array(
                                        "{$column}.keyword" => $val
                                    )
                                );
                            } elseif ($operator == "in") {
                                if (!empty($properties[$column]['fields']['keyword'])) {
                                    $mustList[] = array(
                                        'terms' => array(
                                            "{$column}.keyword" => array_values($value)
                                        )
                                    );
                                } else {
                                    $mustList[] = array(
                                        'terms' => array(
                                            "{$column}" => array_values($value)
                                        )
                                    );
                                }
                            }
                        }
                    } else if (isset($v[0]) && isset($v[1])) {
                        $column = $v[0];
                        $value = $v[1];
                        $mustList[] = array(
                            'match' => array(
                                $column => $value
                            )
                        );
                    }
                } else {
                    if ($k == "or" && is_array($v)) {
                        $shouldList[] = $this->getQuery($v);
                    } else {
                        $mustList[] = array(
                            'term' => array(
                                $k => $v
                            )
                        );
                    }
                }

                if (!empty($shouldList)) {
                    $mustList[] = array("bool" => array("should" => $shouldList));
                }
            }

            $query = array(
                'bool' => array(//                    'must' => $mustList,
                )
            );

            if (!empty($mustList)) {
                $query['bool']['must'] = $mustList;
            }

        }
        return $query;
    }


    /**
     * 创建索引
     *
     * @param array $body
     * @param array $index
     * @return array
     * @throws \Exception
     */
    public function createIndex()
    {
        $body = array(
            'mappings' => array(
                'properties' => $this->getProperties(),
            ),

            'settings' => array(
                'index' => array(
                    'number_of_shards' => $this->numberOfShards,
                    'number_of_replicas' => $this->numberOfReplicas,
                )
            )
        );

        $params = array(
            'index' => $this->getIndex(),
            'body' => $body
        );

        return $this->getClient()->indices()->create($params);
    }

    /**
     * 获取索引属性
     * @return array
     */
    protected function getProperties()
    {
        return $this->properties;
    }

    /**
     * 格式数据
     * @param $data
     * @return array
     */
    protected function formatData($data)
    {
        $properties = $this->getProperties();
        foreach ($data as $k1 => $v1) {
            if (is_array($v1)) {
                foreach ($v1 as $k2 => $v2) {
                    if ($v2 == "0000-00-00 00:00:00") {
                        if (!empty($properties[$k2]['type']) && $properties[$k2]['type'] == "date") {
                            $data[$k1][$k2] = null;
                        }
                    }
                }
            } else {
                if (!empty($properties[$k1]['type']) && $properties[$k1]['type'] == "date") {
                    $data[$k1] = null;
                }
            }
        }
        return $data;
    }

    /**
     * 设置完整索引
     * @param $fullIndex
     */
    protected function setFullIndex($fullIndex)
    {
        $this->fullIndex = $fullIndex;
    }

    /**
     * 检查索引是否存在
     * @return bool
     * @throws BasicException
     */
    public function isIndexExist()
    {
        $params = array(
            'index' => $this->getIndex()
        );
        return $this->getClient()->indices()->exists($params);
    }

}