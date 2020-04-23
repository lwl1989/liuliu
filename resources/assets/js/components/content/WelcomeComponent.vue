<template>
    <div id="app">
        <!-- 商品查詢列 -->
        <el-row>
            <el-form :inline="true" :model="doSearchWelcomeFrom"  ref="doSearchWelcomeFrom" class="demo-form-inline">
                <el-form-item>
                    <el-select v-model="doSearchWelcomeFrom.profile" placeholder="名稱">
                        <el-option label="名稱" value="title"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-input v-model="doSearchWelcomeFrom.profileValue" @keyup.enter.native="doSearchWelcome" auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="doSearchWelcomeFrom.timeType">
                        <el-option label="新增時間" value="create_time"></el-option>
                        <el-option label="最後異動時間" value="update_time"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-date-picker
                            v-model="doSearchWelcomeFrom.time"
                            type="datetimerange"
                            range-separator="~"
                            start-placeholder="開始日期"
                            end-placeholder="結束日期"
                            align="right">
                    </el-date-picker>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="doSearchWelcome">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <!--  商品按鈕列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-button size="small" type="primary" icon="el-icon-circle-plus" @click="addWelcome">新增</el-button>
                <el-button size="small" type="primary" icon="el-icon-remove" @click="deleteWelcome">刪除</el-button>
                <el-button size="small" type="primary" icon="el-icon-goods" @click="saleChange(2)">上架</el-button>
                <el-button size="small" type="primary" icon="el-icon-sold-out" @click="saleChange(1)">下架</el-button>
            </el-col>
            <el-col :span="24">
                總筆數:{{total}}
            </el-col>
        </el-row>
        <!--  商品Table列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="welcome"
                          v-loading="loading"
                          @selection-change="welcomeSelect"
                          :row-class-name="tableRowClassName"
                          empty-text="目前沒有符合資料"
                >
                    <el-table-column type="selection" >
                    </el-table-column>
                    <el-table-column label="項次" type="index" width="50">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="title" label="名稱">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="editWelcomeDetail(scope.row.id)">{{ scope.row.title }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="狀態" :formatter="welcomeStatusFormat"></el-table-column>
                    <el-table-column prop="online_time" label="上架時間(起)" :formatter="onLineTime"></el-table-column>
                    <el-table-column prop="offline_time" label="上架時間(迄)" :formatter="offLineTime"></el-table-column>
                    <el-table-column prop="create_time" label="新增時間"></el-table-column>
                    <el-table-column prop="update_time" label="最後異動時間"></el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination  :page-sizes="[10, 20, 30, 50, 100]" @size-change="handleSizeChange" @current-change="handleWelcomeCurrentChange" :current-page="currentPage"  :page-size="pageSize" layout="sizes, total, prev, pager, next" :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <welcome-detail ref="WelcomeDetail" v-on:add="addWelcomeDetail"></welcome-detail>
    </div>
</template>

<script>
    import WelcomeDetail from './WelcomeDetailComponent';
    import dialog from '../../tools/element-ui-dialog'

    export default {
        name: "welcome-component",
        data: function () {
            return {
                currentPage: 1,
                pageSize: 10,
                total: 1,
                loading: true,
                welcome: [],
                selectIds:[],
                doSearchWelcomeFrom:{
                    profile:"title",
                    profileValue:"",

                    time:[
                        new Date(new Date().getTime() - 3600 * 1000 * 24 * 90),
                        new Date()
                    ],
                    timeType:"update_time"
                },
                dialog:dialog(this)
            }
        },
        components:{WelcomeDetail},
        mounted: function () {
            this.$nextTick(function() {

                this.handleWelcomeCurrentChange(1);
            })
        },
        methods: {
            addWelcomeDetail(item) {
                this.total += 1;
                if(Math.ceil(this.welcome.length / this.pageSize) === this.currentPage) {
                    this.welcome.push(item);
                }
            },

            getWelcomeNowSearchUrl(){
                let time = [];
                if(this.doSearchWelcomeFrom.time&&this.doSearchWelcomeFrom.time.length > 0) {
                    this.doSearchWelcomeFrom.time.forEach(function (value) {
                        time.push(value.getTime());
                    });
                }
                time = time.join(',');
                return this.doSearchWelcomeFrom.profile+'='+this.doSearchWelcomeFrom.profileValue+'&'+this.doSearchWelcomeFrom.timeType+'='+time;
            },

            editWelcomeDetail(id){
                this.$refs.WelcomeDetail.editWelcome(id);
            },

            getWelcomeMaxPage() {
                let that = this;
                axios.get('/content/welcome/count?'+this.getWelcomeNowSearchUrl())
                    .then(function (response) {
                        that.total = response.data.response.count;
                    })
                    .catch(function (error) {
                        that.dialog.openWarning(function () {},error.toString());
                    });
            },

            handleWelcomeCurrentChange(currentPage) {
                if(this.currentPage!==currentPage){
                    this.currentPage = currentPage;
                }
                let that = this;
                let url = '/content/welcome/select?page='+currentPage+'&limit='+that.pageSize+'&'+this.getWelcomeNowSearchUrl();
                axios.get(url) .then(function (response) {
                    that.welcome = response.data.response.list;
                    that.loading = false;
                }).catch(function (error) {
                    that.loading = false;
                });
                this.getWelcomeMaxPage();
            },

            saleChange (status) {
                if (this.selectIds.length === 0) {
                    return this.dialog.openWarning(null,'請至少選擇一筆資料');
                }

                if (this.selectIds.length > 1) {
                    this.dialog.openWarning(null, '此操作只能選擇一筆資料');
                    return;
                }

                let url = '/content/welcome/on';
                let msg = '確定上架？原先上架的圖片將自動下架。';
                if (status < 2) {
                    url = '/content/welcome/off';
                    msg = '確定下架？該資料將於手機端隱藏。'
                }

                this.dialog.openSelfDialog((callback) => {
                    axios.post(url, {ids : this.selectIds}).then((response) => {
                        if(parseInt(response.data.code) === 0) {
                            this.dialog.openSuccess(() => {
                                callback();
                                this.handleWelcomeCurrentChange(1);
                            });

                            return;
                        }

                        this.dialog.openWarning(() => {
                            callback();
                        });
                    }).catch((error) => {
                        this.dialog.openWarning(() => {
                            callback();
                        }, error.toString());
                    });
                }, msg);
            },
            
            deleteWelcome() {
                if (this.selectIds.length === 0) {
                    return this.dialog.openWarning(null, '請至少選擇一筆資料');
                }

                this.dialog.openDelDialog((callback) => {
                    axios.delete('/content/welcome/delete', {data:{id:this.selectIds}}).then((response) => {
                        if (parseInt(response.data.code) === 0) {
                            this.dialog.openSuccess(()=>{
                                callback();
                            })

                            this.handleWelcomeCurrentChange(this.currentPage);

                            return;
                        }

                        this.dialog.openWarning(() => {
                            callback();
                        });
                    }).catch(function (error) {
                        this.dialog.openWarning(() => {
                            callback();
                        }, error.toString());
                    });
                });
            },
            
            welcomeStatusFormat(item) {
                let value = item.status;
                if (value === 2) {
                    return '上架中';
                }
                if (value === 1) {
                    return '已下架';
                }
                return '已下架';
            },
            
            doSearchWelcome(){
                this.loading = true;
                this.handleWelcomeCurrentChange(1);
            },
            
            handleSizeChange(val) {
                this.pageSize = val;
                this.handleWelcomeCurrentChange(1);
            },

            addWelcome(){
                this.$refs.WelcomeDetail.editWelcome(0);
            },
            
            welcomeSelect(rows){
                let ids = [];

                rows.forEach(function (item) {
                    ids.push(item.id);
                });

                this.selectIds = ids;
            },

            onLineTime : function (data) {
                return this.lineTime(data, 'online_time');
            },

            offLineTime : function (data) {
                return this.lineTime(data, 'offline_time');
            },

            tableRowClassName({row}) {
                if (row.status === 2) {
                    return 'warning-row';
                }

                return '';
            },

            lineTime : function (data, key) {
                return data[key] === null ? '-' : data[key];
            }
        },
    }
</script>

<style scoped>
    .warning-row {
        background: oldlace !important;
    }
</style>