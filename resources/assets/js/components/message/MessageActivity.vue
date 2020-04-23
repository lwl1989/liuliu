<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div style="padding-top:5px;">
                    <el-col :span="100">
                        <el-form :inline="true" :model="searchForm" :rules="searchRules" ref="searchForm" class="demo-form-inline">
                            <el-form-item>
                                <el-select v-model="searchForm.search_type" placeholder="活動名稱">
                                    <el-option label="活動名稱" value="1"></el-option>
                                    <el-option label="業務單位" value="2"></el-option>
                                </el-select>
                            </el-form-item>

                            <el-form-item>
                                <el-input v-model="searchForm.search_type_key" @keyup.enter.native='doSearch' auto-complete="off"></el-input>
                            </el-form-item>

                            <el-form-item>
                                <el-select v-model="searchForm.remain_gold_type" placeholder="剩餘報名金幣">
                                    <el-option label="剩餘報名金幣" value="1"></el-option>
                                    <el-option label="剩餘報到金幣" value="2"></el-option>
                                </el-select>
                                <el-select v-model="searchForm.symbol_gold_type" placeholder="請選擇">
                                    <el-option label=">"  value="1"></el-option>
                                    <el-option label=">=" value="2"></el-option>
                                    <el-option label="="  value="3"></el-option>
                                    <el-option label="<"  value="4"></el-option>
                                    <el-option label="<=" value="5"></el-option>
                                </el-select>
                            </el-form-item>

                            <el-form-item>
                                <el-input-number v-model="searchForm.symbol_gold_key" auto-complete="off"></el-input-number>
                            </el-form-item>

                            <el-form-item>
                                <el-select v-model="searchForm.timeType" placeholder="活動舉辦日期">
                                    <el-option label="活動舉辦日期" value="1"></el-option>
                                    <el-option label="新增日期" value="2"></el-option>
                                </el-select>
                            </el-form-item>

                            <el-form-item>
                                <el-date-picker
                                        v-model="searchForm.time"
                                        type="daterange"
                                        range-separator="~"
                                        start-placeholder="開始日期"
                                        end-placeholder="結束日期"
                                        align="right">
                                </el-date-picker>
                            </el-form-item>

                            <el-form-item>
                                <el-button type="primary" icon="el-icon-search" @click="doSearch">查詢</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>

                    <el-col :span="8">
                        <el-button-group>
                            <el-button type="primary" icon="el-icon-circle-plus" @click="addActivity">新增</el-button>
                            <el-button type="primary" class="el-icon-remove" @click="deleteActivity">刪除</el-button>
                            <!--<el-button type="primary" class="el-icon-circle-plus" @click="setActivityGold">設定活動金幣</el-button>-->
                        </el-button-group>
                    </el-col>
                </div>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="activities" empty-text="目前沒有符合資料" stripe v-loading="loading" @selection-change="handleSelectionChange">
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column prop="id" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="business_unit_name" label="業務單位">
                    </el-table-column>
                    <el-table-column prop="name" label="活動名稱">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="editActivity(scope.row.id)">{{ scope.row.name }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" label="新增時間">
                    </el-table-column>
                    <el-table-column prop="open_time" label="活動舉辦時間">
                    </el-table-column>
                    <el-table-column prop="online_registration" label="線上報名">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="onlineRegistration(scope.row.id)" v-if="scope.row.is_online === 1">{{ scope.row.online_registration }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="remain_register_gold" label="剩餘報名金幣">
                        <template slot-scope="scope">
                            {{scope.row.remain_online_gold}}
                            <el-button size="small" type="warning" @click="recycleGold(scope.row.id, scope.row.remain_online_gold, 1)" v-show="scope.row.remain_online_gold > 0 && scope.row.recycle_status === 1">回收</el-button>
                            <span v-if="scope.row.recycle_status === 2">已回收</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="offline_registration" label="現場報到">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="offlineRegistration(scope.row.id)" v-if="scope.row.is_offline === 1">{{ scope.row.offline_registration }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="offline_registration" label="剩餘報到金幣">
                        <template slot-scope="scope">
                            {{scope.row.remain_offline_gold}}
                            <el-button size="small" type="warning" @click="recycleGold(scope.row.id, scope.row.remain_offline_gold, 2)" v-show="scope.row.remain_offline_gold > 0 && scope.row.live_recycle_status === 1">回收</el-button>
                            <span v-if="scope.row.live_recycle_status === 2">已回收</span>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10, 20, 30, 50, 100]"
                            @size-change="changeLimit"
                            @current-change="loadData"
                            :current-page="searchForm.page"
                            :page-size="searchForm.limit"
                            layout="sizes,total,prev,pager,next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <activity-detail ref="ActivityDetail"></activity-detail>
    </div>
</template>

<script>
    import ActivityDetail from './MessageActivityDetailComponent';
    import { ActivitySearchRule, ActivityRule } from '../../tools/element-ui-validate';
    import Tools from "../../tools/vue-common-tools";

    export default {
        name: "message-activity",

        data : function() {
            return {
                total: 1,
                activities: [],
                formLabelWidth: '120px',
                searchForm : {
                    search_type : '1',
                    search_type_key : '',
                    remain_gold_type : '1',
                    symbol_gold_type : '',
                    symbol_gold_key : 0,
                    timeType : '1',
                    time:[
                        // new Date(new Date().getTime() - 3600 * 1000 * 24 * 30),
                        // new Date()
                    ],

                    page : 1,
                    limit : 10,
                },
                searchRules :ActivitySearchRule,
                rules: ActivityRule,
                multipleSelection : [],
                selectIds : {
                    id : []
                },
                loading: true,
                unit_sum : this.getUnitSum(),
                dialog: new Tools.Dialog(this)
            }
        },

        components:{ActivityDetail},

        created : function() {
            this.loadData(1);
        },

        methods: {
            getUnitSum : function(){
                axios.get('/department/getAllUnit').then(response => {
                    this.unit_sum = response.data.response;
                });
            },

            changeLimit : function(limit) {
                this.searchForm.limit = limit;
                this.loadData(1);
            },

            doSearch : function() {
                this.searchForm.page = 1;
                this.loadData();
            },

            loadData : function (page) {
                this.searchForm.page = page ? page : 1;
                let queryArr = JSON.parse(JSON.stringify(this.searchForm));
                queryArr.time = this.getTimestamp(queryArr);
                let queryString = Tools.BuildQueryString(queryArr, page);

                axios.get('/activity/count'+queryString).then(response => {
                        this.total = response.data.response.count;
                });

                this.changeLoadingStatus(true);
                axios.get('/activity/select'+queryString).then(response => {
                    this.changeLoadingStatus(false);
                    this.activities = response.data.response.list;
                }).catch(() => {
                    this.changeLoadingStatus(false);
                });
            },

            getTimestamp : function(queryArray) {
                let time = [];

                if (queryArray.time && queryArray.time.length > 0) {
                    this.searchForm.time.forEach((value) => {
                        time.push(value.getTime());
                    });
                }

                return time.join(',');
            },

            handleSelectionChange : function(val) {
                this.multipleSelection = val;
            },

            addActivity : function () {
                this.$refs.ActivityDetail.editActivity(0);
            },

            deleteActivity : function () {
                let ids = this.multipleSelection.map(item => item.id).join();

                if (ids.length === 0) {
                    return this.dialog.openError(null, '請至少選擇一筆資料');
                }

                this.$msgbox({
                    title: '提示',
                    message: '確定刪除嗎?資料刪除後將無法復原。',
                    showCancelButton: true,
                    confirmButtonText: '確定',
                    cancelButtonText: '取消',

                    beforeClose: (action, instance, done) => {
                        let callback = function () {
                            instance.confirmButtonQuestion = false;
                            instance.confirmButtonText = '確定';
                            done();
                        };
                        if (action === 'confirm') {
                            axios.delete('/activity/delete', {data:JSON.stringify({id: ids})}).then(response => {
                                if(response.data.code > 0) {
                                    this.dealMessageWarning();
                                    return;
                                }

                                if (response.data.response.row !== undefined && response.data.response.row.length > 0) {
                                    this.dealMessageSuccess();
                                    callback();
                                } else {
                                    this.dialog.openError(null, '活動已綁定推播設定，無法刪除');
                                    callback();
                                }
                            });
                        }else{
                            callback();
                        }
                    }
                });
            },

            editActivity : function(id) {
                this.$refs.ActivityDetail.editActivity(id);
            },

            onlineRegistration : function (activityId) {
                Tools.OpenNewWindow('/#/activity/online/'+activityId, '活動報名列表', window.screen.height - 200, window.screen.width - 100);
            },
            offlineRegistration : function (activityId) {
                Tools.OpenNewWindow('/#/activity/offline/'+activityId, '現場活動報名列表', window.screen.height - 200, window.screen.width - 100);
            },

            recycleGold : function (id, remain_online_gold, type) {
                let that = this;
                let h = this.$createElement;
                this.dialog.openSelfDialog((closeCallback) => {
                    axios.get('/activity/recycleGold?id=' + id + '&remain_gold=' + remain_online_gold + '&type=' + type)
                        .then(function (response) {
                            if (response.data.code == 0) {
                                that.loadData(1);
                            }
                            closeCallback();
                        })
                        .catch(function (error) {
                            console.log(error);
                            closeCallback();
                        });
                }, h('p', null, [
                    h('span', null, '確定要回收嗎?')
                ]), '執行中...', '確定');

            },

            dealMessageSuccess : function () {
                this.$emit('success', () => {
                    this.loadData(this.searchForm.page);
                });
            },

            dealMessageWarning : function () {
                this.$emit('warning', () => {
                    this.loadData(this.searchForm.page);
                });
            },

            changeLoadingStatus : function (status) {
                this.loading = status;
            }
        }
    }
</script>

<style scoped>
    .el-checkbox-group{
        font-size: 12px;
    }
    .hide-dialog{
        display:none;
    }
</style>