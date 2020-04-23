<template>
    <div id="app">
        <!-- 商品查詢列 -->
        <el-row>
            <el-form :inline="true"  :model="doSearchAppFrom"  ref="doSearchAppFrom" class="demo-form-inline">
                <el-form-item>
                    <el-select v-model="doSearchAppFrom.profile" placeholder="名稱">
                        <el-option label="名稱" value="title"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-input v-model="doSearchAppFrom.profileValue" @keyup.enter.native='doSearchApp' auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="doSearchAppFrom.timeType">
                        <el-option label="新增時間" value="create_time"></el-option>
                        <el-option label="最後異動時間" value="update_time"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-date-picker
                            v-model="doSearchAppFrom.time"
                            type="datetimerange"

                            range-separator="~"
                            start-placeholder="開始日期"
                            end-placeholder="結束日期"
                            align="right">
                    </el-date-picker>
                    <!--:picker-options="pickerOptions2"-->
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="doSearchApp">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <!--  商品按鈕列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-button size="small" type="primary" icon="el-icon-circle-plus" @click="addApp">新增</el-button>
                <el-button size="small" type="primary" icon="el-icon-remove" @click="deleteApp">刪除</el-button>
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
                <el-table :data="app"
                          v-loading="loading"
                          @selection-change="appSelect"
                          :row-class-name="tableRowClassName"
                          empty-text="目前沒有符合資料"
                >
                    <el-table-column type="selection" >
                    </el-table-column>
                    <el-table-column label="項次" prop="id"
                            type="index"
                            width="50">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="title" label="名稱">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="editAppDetail(scope.row.id)">{{ scope.row.title }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="platform" label="APP版本" :formatter="appPlatformFormat">
                    </el-table-column>
                    <el-table-column prop="status" label="狀態" :formatter="appStatusFormat">
                    </el-table-column>
                    <el-table-column prop="create_time" label="新增時間">
                    </el-table-column>
                    <el-table-column prop="update_time" label="最後異動時間">
                    </el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10, 20, 30, 50, 100]"
                            @size-change="handleSizeChange"
                            @current-change="handleAppCurrentChange"
                            :current-page="currentPage"
                            :page-size="pageSize"
                            layout="sizes, total, prev, pager, next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <app-detail ref="AppDetail" v-on:add="addAppDetail"></app-detail>
    </div>
</template>

<script>
    import AppDetail from './AppDetailComponent';
    import dialog from '../../tools/element-ui-dialog'

    export default {
        name: "app-component",
        data: function () {
            return {
                currentPage: 1,
                pageSize: 10,
                total: 1,
                loading: true,
                app: [],
                selectIds:[],
                doSearchAppFrom:{
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
        components:{AppDetail},
        mounted: function () {
            this.$nextTick(function() {
                this.handleAppCurrentChange(1);
            })
        },
        methods:{
            addAppDetail(item){
                this.total += 1;
                if(Math.ceil(this.app.length / this.pageSize) === this.currentPage) {
                    this.app.push(item);
                }
            },

            getAppNowSearchUrl(){
                let time = [];
                if(this.doSearchAppFrom.time&&this.doSearchAppFrom.time.length > 0) {
                    this.doSearchAppFrom.time.forEach(function (value) {
                       time.push(value.getTime());
                    });
                }
                time = time.join(',');
                return this.doSearchAppFrom.profile+'='+encodeURIComponent(this.doSearchAppFrom.profileValue)+'&'+this.doSearchAppFrom.timeType+'='+time;
            },

            editAppDetail(id){
                this.$refs.AppDetail.editApp(id);
            },

            getAppMaxPage() {
                let that = this;
                axios.get('/content/app/count?'+this.getAppNowSearchUrl())
                    .then(function (response) {
                        that.total = response.data.response.count;
                    })
                    .catch(function (error) {
                        console.error(error)
                    });
            },

            handleAppCurrentChange(currentPage) {
                if(this.currentPage!==currentPage){
                    this.currentPage = currentPage;
                }
                let that = this;
                let url = '/content/app/select?page='+currentPage+'&limit='+that.pageSize+'&'+this.getAppNowSearchUrl();
                console.log(url)
                that.loading = true;
                axios.get(url) .then(function (response) {
                    that.app = response.data.response.list;
                    that.loading = false;
                }).catch(function () {
                    that.loading = false;
                });

                that.getAppMaxPage();
            },

            saleChange(status){
                if (this.selectIds.length === 0) {
                    return this.dialog.openWarning(null, '請至少選擇一筆資料');
                }

                let url = '/content/app/on';
                let msg = '確定上架？該資料將顯示於手機端。';
                if (status < 2) {
                    url = '/content/app/off';
                    msg = '確定下架？該資料將於手機端隱藏。'
                }

                this.dialog.openSelfDialog((callback) => {
                    axios.post(url, {ids : this.selectIds}).then((response) => {
                        if(parseInt(response.data.code) === 0) {
                            this.dialog.openSuccess(() => {
                                callback();
                                this.handleAppCurrentChange(1);
                            });

                            return;
                        }

                        this.dialog.openWarning(() => {
                            callback();
                        });
                    }).catch((error) => {
                        this.dialog.openWarning(() => {
                            callback();
                        });
                    });
                }, msg);
            },

            deleteApp() {
                if (this.selectIds.length === 0) {
                    return this.dialog.openWarning(null, '請至少選擇一筆資料');
                }

                this.dialog.openDelDialog((callback) => {
                    axios.delete('/content/app/delete', {data:{ id: this.selectIds}}).then((response) => {
                        if (parseInt(response.data.code) === 0) {
                            callback();
                            this.handleAppCurrentChange(this.currentPage);

                            return;
                        }

                        this.dialog.openWarning(callback, '操作失敗');
                    }).catch((error) => {
                        this.dialog.openWarning(() => {
                            callback();
                        }, error.toString());
                    });
                });
            },

            appStatusFormat(item) {
                let value = item.status;
                if (value === 2) {
                    return '上架中';
                }
                if (value === 1) {
                    return '已下架';
                }
                return '已下架';
            },

            appPlatformFormat(item) {
                let value = parseInt(item.platform);
                if (value === 2) {
                    return 'iOS';
                }
                if (value === 1) {
                    return 'Android';
                }

                return 'Url';
            },

            doSearchApp(){
                this.loading = true;
                this.handleAppCurrentChange(1);
            },

            handleSizeChange(val) {
                this.pageSize = val;
                this.handleAppCurrentChange(1);
                // this.searchForm.limit = limit;
                // this.loadData(1);
            },

            addApp(){
                this.$refs.AppDetail.editApp(0);
            },

            tableRowClassName({row}) {
                if (row.status === 2) {
                    return 'warning-row';
                }

                return '';
            },

            appSelect(rows){
                let ids = [];

                rows.forEach(function (item) {
                    ids.push(item.id);
                });

                this.selectIds = ids;
            }
        },
    }
</script>

<style scoped>
    .warning-row {
        background: oldlace !important;
    }
</style>