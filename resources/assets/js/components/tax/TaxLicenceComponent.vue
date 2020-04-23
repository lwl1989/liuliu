<template>
    <div id="app">
        <el-row>
            <el-form :inline="true" :model="searchTaxLicenceForm" ref="searchTaxLicenceForm" class="demo-form-inline">
                <el-form-item>
                    <el-input v-model="searchTaxLicenceForm.keys" @keyup.enter.native='loadData(1)' auto-complete="off"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="searchTaxLicence">查詢</el-button>
                </el-form-item>
            </el-form>
            <el-col :span="8">
                <div :class="{'import-content': importFlag === 1, 'hide-dialog': importFlag !== 1}">
                    <el-upload class="upload-demo"
                               :action="importUrl"
                               :multiple=false
                               :limit=1
                               :name="name"
                               :headers="importHeaders"
                               :on-preview="handlePreview"
                               :on-remove="handleRemove"
                               :before-upload="beforeUpload"
                               :on-error="uploadFail"
                               :on-success="uploadSuccess"
                               :file-list="taxLicenceForm.fileList"
                               :with-credentials="withCredentials">
                        <el-button size="small" type="primary" :disabled="processing">{{uploadTip}}</el-button>
                    </el-upload>
                </div>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="licence" v-loading="loading" empty-text="目前沒有符合資料"  :row-class-name="tableRowClassName">
                    <el-table-column prop="name" label="車牌資料">
                        <template slot-scope="scope">
                            <a type="primary" v-if="scope.$index == 0 && currentPage == 1" href="javascript:void(0)"
                               @click="download(scope.row.id)">{{ scope.row.name }}</a>
                            <el-link :underline="false" v-else>{{ scope.row.name }}</el-link>
                        </template>
                    </el-table-column>
                    <el-table-column prop="import_num" label="匯入數量">
                    </el-table-column>
                    <el-table-column prop="valid_num" label="有效數量">
                    </el-table-column>
                    <el-table-column prop="admin_info" label="最後異動資訊">
                    </el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24" v-if="total > 0">
                <div class="app-pagination">
                    <el-pagination
                        :page-sizes="[10, 20, 30, 50, 100]"
                        @size-change="handleSizeChange"
                        @current-change="loadData"
                        :current-page="currentPage"
                        :page-size="pageSize"
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
    import dialog from '../../tools/element-ui-dialog';

    export default {
        name: "TaxLicenceComponent",
        data: function () {
            return {
                currentPage: 1,
                pageSize: 10,
                total: 1,
                loading: true,
                licence: [],
                dialog: dialog(this),
                name: 'file',
                importFlag: 1,
                importUrl: '/tax/licence/file',
                importHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                searchTaxLicenceForm: {
                    keys : ''
                },
                taxLicenceForm: {
                    name : '',
                    fileList: []
                },
                withCredentials: true,
                processing: false,
                uploadTip: '變更檔案',
                errorResults: [],
            }
        },

        mounted: function () {
            this.$nextTick(function () {
                this.loadData(1);
            })
        },
        methods: {
            handleSizeChange(val) {
                this.pageSize = val;
                this.loadData(1);
            },

            handlePreview: function (file) {

            },

            handleRemove: function (file, fileList) {
                this.taxLicenceForm.fileList = [];
            },

            beforeUpload: function (file) {
                this.taxLicenceForm.fileList.push(file);
                let excelfileExtend = ".xls,.xlsx";
                let fileExtend = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
                if (excelfileExtend.indexOf(fileExtend) <= -1) {
                    this.$message.error('匯入失敗，匯入資料錯誤');
                    return false;
                }
                this.uploadTip = '正在處理中...';
                this.processing = true;
            },

            uploadFail: function (err) {
                this.uploadTip = '變更檔案';
                this.processing = false;
                this.$message.error(err)
            },

            uploadSuccess: function (response) {
                let that = this;
                this.uploadTip = '變更檔案';
                this.processing = false;

                if (response.code !== 0) {
                    this.errorResults = response.data;
                    if (this.errorResults) {
                        this.importFlag = 2;
                    } else {
                        this.$message.error(response.errorMsg)
                    }
                } else {
                    that.taxLicenceForm.name = response.response.name;
                    that.taxLicenceForm.file_path = response.response.path;

                    axios.post('/tax/licence/create', that.taxLicenceForm)
                        .then(function (response) {
                            if (response.data.response.id) {
                                window.location.reload();
                            }
                        })
                        .catch(function (error) {
                            that.dialog.openWarning(function () {
                            }, error.toString());
                        });
                }
            },

            tableRowClassName({row}) {
                let timeNow = new Date().getTime()
                let upTime  = new Date(row.online_time).getTime();
                let downTime = new Date(row.offline_time).getTime();
                if (row.status === 2 && timeNow > upTime && timeNow <= downTime) {
                    return 'warning-row';
                }

                return '';
            },

            getSearchUrl() {
                return 'keys=' + this.searchTaxLicenceForm.keys;
            },

            loadData(currentPage) {
                let that = this;
                that.loading = true;

                if (this.currentPage !== currentPage) {
                    this.currentPage = currentPage;
                }

                let url = '/tax/licence/select?page=' + currentPage + '&limit=' + that.pageSize + '&' + this.getSearchUrl();

                axios.get(url).then(function (response) {
                    that.licence = response.data.response.data;
                    that.total = response.data.response.count;
                    that.loading = false;
                }).catch(function (error) {
                    that.loading = false;
                });
            },

            searchTaxLicence() {
                this.loadData(1);
            },

            handleSizeChange(val) {
                this.pageSize = val;
                this.loadData(1);
            },

            download(id) {
                window.location.href = '/tax/licence/download/' + id;
            }
        },
    }
</script>

<style>
    .warning-row {
        background: oldlace !important;
    }
</style>

<style scoped>

</style>