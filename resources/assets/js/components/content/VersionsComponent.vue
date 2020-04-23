<template>
    <div id="app">
        <!--  商品按鈕列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-button type="primary" icon="el-icon-circle-plus" @click="addVersions">新增</el-button>
                <el-button type="primary" icon="el-icon-remove" @click="deleteVersions">刪除</el-button>
            </el-col>
        </el-row>
        <!--  商品Table列 -->
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="versions" stripe style="width: 100%" v-loading="loading" @selection-change="versionsSelect">
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column prop="index" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}</template>
                    </el-table-column>
                    <el-table-column prop="vernumber" label="版本號">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;"
                               @click="editBannerDetail(scope.row.id)">{{ scope.row.vernumber }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="up_time" label="更新時間">
                    </el-table-column>
                    <el-table-column prop="mobile_type" label="使用系統" :formatter="mobileFormat">
                    </el-table-column>
                    <el-table-column prop="is_auto" label="是 否強制更新" :formatter="isautoFormat">
                    </el-table-column>
                    <el-table-column prop="content" label="更新內容">
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
                                   @current-change="handleVersionsCurrentChange" :current-page="currentPage"
                                   :page-size="pageSize" layout="sizes, total, prev, pager, next" :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <versions-detail ref="VersionsDetail" v-on:add="addVersionsDetail"></versions-detail>
    </div>
</template>

<script>
    import VersionsDetail from './VersionsDetailComponent';
    import dialog from '../../tools/element-ui-dialog'
    export default {
        name: "banner-component",
        data: function () {
            return {
                currentPage: 1,
                pageSize: 10,
                total: 1,
                loading: true,
                versions: [],
                selectIds: [],
                dialog:dialog(this)
            }
        },
        components: {VersionsDetail},
        mounted: function () {
            this.$nextTick(function () {
                this.handleVersionsCurrentChange(1);
            })
        },
        methods: {
            mobileFormat(item) {
                let value = item.mobile_type;

                if (value == 1) {
                    return 'Android';
                } else {
                    return 'iOS';
                }
            },
            isautoFormat(item) {
                let value = item.is_auto;

                if (value == 1) {
                    return '是';
                } else {
                    return '否';
                }
            },
            addVersionsDetail(item) {
                let that = this;
                that.total += 1;
                if (Math.ceil(that.versions.length / that.pageSize) === that.currentPage || that.versions.length == 0) {
                    that.versions.unshift(item);
                }
                this.handleVersionsCurrentChange(1)
            },
            editBannerDetail(id) {
                this.$refs.VersionsDetail.editVersions(id);
            },
            getVersionsMaxPage() {
                let that = this;
                axios.get('/content/versions/count')
                    .then(function (response) {
                        that.total = response.data.response.count;
                    })
                    .catch(function (error) {
                        that.dialog.openWarning( function () {
                        }, error.toString());
                    });
            },
            handleVersionsCurrentChange(currentPage) {
                if(this.currentPage!==currentPage){
                    this.currentPage = currentPage;
                }
                let that = this;
                let url = '/content/versions/select?page=' + currentPage + '&limit=' + that.pageSize;
                axios.get(url).then(function (response) {
                    that.versions = response.data.response.list;
                    that.loading = false;
                }).catch(function (error) {
                    that.loading = false;
                });
                this.getVersionsMaxPage();
            },
            handleSizeChange(val) {
                this.pageSize = val;
                this.handleVersionsCurrentChange(1);
            },
            doDeleteVersions(callback) {
                let that = this;
                this.loading = true;
                axios.delete('/content/versions/delete',  {
                    data: JSON.stringify({id: that.selectIds})
                }).then(function (response) {
                    if (parseInt(response.data.code) === 0) {
                        let versions = [];
                        that.versions.forEach(function (item, index) {
                            if (that.selectIds.indexOf(item.id) === -1) {
                                versions.push(item);
                            }
                        });
                        that.versions = versions;
                        that.dialog.openSuccess( function () {
                            that.loading = false;
                            that.handleVersionsCurrentChange(1);
                        });
                    } else {
                        that.dialog.openWarning( function () {
                            that.loading = false;
                        });
                    }
                    callback();
                }).catch(function (error) {
                    that.dialog.openWarning( function () {
                        that.loading = false;
                        callback();
                    }, error.toString());
                });
            },
            deleteVersions() {

                if (this.selectIds.length === 0) {
                    this.dialog.openWarning( function () {
                    }, '請至少選擇一筆資料');
                    return;
                }

                let that = this;
                this.dialog.openDelDialog(that.doDeleteVersions);
            },
            addVersions() {
                this.$refs.VersionsDetail.editVersions(0);
            },
            versionsSelect(rows) {
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

</style>