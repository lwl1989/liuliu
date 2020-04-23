<template>
    <div id="app">
        <el-row>
            <el-form :inline="true" :model="search" class="demo-form-inline" size="small">
                <el-form-item label="供應商:">
                    <el-select v-model="search.shop_type" style="width:100px">
                        <el-option label="全部" value="-1"></el-option>
                        <el-option label="特約商店" value="1"></el-option>
                        <el-option label="公益團體" value="2"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="商品狀態:">
                    <el-select v-model="search.goods_status" style="width:100px">
                        <el-option label="全部" value="-1"></el-option>
                        <el-option label="本月主打" value="3"></el-option>
                        <el-option label="已上架" value="2"></el-option>
                        <el-option label="未上架" value="1"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="search.date_type" style="width:125px">
                        <el-option label="最後異動時間" value="update_time"></el-option>
                        <el-option label="上架時間" value="online_time"></el-option>
                        <el-option label="下架時間" value="offline_time"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-date-picker
                            style="width:230px"
                            :change="dateSelect"
                            v-model="search.date_value"
                            type="daterange"
                            range-separator="~"
                            start-placeholder="開始日期"
                            end-placeholder="結束日期"
                    >
                    </el-date-picker>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="search.goods_type" style="width:112px">
                        <el-option label="商品名稱" value="name"></el-option>
                        <el-option label="商品編號" value="number"></el-option>
                        <el-option label="供應商名稱" value="distributor_name"></el-option>
                        <el-option label="供應商代號" value="distributor_account"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-input v-model="search.goods_value" style="width:180px"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="search.num_type" style="width:140px">
                        <el-option label="可兌換商品數量" value="exchange"></el-option>
                        <el-option label="兌換金幣數" value="gold_num"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="search.num_operation" style="width:62px">
                        <el-option label=">" value=">"></el-option>
                        <el-option label=">=" value=">="></el-option>
                        <el-option label="=" value="="></el-option>
                        <el-option label="<" value="<"></el-option>
                        <el-option label="<=" value="<="></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-input v-model="search.num_value" style="width:100px"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="loadData(1, true)">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>

        <el-row>
            <el-col :span="24">
                <el-button-group>
                    <el-button size="small" type="primary" icon="el-icon-news" @click="openWindowGoods(0)">新增</el-button>
                    <el-button size="small" type="primary" icon="el-icon-goods" @click="saleSwitch(2)">上架</el-button>
                    <el-button size="small" type="primary" icon="el-icon-sold-out" @click="saleSwitch(1)">下架</el-button>
                </el-button-group>
                <el-button-group size="small">
                    <el-button size="small" type="primary" @click="recommendSwitch(1)">本月主打</el-button>
                    <el-button size="small" type="primary" @click="recommendSwitch(0)">取消本月主打</el-button>
                </el-button-group>
                <el-button-group size="small">
                    <el-button size="small" type="primary" icon="el-icon-edit" @click="stockChange">兌換數量維護</el-button>
                    <el-button size="small" type="primary" icon="el-icon-menu" @click="goCategory">類別維護</el-button>
                </el-button-group>
                <el-button-group>
                    <el-button size="small" type="success" @click="statusSwitch(2)">全部上架</el-button>
                    <el-button size="small" type="warning" @click="statusSwitch(1)">全部下架</el-button>
                    <el-button size="small" type="danger" @click="stockClearAll">全部庫存歸零</el-button>
                </el-button-group>
            </el-col>
        </el-row>

        <el-row>
            <el-col :span="24" style="margin-top:20px;">
                <el-table :data="goods" stripe style="width:100%" v-loading="loading" @selection-change="goodsSelect" empty-text="目前沒有符合資料">
                    <el-table-column type="selection" ></el-table-column>
                    <el-table-column prop="goods_cover" label="商品圖示">
                        <template slot-scope="scope">
                            <img :src="scope.row.goods_cover[0].url" alt="" style="width: 50px;height: 50px">
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_name" label="商品資訊">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color:#00afff;" @click="openWindowGoods(scope.row.id)">
                                {{scope.row.goods_name}} {{scope.row.goods_num ? '/' + scope.row.goods_num : ''}}
                            </a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="商品狀態">
                        <template slot-scope="scope">
                            <span v-if="scope.row.status == 2">
                                <span v-if="scope.row.recommend == 1" style="color:#F56C6C">
                                    本月主打
                                </span>
                                <span v-else>
                                    已上架
                                </span>
                            </span>
                            <span v-else>
                                未上架
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="shop_name" label="供應商">
                        <template slot-scope="scope">
                            {{scope.row.shop_name}}/{{scope.row.type === 1 ? '特約商店' : '公益團體'}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="exchange_gold" label="兌換金幣數"></el-table-column>
                    <el-table-column prop="goods_stock" label="可兌換商品數量"></el-table-column>
                    <el-table-column prop="goods_exchange" label="累積已兌換量">
                        <template slot-scope="scope">
                                {{ scope.row.goods_exchanged}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="online_time" label="上架時間">
                        <template slot-scope="scope">
                            <span v-if="scope.row.online_time == null">-</span>
                            <span v-else>{{scope.row.online_time}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="offline_time" label="下架時間">
                        <template slot-scope="scope">
                            <span v-if="scope.row.offline_time == null">-</span>
                            <span v-else>{{scope.row.offline_time}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="update_time" label="最後異動時間"></el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10, 20, 30, 50, 100]"
                            @size-change="limitChange"
                            @current-change="loadData"
                            :current-page="search.page"
                            :page-size="search.limit"
                            layout="sizes, total, prev, pager, next"
                            :total="total"
                    >
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <goods-stock-component ref="GoodsStockComponent"></goods-stock-component>
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools'
    import GoodsStockComponent from './GoodsStockComponent'

    export default {
        name: "goods-list",
        components: {GoodsStockComponent},
        data: function () {
            return {
                total: 1,
                search : {
                    page : 1,
                    limit : 10,

                    shop_type : '-1',

                    goods_status : '-1',
                    goods_type : 'name',
                    goods_value : '',

                    num_type : 'exchange',
                    num_operation : '>',
                    num_value : '',

                    date_type : 'update_time',
                    date_value : [
                        new Date().getTime()-3600*1000*24*30*3,
                        new Date().getTime()
                    ]
                },
                isSearch : false,
                loading: true,
                goods: [],
                selectIds:[],
                dialog:new Tools.Dialog(this)
            }
        },

        created() {
            this.loadData();
        },

        methods: {
            limitChange (val) {
                this.search.limit = val;
                this.loadData();
            },

            loadData(page, isSearch) {
                if (isSearch) {
                    this.isSearch = true;
                }

                this.search.page = page ? page : 1;
                let search = JSON.parse(JSON.stringify(this.search));

                if (!this.isSearch) {
                    search.date_value = '';
                }

                this.loading = true;
                axios.get('/goods/select'+Tools.BuildQueryString(search)).then((response) => {
                    this.loading = false;
                    this.total = response.data.response.count;
                    this.goods = response.data.response.list;
                }).catch((error) => {
                    this.loading = false;
                    console.error(error)
                });
            },

            dateSelect() {
            },

            goodsLoad () {
                this.loadData();
            },

            stockChange(){
                if(this.selectIds.length === 0) {
                    return this.dialog.openError(null, '請至少選擇一筆資料');
                }

                if(this.selectIds.length > 1) {
                    return this.dialog.openError(null, '一次只能維護一筆資料');
                }

                this.$refs.GoodsStockComponent.showStockLog(this.selectIds[0]);
            },

            recommendSwitch(status){
                if (this.selectIds.length === 0) {
                    return this.dialog.openWarning(null, '請至少選擇一筆資料！');
                }

                let h = this.$createElement;
                this.dialog.openSelfDialog((closeCallback) => {
                    axios.post("/goods/recommend/"+status, {ids: this.selectIds}).then((response) => {
                        if (response.data.code > 0) {
                            return this.dialog.openWarning(closeCallback, '操作失敗！');
                        }

                        closeCallback();
                        this.goods.forEach((item) => {
                            if (this.selectIds.indexOf(item.id) >= 0) {
                                item.status = status;
                            }
                        });

                        this.dialog.openSuccess(() => {
                            this.goodsLoad();
                        });
                    }).catch(function (error) {
                        closeCallback();

                        this.dialog.openWarning(() => {
                                this.goodsLoad();
                            }, error.toString()
                        );
                    });
                }, h('p', null, [
                    h('span', null, status >= 1 ? '確定設為本月主打？設定的商品將會顯示於手機端的本月主打頁。' : '確定取消本月主打？取消後的主打商品會移至手機端的最新上架頁。')
                ]), '執行中...', '確定');
            },

            saleSwitch(status){
                if (this.selectIds.length === 0) {
                    return this.dialog.openError(null, '請至少選擇一筆資料');
                }

                let h = this.$createElement;
                let url = status >= 2 ? '/goods/sale/on' : '/goods/sale/off';

                this.dialog.openSelfDialog((closeCallback) => {
                    axios.post(url, {ids:this.selectIds}).then((response) => {
                        if (response.data.code > 0) {
                            return this.dialog.openError(closeCallback, '操作失敗！');
                        }

                        closeCallback();

                        this.goods.forEach((item) => {
                            if(this.selectIds.indexOf(item.id) >= 0) {
                                item.status = status;
                            }
                        });

                        this.dialog.openSuccess(() => {
                            this.goodsLoad()
                        });
                    }).catch((error) => {
                        closeCallback();

                        this.dialog.openWarning(() => {
                            this.goodsLoad();
                        }, error.toString());
                    });
                }, h('p', null, [
                    h('span', null, status >= 2 ? '確定上架？商品上架後將顯示於手機端。' : '確定下架？商品下架後將無法顯示於手機端。')
                ]), '執行中...', '確定');
            },

            statusSwitch(status) {
                let h = this.$createElement;

                this.dialog.openSelfDialog((callback) => {
                    axios.put('/goods/status/switch/'+status).then((response) => {
                        if(response.data.code > 0 || response.data.response <= 0) {
                            return this.dialog.openError(callback);
                        }

                        callback();
                        this.dialog.openSuccess(() => {
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        });
                    }).catch(() => {
                        this.dialog.openWarning(callback);
                    });
                }, h('p', null, [
                    h('span', null, status >=2 ? '確定上架？商品上架後將會顯示於手機端。' : '確定下架？商品下架後將無法顯示於手機端。'),
                ]), '執行中...', '確定');
            },

            stockClearAll() {
                let h = this.$createElement;

                this.dialog.openSelfDialog((callback) => {
                    axios.delete('/goods/stock/clear').then((response) => {
                        if(response.data.code > 0 || response.data.response <= 0) {
                            return this.dialog.openError(callback, '操作失敗');
                        }

                        callback();
                        this.dialog.openSuccess(() => {
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        });
                    }).catch(() => {
                        this.dialog.openError(callback, '操作失敗');
                    });
                }, h('p', null, [
                    h('span', null, '確定將列表中全部商品庫存歸零？商品仍顯示於手機端但無法兌換。'),
                ]), '執行中...', '確定');
            },

            goCategory() {
                this.$router.push({path:'/goods/category/list'});
            },

            openWindowGoods(id) {
                Tools.OpenNewWindow(
                    "/#/edit/goods/"+id,
                    (id ? '編輯' : '新增') + "商品",
                    800,
                    1024
                );
            },

            goodsSelect(rows) {
                let ids = [];
                rows.forEach(function (item) {
                    ids.push(item.id);
                });
                this.selectIds = ids;
            }
        }
    }
</script>

<style scoped>

</style>