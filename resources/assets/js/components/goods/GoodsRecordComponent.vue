<template>
    <div id="app">
        <el-row>
            <el-form :inline="true" :model="search" class="demo-form-inline" size="small">
                <el-form-item label="商店:">
                    <el-select v-model="search.shop_type" style="width:100px">
                        <el-option label="全部商店" value="-1"></el-option>
                        <el-option
                            v-for="item in admins"
                            :label="item.name"
                            :value="item.shop_id"
                            :key="item.shop_id">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="商品:" v-if="search.shop_type !== '-1'">
                    <el-select v-model="search.goods_type" style="width:100px">
                        <el-option label="全部商品" value="-1"></el-option>
                        <el-option
                            v-for="item in goods_types"
                            :label="item.goods_name"
                            :value="item.id"
                            :key="item.id">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="兌換日期:"></el-form-item>
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
                    <el-button type="primary" @click="loadData(1, true)">查詢</el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="loadData(1, true, 1)">匯出</el-button>
                </el-form-item>
            </el-form>
        </el-row>

        <el-row>
            <el-col :span="24">
                <el-button-group>
                    <el-button size="small" type="success" @click="openWindowGoods">兌換排行榜</el-button>
                </el-button-group>
            </el-col>
        </el-row>

        <el-row>
            <el-col :span="24" style="margin-top:20px;">
                <el-table :data="goodsRecord" stripe style="width:100%" v-loading="loading" @selection-change="goodsSelect" empty-text="目前沒有符合資料">
                    <el-table-column prop="offline_time" label="兌換時間">
                        <template slot-scope="scope">
                            {{scope.row.create_time}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_exchange" label="商店名稱">
                        <template slot-scope="scope">
                            {{ scope.row.shop_name}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_cover" label="商品圖示">
                        <template slot-scope="scope">
                            <img :src="scope.row.cover_url" alt="" style="width: 50px;height: 50px">
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_name" label="商品名稱">
                        <template slot-scope="scope">
                            {{ scope.row.remark}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_exchange" label="兌換金幣數">
                        <template slot-scope="scope">
                            {{ scope.row.exchange_gold}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_exchange" label="兌換數量">
                        <template slot-scope="scope">
                            {{ scope.row.exchange_num}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_exchange" label="總金幣數">
                        <template slot-scope="scope">
                            {{ scope.row.number}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_exchange" label="參考轉換金額">
                        <template slot-scope="scope">
                            {{ scope.row.exchange_money}}
                        </template>
                    </el-table-column>
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
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools'

    export default {
        name: "goods-record-list",
        data: function () {
            return {
                total: 1,
                search : {
                    page : 1,
                    limit : 10,
                    shop_type : '-1',
                    goods_type : '-1',
                    date_value : [
                        new Date(new Date().getFullYear(), new Date().getMonth(), 1),
                        new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate())
                    ],
                    date : []
                },
                isSearch : false,
                loading: true,
                goodsRecord: [],
                selectIds:[],
                dialog:new Tools.Dialog(this),
                admins:[],
                goods_types:[]
            }
        },

        watch: {
            'search.shop_type' : function (val) {
                if (val === '-1') {
                    this.search.goods_type = '-1';
                } else {
                    this.getGoodsType();
                }
            }
        },

        created() {
            this.loadData();
            this.getAdmin();
        },

        methods: {
            limitChange (val) {
                this.search.limit = val;
                this.loadData();
            },

            loadData(page, isSearch, type) {
                if (isSearch) {
                    this.isSearch = true;
                }

                this.search.page = page ? page : 1;
                this.search.type = type;
                this.search.date[0] = this.search.date_value[0].getTime().toString();
                this.search.date[1] = this.search.date_value[1].getTime().toString();
                let search = JSON.parse(JSON.stringify(this.search));
                this.loading = true;
                console.log(Tools.BuildQueryString(search));

                let url = '/goods/record/select' + Tools.BuildQueryString(search);
                if (type) {
                    this.loading = false;
                    window.location.href = url;
                } else {
                    axios.get(url).then((response) => {
                        this.loading = false;
                        this.goodsRecord = response.data.response.list;
                    }).catch((error) => {
                        this.loading = false;
                        console.error(error)
                    });

                    axios.get('/goods/record/count'+Tools.BuildQueryString(search)).then((response) => {
                        this.loading = false;
                        this.total = response.data.response.count;
                    }).catch((error) => {
                        this.loading = false;
                        console.error(error)
                    });
                }
            },

            getAdmin() {
                axios.get('/admin/select?limit=10000&field=id,name,type&role=1').then((response) => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        if (response.data.response.admin.length === 0) {
                            this.closeWindow('商店列表爲空');
                            return;
                        }

                        this.admins = response.data.response.admin;
                        return;
                    }
                }).catch(function () {
                    this.loading = false;
                    this.closeWindow('獲取商店列表失敗');
                });
            },

            getGoodsType() {
                axios.get('/goods/record/shop/' + this.search.shop_type).then((response) => {
                    this.loading = false;
                    if (response.data.code === 0) {
                        if (response.data.response.goods.length === 0) {
                            this.goods_types = [];
                            this.closeWindow('商店列表爲空');
                            return;
                        }

                        this.goods_types = response.data.response.goods;
                        return;
                    }
                }).catch(function () {
                    this.loading = false;
                    this.closeWindow('獲取商店列表失敗');
                });
            },

            dateSelect() {

            },

            openWindowGoods() {
                Tools.OpenNewWindow(
                    "/#/edit/record",
                    "兌換排行榜",
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