<?php
/**
 * Order.php
 *
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.niushop.com.cn
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : niuteam
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */

namespace data\service\Order;


use data\model\NsGoodsSkuPictureModel;
use data\model\NsOrderGoodsModel;
use data\model\NsOrderModel;
use data\service\BaseService;


/**
 * 订单操作类
 */
class Order extends BaseService
{
	public $order;//订单主表model
	public $order_goods;//订单项表model
	public $status = array(
		0 => [
			'status_id' => '0',
			'status_name' => '待付款',
			'is_refund' => 0, // 是否可以申请退款
			'operation' => array(
				'0' => array(
					'no' => 'pay',
					'name' => '线下支付',
					'color' => '#FF9800'
				),
				'1' => array(
					'no' => 'close',
					'color' => '#E61D1D',
					'name' => '交易关闭'
				),
				'2' => array(
					'no' => 'adjust_price',
					'color' => '#4CAF50',
					'name' => '修改价格'
				),
				'3' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				)
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'pay',
					'name' => '去支付',
					'color' => '#F15050',
					'class_name' => 'ns-bg-color'
				),
				
				'1' => array(
					'no' => 'close',
					'name' => '关闭订单',
					'color' => '#999999',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		1 => [
			'status_id' => '1',
			'status_name' => '待发货',
			'is_refund' => 1,
			'operation' => array(
				'0' => array(
					'no' => 'delivery',
					'color' => 'green',
					'name' => '发货'
				),
				'1' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'2' => array(
					'no' => 'update_address',
					'color' => '#51A351',
					'name' => '修改地址'
				)
			),
			'member_operation' => array()
		],
		2 => [
			'status_id' => '2',
			'status_name' => '已发货',
			'is_refund' => 1,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'1' => array(
					'no' => 'logistics',
					'color' => '#666666',
					'name' => '查看物流'
				),
				'2' => array(
					'no' => 'getdelivery',
					'name' => '确认收货',
					'color' => '#FF6600'
				)
			),
			
			'member_operation' => array(
				'0' => array(
					'no' => 'getdelivery',
					'name' => '确认收货',
					'color' => '#FF6600',
					'class_name' => 'ns-bg-color'
				),
				'1' => array(
					'no' => 'logistics',
					'color' => '#cccccc',
					'name' => '查看物流',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		3 => [
			'status_id' => '3',
			'status_name' => '已收货',
			'is_refund' => 0,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'1' => array(
					'no' => 'logistics',
					'color' => '#666666',
					'name' => '查看物流'
				)
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'logistics',
					'color' => '#cccccc',
					'name' => '查看物流',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		4 => [
			'status_id' => '4',
			'status_name' => '已完成',
			'is_refund' => 0,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'1' => array(
					'no' => 'logistics',
					'color' => '#666666',
					'name' => '查看物流'
				)
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'logistics',
					'color' => '#cccccc',
					'name' => '查看物流',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		5 => [
			'status_id' => '5',
			'status_name' => '已关闭',
			'is_refund' => 0,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				),
				'1' => array(
					'no' => 'delete_order',
					'color' => '#ff0000',
					'name' => '删除订单'
				)
			),
			'member_operation' => array(
				'0' => array(
					'no' => 'delete_order',
					'color' => '#ff0000',
					'name' => '删除订单',
					'class_name' => 'ns-bg-color-gray-shade-20'
				)
			)
		],
		-1 => [
			'status_id' => '-1',
			'status_name' => '退款中',
			'is_refund' => 1,
			'operation' => array(
				'0' => array(
					'no' => 'seller_memo',
					'color' => '#666666',
					'name' => '备注'
				)
			),
			'member_operation' => array()
		]
	);
	
	public $order_form = array(
		1 => [
			'type_id' => '1',
			'type_name' => '微信端',
			'tag' => 'fa fa-weixin'
		],
		2 => [
			'type_id' => '2',
			'type_name' => '手机端',
			'tag' => 'fa fa-mobile fa-2x'
		],
		3 => [
			'type_id' => '3',
			'type_name' => 'pc端',
			'tag' => 'fa fa-television'
		],
		4 => [
			'type_id' => '4',
			'type_name' => '微信小程序端',
			'tag' => 'fa fa-wechat-applet'
		],
	);
	
	
	public $pay_type = array(
		0 => [
			'type_id' => '0',
			'type_name' => '在线支付'
		],
		1 => [
			'type_id' => '1',
			'type_name' => '微信支付'
		],
		2 => [
			'type_id' => '2',
			'type_name' => '支付宝'
		],
		3 => [
			'type_id' => '3',
			'type_name' => '银联卡'
		],
		4 => [
			'type_id' => '4',
			'type_name' => '货到付款'
		],
		5 => [
			'type_id' => '5',
			'type_name' => '余额支付'
		],
		6 => [
			'type_id' => '6',
			'type_name' => '到店支付'
		],
		10 => [
			'type_id' => '10',
			'type_name' => '线下支付'
		],
		11 => [
			'type_id' => '11',
			'type_name' => '积分兑换'
		],
		12 => [
			'type_id' => '12',
			'type_name' => '砍价'
		]
	);
	public $shipping_type = array(
		1 => [
			'type_id' => '1',
			'type_name' => '物流配送'
		],
		2 => [
			'type_id' => '2',
			'type_name' => '买家自提'
		],
		3 => [
			'type_id' => '3',
			'type_name' => '本地配送'
		]
	);
	
	public $shipping_status = array(
		0 => [
			'shipping_status' => '0',
			'status_name' => '待发货'
		],
		1 => [
			'shipping_status' => '1',
			'status_name' => '已发货'
		],
		2 => [
			'shipping_status' => '2',
			'status_name' => '已收货'
		],
		3 => [
			'shipping_status' => '3',
			'status_name' => '备货中'
		],
	);
	
	public $refund_status = array(
		'1' => array(
			'status_id' => '1',
			'status_name' => '买家申请退款',
			'status_desc' => '发起了退款申请,等待卖家处理',
			'refund_operation' => array(
				'0' => array(
					'no' => 'agree',
					'name' => '同意',
					'color' => '#4CAF50'
				),
				'1' => array(
					'no' => 'refuse',
					'name' => '拒绝',
					'color' => 'rgb(232, 80, 69)'
				)
			)
		),
		'2' => array(
			'status_id' => '2',
			'status_name' => '等待买家退货',
			'status_desc' => '卖家已同意退款申请,等待买家退货',
			'refund_operation' => array()
		),
		'3' => array(
			'status_id' => '3',
			'status_name' => '等待卖家确认收货',
			'status_desc' => '买家已退货,等待卖家确认收货',
			'refund_operation' => array(
				'0' => array(
					'no' => 'confirm_receipt',
					'name' => '确认收货',
					'color' => '#4CAF50'
				)
			)
		),
		'4' => array(
			'status_id' => '4',
			'status_name' => '等待卖家确认退款',
			'status_desc' => '卖家同意退款',
			'refund_operation' => array(
				'0' => array(
					'no' => 'confirm_refund',
					'name' => '确认退款',
					'color' => '#4CAF50'
				)
			)
		),
		'5' => array(
			'status_id' => '5',
			'status_name' => '退款已成功',
			'status_desc' => '卖家退款给买家，本次维权结束',
			'refund_operation' => array()
		),
		'-1' => array(
			'status_id' => '-1',
			'status_name' => '退款已拒绝',
			'status_desc' => '卖家拒绝本次退款，本次维权结束',
			'refund_operation' => array()
		),
		'-2' => array(
			'status_id' => '-2',
			'status_name' => '退款已关闭',
			'status_desc' => '主动撤销退款，退款关闭',
			'refund_operation' => array()
		),
		'-3' => array(
			'status_id' => '-3',
			'status_name' => '退款申请不通过',
			'status_desc' => '拒绝了本次退款申请,等待买家修改',
			'refund_operation' => array()
		)
	);
	
	
	public $pay_status = array(
		0 => [
			'pay_status' => '0',
			'status_name' => '待支付'
		],
		1 => [
			'pay_status' => '1',
			'status_name' => '支付中'
		],
		2 => [
			'pay_status' => '2',
			'status_name' => '已支付'
		]
	);
	
	// 订单主表
	function __construct()
	{
	    $this->init_status();
		parent::__construct();
		$this->order = new NsOrderModel();
		$this->order_goods = new NsOrderGoodsModel();
	}
	private function init_status()
    {
        $this->status = array(
            0 => [
                'status_id' => '0',
                'status_name' => lang('待付款'),
                'is_refund' => 0, // 是否可以申请退款
                'operation' => array(
                    '0' => array(
                        'no' => 'pay',
                        'name' => lang('线下支付'),
                        'color' => '#FF9800'
                    ),
                    '1' => array(
                        'no' => 'close',
                        'color' => '#E61D1D',
                        'name' => lang('交易关闭')
                    ),
                    '2' => array(
                        'no' => 'adjust_price',
                        'color' => '#4CAF50',
                        'name' => lang('修改价格')
                    ),
                    '3' => array(
                        'no' => 'seller_memo',
                        'color' => '#666666',
                        'name' => lang('备注')
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'pay',
                        'name' => lang('去支付'),
                        'color' => '#F15050',
                        'class_name' => 'ns-bg-color'
                    ),
                    
                    '1' => array(
                        'no' => 'close',
                        'name' => lang('关闭订单'),
                        'color' => '#999999',
                        'class_name' => 'ns-bg-color-gray-shade-20'
                    )
                )
            ],
            1 => [
                'status_id' => '1',
                'status_name' => lang('待发货'),
                'is_refund' => 1,
                'operation' => array(
                    '0' => array(
                        'no' => 'delivery',
                        'color' => 'green',
                        'name' => lang('发货')
                    ),
                    '1' => array(
                        'no' => 'seller_memo',
                        'color' => '#666666',
                        'name' => lang('备注')
                    ),
                    '2' => array(
                        'no' => 'update_address',
                        'color' => '#51A351',
                        'name' => lang('修改地址')
                    )
                ),
                'member_operation' => array()
            ],
            2 => [
                'status_id' => '2',
                'status_name' => lang('已发货'),
                'is_refund' => 1,
                'operation' => array(
                    '0' => array(
                        'no' => 'seller_memo',
                        'color' => '#666666',
                        'name' => lang('备注')
                    ),
                    '1' => array(
                        'no' => 'logistics',
                        'color' => '#666666',
                        'name' => lang('查看物流')
                    ),
                    '2' => array(
                        'no' => 'getdelivery',
                        'name' => lang('确认收货'),
                        'color' => '#FF6600'
                    )
                ),
                
                'member_operation' => array(
                    '0' => array(
                        'no' => 'getdelivery',
                        'name' => lang('确认收货'),
                        'color' => '#FF6600',
                        'class_name' => 'ns-bg-color'
                    ),
                    '1' => array(
                        'no' => 'logistics',
                        'color' => '#cccccc',
                        'name' => lang('查看物流'),
                        'class_name' => 'ns-bg-color-gray-shade-20'
                    )
                )
            ],
            3 => [
                'status_id' => '3',
                'status_name' => lang('已收货'),
                'is_refund' => 0,
                'operation' => array(
                    '0' => array(
                        'no' => 'seller_memo',
                        'color' => '#666666',
                        'name' => lang('备注')
                    ),
                    '1' => array(
                        'no' => 'logistics',
                        'color' => '#666666',
                        'name' => lang('查看物流')
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'logistics',
                        'color' => '#cccccc',
                        'name' => lang('查看物流'),
                        'class_name' => 'ns-bg-color-gray-shade-20'
                    )
                )
            ],
            4 => [
                'status_id' => '4',
                'status_name' => lang('已完成'),
                'is_refund' => 0,
                'operation' => array(
                    '0' => array(
                        'no' => 'seller_memo',
                        'color' => '#666666',
                        'name' => lang('备注')
                    ),
                    '1' => array(
                        'no' => 'logistics',
                        'color' => '#666666',
                        'name' => lang('查看物流')
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'logistics',
                        'color' => '#cccccc',
                        'name' => lang('查看物流'),
                        'class_name' => 'ns-bg-color-gray-shade-20'
                    )
                )
            ],
            5 => [
                'status_id' => '5',
                'status_name' => lang('已关闭'),
                'is_refund' => 0,
                'operation' => array(
                    '0' => array(
                        'no' => 'seller_memo',
                        'color' => '#666666',
                        'name' => lang('备注')
                    ),
                    '1' => array(
                        'no' => 'delete_order',
                        'color' => '#ff0000',
                        'name' => lang('删除订单')
                    )
                ),
                'member_operation' => array(
                    '0' => array(
                        'no' => 'delete_order',
                        'color' => '#ff0000',
                        'name' => lang('删除订单'),
                        'class_name' => 'ns-bg-color-gray-shade-20'
                    )
                )
            ],
            -1 => [
                'status_id' => '-1',
                'status_name' => lang('退款中'),
                'is_refund' => 1,
                'operation' => array(
                    '0' => array(
                        'no' => 'seller_memo',
                        'color' => '#666666',
                        'name' => lang('备注')
                    )
                ),
                'member_operation' => array()
            ]
        );
        $this->shipping_type = array(
            1 => [
                'type_id' => '1',
                'type_name' => lang('物流配送')
            ],
            2 => [
                'type_id' => '2',
                'type_name' => lang('买家自提')
            ],
            3 => [
                'type_id' => '3',
                'type_name' => lang('本地配送')
            ]
        );
        $this->refund_status = array(
            '1' => array(
                'status_id' => '1',
                'status_name' => lang('买家申请退款'),
                'status_desc' => lang('发起了退款申请,等待卖家处理'),
                'refund_operation' => array(
                    '0' => array(
                        'no' => 'agree',
                        'name' => lang('同意'),
                        'color' => '#4CAF50'
                    ),
                    '1' => array(
                        'no' => 'refuse',
                        'name' => lang('拒绝'),
                        'color' => 'rgb(232, 80, 69)'
                    )
                )
            ),
            '2' => array(
                'status_id' => '2',
                'status_name' => lang('等待买家退货'),
                'status_desc' => lang('卖家已同意退款申请,等待买家退货'),
                'refund_operation' => array()
            ),
            '3' => array(
                'status_id' => '3',
                'status_name' => lang('等待卖家确认收货'),
                'status_desc' => lang('买家已退货,等待卖家确认收货'),
                'refund_operation' => array(
                    '0' => array(
                        'no' => 'confirm_receipt',
                        'name' => lang('确认收货'),
                        'color' => '#4CAF50'
                    )
                )
            ),
            '4' => array(
                'status_id' => '4',
                'status_name' => lang('等待卖家确认退款'),
                'status_desc' => lang('卖家同意退款'),
                'refund_operation' => array(
                    '0' => array(
                        'no' => 'confirm_refund',
                        'name' => lang('确认退款'),
                        'color' => '#4CAF50'
                    )
                )
            ),
            '5' => array(
                'status_id' => '5',
                'status_name' => lang('退款已成功'),
                'status_desc' => lang('卖家退款给买家，本次维权结束'),
                'refund_operation' => array()
            ),
            '-1' => array(
                'status_id' => '-1',
                'status_name' => lang('退款已拒绝'),
                'status_desc' => lang('卖家拒绝本次退款，本次维权结束'),
                'refund_operation' => array()
            ),
            '-2' => array(
                'status_id' => '-2',
                'status_name' => lang('退款已关闭'),
                'status_desc' => lang('主动撤销退款，退款关闭'),
                'refund_operation' => array()
            ),
            '-3' => array(
                'status_id' => '-3',
                'status_name' => lang('退款申请不通过'),
                'status_desc' => lang('拒绝了本次退款申请,等待买家修改'),
                'refund_operation' => array()
            )
        );
        $this->pay_status = array(
            0 => [
                'pay_status' => '0',
                'status_name' => lang('待支付')
            ],
            1 => [
                'pay_status' => '1',
                'status_name' => lang('支付中')
            ],
            2 => [
                'pay_status' => '2',
                'status_name' => lang('已支付')
            ]
        );
    }
	
	/**
	 * 得到订单状态信息
	 */
	public function getOrderStatusInfo($param)
	{
		$status_info = $this->status[ $param["order_status"] ];
		$result = hook("getOrderStatusInfo", $param);
		
		$result = arrayFilter($result);
		if (!empty($result[0])) {
			$status_info = $result[0];
		}
		
		if (empty($status_info)) {
			return [
				'status_id' => $param["order_status"],
				'status_name' => '',
				'is_refund' => 0, // 是否可以申请退款
				'operation' => [],
				'member_operation' => []
			];
		}
		switch ($param["order_status"]) {
			case 1 :
				if ($param["shipping_type"] == 2) {
					$status_info['status_name'] = lang('待提货');
					$status_info['operation'] = array(
						'0' => array(
							'no' => 'pickup',
							'color' => '#FF9800',
							'name' => lang('提货')
						),
						'1' => array(
							'no' => 'seller_memo',
							'color' => '#666666',
							'name' => lang('备注')
						)
					);
					$status_info['member_operation'] = array(
						'0' => array(
							'no' => 'member_pickup',
							'color' => '#FF9800',
							'name' => lang('提货'),
							'class_name' => 'ns-bg-color'
						)
					);
					
				} else if ($param["shipping_type"] == 3) {
					$status_info['operation'] = array(
						'0' => array(
							'no' => 'o2o_delivery',
							'color' => 'green',
							'name' => lang('发货')
						),
						'1' => array(
							'no' => 'seller_memo',
							'color' => '#666666',
							'name' => lang('备注')
						),
						'2' => array(
							'no' => 'update_address',
							'color' => '#51A351',
							'name' => lang('修改地址')
						)
					);
					$status_info['member_operation'] = array();
				}
				break;
			case 3 :
				if ($param["shipping_type"] == 2) {
					$status_info['status_name'] = lang('已提货');
					$status_info['operation'] = array(
						'0' => array(
							'no' => 'seller_memo',
							'color' => '#666666',
							'name' => lang('备注')
						),
						'1' => array(
							'no' => 'logistics',
							'color' => '#51A351',
							'name' => lang('查看物流')
						)
					);
					$status_info['member_operation'] = array();
					
				}
		}
		if ($param["shipping_type"] == 0) {
			if (!empty($status_info['operation'])) {
				foreach ($status_info['operation'] as $k => $v) {
					if ($v["name"] == '查看物流') {
						unset($status_info['operation'][ $k ]);
						sort($status_info['operation']);
						continue;
					}
				}
				foreach ($status_info['member_operation'] as $k => $v) {
					if ($v["name"] == '查看物流') {
						unset($status_info['member_operation'][ $k ]);
						sort($status_info['member_operation']);
						continue;
					}
				}
			}
			
		}
		return $status_info;
	}
	
	/**
	 * 所有订单状态
	 */
	public function getOrderStatus($data)
	{
		$result = hook("getOrderStatus", $data);
		$result = arrayFilter($result);
		if (!empty($result[0])) {
//             sort($result[0]);
			return $result[0];
		}
//         sort($this->status);
		return $this->status;
	}
	
	/***
	 * 支付方式
	 */
	public function getPayType($param)
	{
		$result = hook("getPayType", $param);
		$result = arrayFilter($result);
		if (!empty($result[0])) {
			return $result[0];
		}
		return $this->pay_type;
	}
	
	/***
	 * 支付方式
	 */
	public function getPayTypeInfo($param)
	{
		$result = hook("getPayType", $param);
		$result = arrayFilter($result);
		if (!empty($result[0])) {
			return $result[0];
		}
		return $this->pay_type[ $param["pay_type"] ];
	}
	
	/**
	 * 物流方式
	 */
	public function getShippingType($param)
	{
		$result = hook("getShippingType", $param);
		$result = arrayFilter($result);
		if (!empty($result[0])) {
			return $result[0];
		}
		return $this->shipping_type;
	}
	
	/**
	 * 物流方式
	 */
	public function getShippingTypeInfo($param)
	{
		$result = hook("getShippingTypeInfo", $param);
		$result = arrayFilter($result);
		if (!empty($result[0])) {
			return $result[0];
		}
		return $this->shipping_type[ $param["shipping_type"] ];
	}
	
	/**
	 * 物流状态
	 */
	public function getShippingStatus($param)
	{
		$result = hook("getShippingStatus", $param);
		$result = arrayFilter($result);
		if (!empty($result[0])) {
			return $result[0];
		}
		return $this->shipping_status;
	}
	
	/**
	 * 物流状态
	 */
	public function getShippingStatusInfo($param)
	{
		$result = hook("getShippingStatusInfo", $param);
		$result = arrayFilter($result);
		if (!empty($result[0])) {
			return $result[0];
		}
		return $this->shipping_status[ $param["shipping_status"] ];
	}
	
	/**
	 * 得到订单退款状态
	 */
	public function getOrderRefundStatus($param = [])
	{
		$result = hook("getOrderRefundStatus", $param);
		$result = arrayFilter($result);
		if (!empty($result)) {
			return $result;
		}
		return $this->refund_status;
		
	}
	
	/**
	 * 得到订单退款状态
	 */
	public function getOrderRefundStatusInfo($param)
	{
		$result = hook("getOrderRefundStatusInfo", $param);
		$result = arrayFilter($result);
		if (!empty($result)) {
			return $result;
		}
		return $this->refund_status[ $param["refund_status"] ];
	}
	
	/**
	 * 获取订单类型
	 */
	public function getOrderTypeInfo($param)
	{
		$result = hook("getOrderTypeInfo", $param);
		$result = arrayFilter($result);
		if (!empty($result[0])) {
			return $result[0];
		}
		return [
			'id' => 1,
			'name' => lang('普通订单')
		];
		
	}
	
	/**
	 * 获取订单类型列表
	 */
	public function getOrderType()
	{
		$order_type_arr = [
			[
				'id' => 1,
				'name' => lang('普通订单')
			]
		];
		$hook_result = hook("getOrderType");
		$hook_result = arrayFilter($hook_result);
		$result = $order_type_arr;
		if (!empty($hook_result) && $hook_result != null) {
			$result = array_merge($order_type_arr, $hook_result);
		}
		return $result;
	}
	
	/**
	 * 订单来源
	 */
	public function getOrderFormInfo($param)
	{
		return $this->order_form[ $param["order_form"] ];
	}
	
	/**
	 * 支付状态
	 */
	public function getPayStatus($param)
	{
		return $this->pay_status;
	}
	
	/**
	 * 支付状态
	 */
	public function getPayStatusInfo($param)
	{
		return $this->pay_status[ $param["pay_status"] ];
	}
	
	/**
	 * 营销类型
	 */
	public function getPrtomotionType()
	{
		$list = hook("getPromotionType", []);
		$list = arrayFilter($list);
		$list[] = array(
			"id" => 4,
			"name" => "积分兑换"
		);
		return $list;
	}
	
	/**
	 * 营销类型详情
	 */
	public function getPrtomotionTypeInfo($param)
	{
		$info = hook("getPromotionTypeInfo", $param);
		$info = arrayFilter($info);
		if (!empty($info[0])) {
			return $info[0];
		}
		
		if ($param["promotion_type"] == 4) {
			return array(
				"id" => 4,
				"name" => "积分兑换"
			);
		}
		return [];
	}
	
	/**
	 * 根据商品规格信息查询SKU主图片
	 */
	public function getSkuPictureBySkuId($goods_sku_info)
	{
		$picture = 0;
		$attr_value_items = $goods_sku_info['attr_value_items'];
		if (!empty($attr_value_items)) {
			$attr_value_items_array = explode(";", $attr_value_items);
			foreach ($attr_value_items_array as $k => $v) {
				$temp_array = explode(":", $v); // 规格：规格值
				$condition['goods_id'] = $goods_sku_info['goods_id'];
				$condition['spec_id'] = $temp_array[0]; // 规格
				$condition['spec_value_id'] = $temp_array[1]; // 规格值
				$condition['shop_id'] = $this->instance_id;
				$goods_sku_picture_model = new NsGoodsSkuPictureModel();
				$sku_img_array = $goods_sku_picture_model->getInfo($condition, 'sku_img_array');
				if (!empty($sku_img_array['sku_img_array'])) {
					$temp = explode(",", $sku_img_array['sku_img_array']);
					$picture = $temp[0];
					break;
				}
			}
		}
		
		return $picture;
	}
}