<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div style="padding-top:5px;">
                    <el-col :span="100">
                        <el-form :inline="true" :model="searchForm" :rules="searchRules" ref="searchForm" class="demo-form-inline">
                            <el-form-item>
                                <el-select v-model="searchForm.search_type" placeholder="手機號碼">
                                    <el-option label="手機號碼" value="1"></el-option>
                                    <el-option label="姓名" value="2"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-input v-model="searchForm.search_type_key" @keyup.enter.native='loadData(1)' auto-complete="off"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="searchForm.search_time_type" placeholder="最後異動日期">
                                    <el-option label="最後異動日期" value="1"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-date-picker
                                        v-model="searchForm.time"
                                        type="datetimerange"
                                        range-separator="~"
                                        start-placeholder="開始日期"
                                        end-placeholder="結束日期"
                                        align="right">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary" icon="el-icon-search" @click="loadData(1)">查詢</el-button>
                                <el-button type="primary" class="el-icon-circle-download" @click="goldsExcel">匯出</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>
                </div>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="personGolds" stripe v-loading='loading'>
                    <el-table-column prop="id" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="mobile" label="手機號碼">
                    </el-table-column>
                    <el-table-column prop="name" label="姓名">
                    </el-table-column>
                    <el-table-column prop="total" label="金幣總數">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="showPersonGold(scope.row.user_id)">{{ scope.row.total }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="update_time" label="最後異動時間">
                    </el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                        :page-sizes="[10, 20, 30, 50, 100]"
                        @size-change="limitChange"
                        @current-change="loadData"
                        :current-page="searchForm.page"
                        :page-size="searchForm.limit"
                        layout="sizes, total, prev, pager, next"
                        :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import { GoldRecyclePersonSearchRule, GoldRecyclePersonRule } from '../../tools/element-ui-validate';
    import Tools from "../../tools/vue-common-tools";
    import NewDialog from '../../tools/element-ui-dialog'

    export default {
        name: "GoldRecyclePerson",
        data : function() {
            return {
                searchRules :GoldRecyclePersonSearchRule,
                rules: GoldRecyclePersonRule,
                personGolds : [],
                searchForm:{
                    page : 1,
                    limit : 10,
                    time : '',
                    search_type : '1',
                    search_type_key : '',
                    search_time_type : '1'
                },
                total : 0,
                loading : false,
                dialog:NewDialog(this),

            }
        },

        watch : {
            'searchForm.search_type' : function (value) {
                this.searchForm.search_type = value;
            },

            'searchForm.search_type_key' : function (value) {
                if (this.searchForm.search_type == '1') {
                    if (value.length > 10) {
                        this.dialog.openError(null, '可輸入 1-10 個字符');
                    }
                }

                if (this.searchForm.search_type == '2') {
                    if (value.length > 30) {
                        this.dialog.openError(null, '名稱長度不能超過 30 個字符');
                    }
                }
            }
        },

        created:function () {
            this.loadData(1);
        },

        methods: {
            loadData : function(page) {
                let queryString = Tools.BuildQueryString(this.searchForm, page);

                axios.get('/gold/recycle/count' + queryString)
                    .then((response) => {
                            this.total =response.data.response.total

                    });

                this.loading = true;

                axios.get('/gold/recycle/select' + queryString).then((response) => {
                    this.loading = false;
                    this.personGolds = response.data.response;
                }).catch(() => {
                    this.loading = false;
                });
            },

            goldsExcel : function() {
                if (this.total > 0) {
                    let queryString = Tools.BuildQueryString(this.searchForm, this.searchForm.page);
                    axios.get('/gold/recycle/count' + queryString)
                        .then((response) => {
                            if(response.data.response.total>0){
                                let url = '/gold/recycle/select' + queryString + '&excel=1';
                                window.location.href = url;
                            }else{
                                this.dialog.openError(null, '目前尚無可匯出數據');
                            }
                        });

                } else {
                    this.dialog.openError(null, '目前尚無可匯出數據');
                }
            },

            showPersonGold : function (user_id) {
                Tools.OpenNewWindow(
                    "/#/gold/person/recycle/"+user_id,
                    (user_id ? '編輯' : '新增') + "個人金幣",
                    800,
                    1024
                );
            },
            limitChange : function (limit) {
                this.searchForm.limit = limit;
                this.loadData(1);
            },
        }
    }
</script>

<style scoped>

</style>