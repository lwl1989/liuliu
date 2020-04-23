<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div class="app-search-bg">
                </div>
                <div style="padding-top:5px;">
                    <el-col :span="15">
                        <el-form :inline="true" :model="search" :rules="searchRules" ref="searchForm" class="demo-form-inline">
                            <el-form-item>
                                <el-select v-model="search.type" placeholder="手機號碼">
                                    <el-option label="手機號碼" value="1"></el-option>
                                    <el-option label="姓名" value="3"></el-option>
                                    <el-option label="國籍" value="6"></el-option>
                                    <el-option label="通訊地址" value="5"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-input v-model="search.key" @keyup.enter.native='loadData(1)' auto-complete="off"></el-input>
                            </el-form-item>
                            <el-form-item label="數位縣民">
                                <el-select v-model="search.digitalize_type" placeholder="">
                                    <el-option label="請選擇" value="-1"></el-option>
                                    <el-option label="是" value="1"></el-option>
                                    <el-option label="否" value="0"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="行動裝置類別">
                                <el-select v-model="search.device_type" placeholder="">
                                    <el-option label="請選擇" value="0"></el-option>
                                    <el-option label="Android" value="1"></el-option>
                                    <el-option label="IOS" value="2"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="search.time_type" placeholder="新增日期">
                                    <el-option label="新增日期" value="1"></el-option>
                                    <el-option label="最後異動日期" value="2"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-date-picker
                                        v-model="search.time"
                                        type="datetimerange"
                                        range-separator="~"
                                        start-placeholder="開始日期"
                                        end-placeholder="結束日期"
                                        align="right">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item label="性別">
                                <el-select v-model="search.sex_type" placeholder="">
                                    <el-option label="全部" value="0"></el-option>
                                    <el-option label="男性" value="1"></el-option>
                                    <el-option label="女性" value="2"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary" icon="el-icon-search" @click="loadData(1)">查詢</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>

                </div>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                總筆數:{{total}}
                <el-table :data="users" stripe v-loading='loading'>
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column prop="id" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="mobile" label="手機號碼">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="editUser(scope.row.user_id)">{{ scope.row.mobile }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="姓名">
                    </el-table-column>
                    <el-table-column prop="sex" label="性別" :formatter="sexFormat">
                    </el-table-column>
                    <el-table-column prop="nationality" label="國籍">
                    </el-table-column>
                    <el-table-column prop="address" label="通訊地址">
                    </el-table-column>
                    <el-table-column prop="device_name" label="行動裝置類別">
                    </el-table-column>
                    <el-table-column prop="create_time" label="新增時間">
                    </el-table-column>
                    <el-table-column prop="update_time" label="最後變動時間">
                    </el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10,20,30,50,100]"
                            @size-change="limitChange"
                            @current-change="loadData"
                            :current-page="search.page"
                            :page-size="search.limit"
                            layout="sizes,total,prev,pager,next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import { UsersSearchRule, UsersRule } from '../../tools/element-ui-validate';
    import Tools from "../../tools/vue-common-tools";

    export default {
        name: "usersComponent",
        data : function() {
            return {
                total : 0,
                users : [],
                search : {
                    key : '',
                    page : 1,
                    time : '',
                    type : '1',
                    limit : 10,
                    device_type : '0',
                    time_type : '1',
                    digitalize_type : '-1',
                    sex_type : '0'
                },
                loading : true,
                searchRules : UsersSearchRule,
                rules : UsersRule
            }
        },

        created:function () {
            this.loadData(1);
        },

        methods: {
            sexFormat : function (item) {
                if (item.sex === 1) {
                    return '男';
                }

                if (item.sex === 2) {
                    return '女';
                }

                if (item.sex === 0) {
                    return '不公開';
                }
            },

            loadData : function(page) {
                let queryString = Tools.BuildQueryString(this.search, page);

                axios.get('/users/count'+queryString)
                    .then((response) => {
                        this.total = response.data.response.count;
                    });

                this.loading = true;
                axios.get('/users/select'+queryString).then((response) => {
                    this.loading = false;
                    this.users = response.data.response.data;
                }).catch(() => {
                    this.loading = false;
                });
            },

            limitChange : function (limit) {
                this.search.limit = limit;
                this.loadData(1);
            },

            editUser : function(id) {
                Tools.OpenNewWindow('/#/edit/users/'+id, '臺東縣民編輯', 800, 1024);
            }
        }
    }
</script>

<style scoped>
    .el-checkbox-group{
        font-size: 12px;
    }
</style>