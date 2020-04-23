<template>
    <div id="app" v-loading="loading">
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
                <el-form-item label="商品狀態:">
                    <el-select v-model="search.goods_status" style="width:100px">
                        <el-option label="全部狀態" value="-1"></el-option>
                        <el-option label="本月主打" value="1"></el-option>
                        <el-option label="已上架" value="2"></el-option>
                        <el-option label="已下架" value="3"></el-option>
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
                <el-form-item label="排行類型:">
                    <el-select v-model="search.goods_rank_type" style="width:100px">
                        <el-option label="兌換總金幣數" value="1"></el-option>
                        <el-option label="兌換總數量" value="2"></el-option>
                    </el-select>
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
            <el-col :span="24" style="margin-top:20px;">
                <el-table :data="goodsRecordDetail" stripe style="width:100%" v-loading="loading" @selection-change="goodsSelect" empty-text="目前沒有符合資料">
                    <el-table-column prop="id" label="名次">
                        <template slot-scope="scope">
                            {{scope.row.id}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_name" label="商品名稱">
                        <template slot-scope="scope">
                            {{scope.row.goods_name}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="shop_name" label="商店名稱">
                        <template slot-scope="scope">
                            {{ scope.row.shop_name}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="sum" label="總數">
                        <template slot-scope="scope">
                            {{ scope.row.sum}}
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools'

    export default {
        name: "goods-record-detail-list",
        data: function () {
            return {
                total: 1,
                search : {
                    page : 1,
                    limit : 10,
                    shop_type : '-1',
                    goods_status : '-1',
                    date_value : [
                        new Date(new Date().getFullYear(), new Date().getMonth(), 1),
                        new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate())
                    ],
                    goods_rank_type : '1',
                    date : []
                },
                isSearch : false,
                loading : true,
                goodsRecordDetail : [],
                selectIds : [],
                dialog : new Tools.Dialog(this),
                admins : [],
            }
        },

        created() {
            this.loadData();
            this.getAdmin();
        },

        methods: {
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
                
                let url = '/goods/record/rank'+Tools.BuildQueryString(search);
                if (type) {
                    this.loading = false;
                    window.location.href = url;
                } else {
                    axios.get('/goods/record/rank'+Tools.BuildQueryString(search)).then((response) => {
                        this.loading = false;
                        this.goodsRecordDetail = response.data.response.list;
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

            dateSelect() {

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