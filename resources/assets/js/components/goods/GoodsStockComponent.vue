<template>
    <div id="app">
        <el-dialog title="兌換數量維護" :visible.sync="GoodsStockVisible" width="70%" v-loading="loading">
            <template>
                <el-row>
                    商品：{{goods.goods_name}}/{{goods.goods_num}}
                </el-row>
                <el-row style="color: red;">
                    兌換金幣數 {{goods.exchange_gold}} / 可兌換商品數量 {{goods.goods_stock}} / 累積已兌換數量 {{goods.goods_exchanged}} / 累積提供兌換數量 {{goods.goods_stock > 0 ? goods.goods_sum : goods.goods_exchanged}}
                </el-row>
                <el-row>
                    <el-col :span="24" style="margin-top: 20px;">
                        <el-button type="primary" icon="el-icon-circle-plus" @click="addStock">新增</el-button>
                    </el-col>
                </el-row>
                <el-row>
                    <el-table :data="stock_logs" stripe style="width: 100%" >
                        <el-table-column prop="status" label="欲新增或減少兌換">
                            <template slot-scope="scope" >
                                <el-select v-model="scope.row.is_inc" v-if="scope.row.id === 0">
                                    <el-option label="新增" value="1"></el-option>
                                    <el-option label="減少" value="0"></el-option>
                                </el-select>
                                <template v-else>
                                    <span v-if="parseInt(scope.row.is_inc) === 1">
                                        新增
                                    </span>
                                    <span v-else>
                                        減少
                                    </span>
                                </template>
                            </template>
                        </el-table-column>
                        <el-table-column prop="number" label="異動的兌換量">
                            <template slot-scope="scope" >
                                <el-input-number v-if="scope.row.id === 0" v-model="scope.row.number" :min="0" :max="999999"></el-input-number>
                                <span v-else>{{scope.row.number}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="goods_stock" label="備註">
                            <template slot-scope="scope" >
                                <el-input v-if="scope.row.id === 0" v-model="scope.row.remark" :maxlength="400" placeholder="長度限制400字"></el-input>
                                <span v-else :style="scope.row.is_inc===2?'color:#F56C6C':''">{{scope.row.remark}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="goods_exchange" label="最後異動資訊">
                            <template slot-scope="scope" >
                                <el-button v-if="scope.row.id === 0" type="primary" size="small" @click="saveStock(scope.$index)">儲存</el-button>
                                <span v-else>{{scope.row.create_time}}&nbsp;&nbsp;{{scope.row.account}}</span>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-row>
            </template>
        </el-dialog>
    </div>
</template>

<script>
    import dialog from '../../tools/element-ui-dialog'
    export default {
        name: "GoodsStockComponent",
        data:function () {
            return {
                loading: false,
                goods:{
                    shop_id:0,
                    goods_name:"",
                    goods_num:"",
                    goods_id:0,
                    goods_sum:0,
                    exchange_gold:0,
                    goods_stock:0,
                    goods_exchanged:0,
                },
                stock_logs:[

                ],
                GoodsStockVisible:false,
                dialog:dialog(this)
            }
        },
        methods:{
            addStock() {
                this.stock_logs.push({
                   id:0,
                   remark:"",
                   number:0,
                   is_inc:"1",
                   create_time:new Date().Format('yyyy-MM-dd hh:mm:ss'),
                   edit:true
                });
            },

            saveStock(index) {
                let t = this.stock_logs[index];
                if(t.number<=0||(parseInt(t.is_inc) !== 1&&t.number>this.goods.goods_stock)){
                    this.dialog.openError(null,'數量不足')
                    return
                }
                let url = '/goods/stock/add';
                if(parseInt(t.is_inc) !== 1) {
                    url = '/goods/stock/sub';
                }
                t.goods_id = this.goods.goods_id;
                this.dialog.openSelfDialog((callback)=>{
                    axios.post(url,t)
                        .then((response) => {
                            if(response.data.code === 0) {
                                this.dialog.openSuccess(function () {
                                    setTimeout(()=>{
                                        window.location.reload(true);
                                    },2000);
                                }, '新增成功');
                            }else if(response.data.code === 300300) {
                                this.dialog.openWarning(()=>{
                                    callback();
                                }, '庫存不支持操作');
                            }else{
                                this.dialog.openWarning(()=>{
                                    callback();
                                },'操作失敗');
                            }
                        })
                        .catch((error) => {
                            this.dialog.openWarning(()=>{
                                callback();
                            },'操作失敗');
                        });
                }, '確定要修改商品庫存嘛？');

            },

            showStockLog(goodsId) {
                if(goodsId < 0) {
                    this.dialog.openWarning(null, '錯誤的商品！');
                    return
                }

                this.getGoodsStock(goodsId);
                this.getGoodsLogs(goodsId);
                this.GoodsStockVisible = true;
            },

            getGoodsStock(goodsId) {
                this.loadings++;
                axios.get('/goods/stock?id=' + goodsId)
                    .then((response) => {
                        if (response.data.code === 0) {
                            this.goods = response.data.response.data;
                            this.goods.goods_id = parseInt(goodsId);
                        } else {
                            this.dialog.openSuccess(() => {
                                this.loadings--;
                                this.closeStock();
                            }, '獲取失敗');
                        }
                    })
                    .catch((error) => {
                        this.dialog.openSuccess(() => {
                            this.loadings--;
                            this.closeStock();
                        }, '獲取失敗');
                    });
            },

            getGoodsLogs(goodsId) {
                this.loading = true;
                axios.get('/goods/stock/logs?id=' + goodsId)
                    .then((response) => {
                        this.loading = false;
                        if (response.data.code === 0) {
                            this.stock_logs = response.data.response.list;
                        } else {
                            this.dialog.openSuccess(() => {
                                this.closeStock();
                            }, '獲取失敗');
                        }
                    })
                    .catch((error) => {
                        this.loading = false;
                        this.dialog.openSuccess(() => {
                            this.closeStock();
                        }, '獲取失敗');
                    });
            },

            closeStock() {
                this.GoodsStockVisible = false;
                this.goods={
                    shop_id:0,
                    shop_name:"",
                    goods_name:"",
                    goods_sum:0,
                    goods_id:0,
                    exchange_gold:0,
                    goods_stock:0,
                    goods_exchanged:0,
                };
                this.stock_logs = [];
            }
        }
    }
</script>