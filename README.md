#### 圆通快递PHP-SDK

害, 太能折腾了, 刚写完顺丰, 转过来就要对接圆通, 真想用快递鸟等集成接口. 当然, 为了方便以后使用, 我就把这些都整理成了包, 要用的时候, 直接安装就行了. 自然, 有问题, 修改再发布, 也还是可以的. 

* 基础说明

搞不懂圆通那么多同值的字段是为啥, 一会儿客户编码, 一会儿渠道编码, 一会儿客户标识. 还有就是校验码当 `partnerID` 在用, 唬我不知道`abcd`么. 众多不同的名称实在令我有些头昏眼花, 希望以后圆通那边的技术能统一一下名称什么的吧.

* 接口情况
    * 订单服务类
        * 配置参考
            * 客户编码 TEST
            * 校验码 123456
        * 订单创建接口[已对接] `quickOrderGeneral`
            * 调用地址 http://opentestapi.yto.net.cn/service/order_create/v1/oksM7N
        * 订单取消接口[已对接] `quickOrderCancel`
            * 调用地址 http://opentestapi.yto.net.cn/service/order_cancel/v1/oksM7N
        * 物流状态通知接口[未对接]
        
    * 查询服务类
        * 配置参考
            * 客户编码 YTOTEST
            * 校验码 1QLlIZ
        * 标准运价查询接口[已对接] `queryTransportPrice`
            * 调用地址 http://opentestapi.yto.net.cn/service/charge_query/v1/oksM7N
            * 方法名 yto.Marketing.TransportPrice
        * 走件流程查询接口[已对接] `queryTrace`
            * 调用地址 http://opentestapi.yto.net.cn/service/waybill_query/v1/oksM7N
            * 方法名 yto.Marketing.WaybillTrace
        * 根据省ID查询市接口[已对接] `queryProvinceOfCity`
            * 调用地址  http://opentestapi.yto.net.cn/service/subcity_query/v1/oksM7N
            * 方法名 yto.BaseData.ProvinceOfCity
        * 根据市ID查询下属网点接口[已对接] `queryCity`
            * 调用地址 http://opentestapi.yto.net.cn/service/subnetwork_query/v1/oksM7N
            * 方法名 yto.BaseData.CityOfStation
        * 根据网点ID查询网点服务信息接口[已对接] `queryNetWorkService`
            * 调用地址  http://opentestapi.yto.net.cn/service/newtwork_service_query/v1/oksM7N
            * 方法名 yto.BaseData.StationInfo
            
    * 电子面单类
        * 配置参考
            * 客户编码 K21000119
            * 校验码 u2Z1F7Fh
        * 电子面单下单接口[已对接] `quickOrderElectronic`
            * 调用地址 http://opentestapi.yto.net.cn/service/e_order_create/v1/oksM7N
        * 省市区地址合并拉单接口[未对接]
        * 海外G面单接口[未对接]