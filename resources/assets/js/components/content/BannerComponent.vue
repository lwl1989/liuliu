<template>
    <div id="app">
        <!-- 商品查詢列 -->
        <el-row>
            <el-form :inline="true" :model="searchBannerFrom" ref="searchBannerFrom" class="demo-form-inline">
                <el-form-item>
                    <el-select v-model="searchBannerFrom.profile" placeholder="名稱">
                        <el-option label="名稱" value="title"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-input v-model="searchBannerFrom.profileValue" @keyup.enter.native='searchBanner' auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-select v-model="searchBannerFrom.timeType">
                        <el-option label="新增時間" value="create_time"></el-option>
                        <el-option label="最後異動時間" value="update_time"></el-option>
                        <el-option label="上架時間（起）" value="online_time"></el-option>
                        <el-option label="上架時間（迄）" value="offline_time"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-date-picker
                            v-model="searchBannerFrom.time"
                            type="datetimerange"
                            range-separator="~"
                            start-placeholder="開始日期"
                            end-placeholder="結束日期"
                            align="right">
                    </el-date-picker>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="searchBanner()">查詢</el-button>
                </el-form-item>
            </el-form>
        </el-row>
        <!--  商品按鈕列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-button size='small' type="primary" icon="el-icon-circle-plus" @click="addBanner">新增</el-button>
                <el-button size='small' type="primary" icon="el-icon-remove" @click="deleteBanner">刪除</el-button>
                <el-button size='small' type="primary" icon="el-icon-goods" @click="saleChange(2)">上架</el-button>
                <el-button size='small' type="primary" icon="el-icon-sold-out" @click="saleChange(1)">下架</el-button>
            </el-col>
            <el-col :span="24">
                總筆數:{{total}}
            </el-col>
        </el-row>
        <!--  商品Table列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="banner"
                          v-loading="loading"
                          @selection-change="bannerSelect"
                          :row-class-name="tableRowClassName"
                          empty-text="目前沒有符合資料"
                >
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column label="項次" prop="id"
                            type="index"
                            width="50">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="title" label="名稱">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;"
                               @click="editBannerDetail(scope.row.id)">{{ scope.row.title }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="狀態" :formatter="bannerStatusFormat">
                    </el-table-column>
                    <el-table-column prop="online_time" label="上架時間（起）">
                    </el-table-column>
                    <el-table-column prop="offline_time" label="上架時間（迄）">
                    </el-table-column>
                    <el-table-column prop="create_time" label="新增時間">
                    </el-table-column>
                    <el-table-column prop="update_time" label="最後異動時間">
                    </el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination :page-sizes="[10, 20, 30, 50, 100]" @size-change="handleSizeChange"
                                   @current-change="handleBannerCurrentChange" :current-page="currentPage"
                                   :page-size="pageSize" layout="sizes, total, prev, pager, next" :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <banner-detail ref="BannerDetail" v-on:add="addBannerDetail"></banner-detail>
    </div>
</template>

<script>
    import BannerDetail from './BannerDetailComponent';
    import dialog from '../../tools/element-ui-dialog'

    export default {
        name: "banner-component",
        data: function () {
            return {
                currentPage: 1,
                pageSize: 10,
                total: 1,
                loading: true,
                banner: [],
                selectIds: [],
                searchBannerFrom: {
                    profile: "title",
                    profileValue: "",

                    time: [
                        new Date(new Date().getTime() - 3600 * 1000 * 24 * 90),
                        new Date()
                    ],
                    timeType: "update_time"
                },
                dialog:dialog(this)
            }
        },
        components: {BannerDetail},
        mounted: function () {
            this.$nextTick(function () {

                this.handleBannerCurrentChange(1);
            })
        },
        methods: {
            bannerStatusFormat(item) {
                let time = new Date().getTime();
                let upTime = new Date(item.online_time).getTime();
                let downTime = new Date(item.offline_time).getTime();
                if(item.status===2&&time > upTime && time <= downTime) {
                    return '上架中';
                } else {
                    return '已下架';
                }
            },

            searchBanner() {
                this.loading = true;
                this.handleBannerCurrentChange(1);
            },

            getBannerNowSearchUrl() {
                let time = [];
                if (this.searchBannerFrom.time&&this.searchBannerFrom.time.length > 0) {
                    this.searchBannerFrom.time.forEach(function (value) {
                        time.push(value.getTime());
                    });
                }
                time = time.join(',');
                return this.searchBannerFrom.profile + '=' + this.searchBannerFrom.profileValue + '&' + this.searchBannerFrom.timeType + '=' + time;
            },

            addBannerDetail(item) {
                this.total += 1;
                if (Math.ceil(this.banner.length / this.pageSize) === this.currentPage) {
                    this.banner.push(item);
                }
            },

            editBannerDetail(id) {
                this.$refs.BannerDetail.editBanner(id);
            },

            getBannerMaxPage() {
                let that = this;
                axios.get('/content/banner/count?'+this.getBannerNowSearchUrl())
                    .then(function (response) {
                        that.total = response.data.response.count;
                    })
                    .catch(function (error) {
                        that.dialog.openWarning( function () {
                        }, error.toString());
                    });
            },

            handleBannerCurrentChange(currentPage) {
                if(this.currentPage!==currentPage){
                    this.currentPage = currentPage;
                }
                let that = this;
                let url = '/content/banner/select?page=' + currentPage + '&limit=' + that.pageSize + '&' + this.getBannerNowSearchUrl();
                axios.get(url).then(function (response) {
                    that.banner = response.data.response.list;
                    that.loading = false;
                }).catch(function (error) {
                    that.loading = false;
                });
                this.getBannerMaxPage();
            },

            saleChange(status) {
                if (this.selectIds.length === 0) {
                    return this.dialog.openWarning(null, '請至少選擇一筆資料');
                }
                let url = '/content/banner/on';
                let msg = '確定上架？該資料將顯示於手機端。';
                if (status < 2) {
                    url = '/content/banner/off';
                    msg = '確定下架？該資料將於手機端隱藏。'
                }

                this.dialog.openSelfDialog((callback) => {
                    axios.post(url, {ids : this.selectIds}).then((response) => {
                        if(parseInt(response.data.code) === 0) {
                            this.dialog.openSuccess(() => {
                                callback();
                                this.handleBannerCurrentChange(1);
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

            handleSizeChange(val) {
                this.pageSize = val;
                this.handleBannerCurrentChange(1);
            },

            deleteBanner() {
                if (this.selectIds.length === 0) {
                    return this.dialog.openWarning(null, '請至少選擇一筆資料');
                }

                this.dialog.openDelDialog((callback) => {
                    axios.delete('/content/banner/delete', {data:{ id: this.selectIds}}).then((response) => {
                        if (parseInt(response.data.code) === 0) {
                            callback();
                            this.handleBannerCurrentChange(this.currentPage);

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

            addBanner() {
                this.$refs.BannerDetail.editBanner(0);
            },

            bannerSelect(rows) {
                let ids = [];
                rows.forEach(function (item) {
                    ids.push(item.id);
                });
                this.selectIds = ids;
            },

            tableRowClassName({row}) {
                let time = new Date().getTime();
                let upTime = new Date(row.online_time).getTime();
                let downTime = new Date(row.offline_time).getTime();
                if(row.status===2&&time > upTime && time <= downTime) {
                    return 'warning-row';
                }

                return '';
            },
        },
    }
</script>

<style scoped>
    .warning-row {
        background: oldlace !important;
    }
</style>