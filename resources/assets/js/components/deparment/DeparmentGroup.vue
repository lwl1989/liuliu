<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div class="app-search-bg">
                </div>
                <div style="padding-top:5px;">
                    <el-col :span="20">
                        <el-form :inline="true" :model="searchForm" :rules="searchRules" ref="searchForm"
                                 class="demo-form-inline">
                            <el-form-item>
                                <el-select v-model="unitType" placeholder="群組名稱">
                                    <el-option label="群組名稱" value="0"></el-option>
                                    <el-option label="業務單位" value="1"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-input v-model="searchForm.name"></el-input>
                            </el-form-item>
                            <!--<el-form-item label="是否使用">-->
                            <!--<el-select v-model="searchForm.use_state" placeholder="使用中">-->
                            <!--<el-option label=" " value="-1"></el-option>-->
                            <!--<el-option label="使用中" value="1"></el-option>-->
                            <!--<el-option label="未使用" value="0"></el-option>-->
                            <!--</el-select>-->
                            <!--</el-form-item>-->
                            <el-form-item>
                                <el-select v-model="searchForm.date_type" placeholder="最後異動日期">
                                    <el-option label="最後異動日期" value="0"></el-option>
                                    <el-option label="新增日期" value="1"></el-option>
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
                            </el-form-item>
                        </el-form>
                    </el-col>
                    <el-col :span="8">
                        <el-button-group>
                            <el-button type="primary" class="el-icon-circle-plus" @click="importDepartmentGroup">
                                匯入群組資料
                            </el-button>
                            <el-button type="primary" class="el-icon-circle-plus" @click="searchDepartmentGroup">搜尋加入群組</el-button>
                            <el-button type="primary" class="el-icon-remove" @click="deleteDepartmentGroup">刪除
                            </el-button>
                        </el-button-group>
                    </el-col>
                </div>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="departmentGroups" stripe style="width: 100%" v-loading="loading"
                          @selection-change="handleSelectionChange">
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column prop="department_name" label="業務單位">
                    </el-table-column>
                    <el-table-column prop="name" label="群組名稱">
                        <template slot-scope="scope">
                            {{scope.row.code}} {{ scope.row.name }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="group_number" label="群組人數">
                        <template slot-scope="scope">
                        <a href="javascript:void(0)" @click="openMember(scope.row.id, 1)" style="text-decoration:none;color: #00afff;">{{ scope.row.group_number }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="valid_group_number" label="有效群組人數">
                        <template slot-scope="scope">
                        <a href="javascript:void(0)" @click="openMember(scope.row.id, 2)" style="text-decoration:none;color: #00afff;">{{ scope.row.valid_group_number }}</a>
                        </template>
                    </el-table-column>
                    <!--<el-table-column prop="version_number" label="版號">-->
                    <!--</el-table-column>-->
                    <!--<el-table-column prop="use_state" label="是否使用">-->
                    <!--</el-table-column>-->
                    <!--<el-table-column prop="import_state" label="匯入狀態">-->
                    <!--</el-table-column>-->
                    <el-table-column prop="create_time" label="新增時間">
                    </el-table-column>
                    <el-table-column prop="update_time" label="最後異動時間">
                    </el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10,20,30,50,100]"
                            @size-change="limitChange"
                            @current-change="loadData"
                            :current-page="searchForm.page"
                            :page-size="searchForm.limit"
                            layout="sizes,total,prev,pager,next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <el-dialog title="匯入群組資料" :visible.sync="departmentGroupsFormImportVisible" :modal-append-to-body="false"
                   :close-on-click-modal="false" class="dialog-import" @close="cancelImport">
            <el-form :model="departmentGroupForm" :rules="rules" ref="departmentGroupForm">
                <el-form-item label="業務單位" :label-width="formLabelWidth" prop="department_id">
                    <el-select v-model="departmentGroupForm.department_id" @change="changeDepartment" placeholder="請選擇">
                        <template v-for="(item) in unit_sum_array">
                            <el-option :label="item.name" :value="item.id.toString()"
                                       :key="item.id.toString()"></el-option>
                        </template>
                    </el-select>
                </el-form-item>
                <el-form-item label="群組名稱" :label-width="formLabelWidth" prop="name">
                    <el-col :span="15">
                        <el-form-item>
                            <el-radio v-model="departmentGroupForm.groupRadio" :label="1">加入現有群組</el-radio>
                            <el-select v-model="departmentGroupForm.selectId" placeholder="選擇群組" no-data-text="選擇群組">
                                <el-option
                                        v-for="(item) in groups"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item>
                            <el-radio v-model="departmentGroupForm.groupRadio" :label="2">新增群組</el-radio>
                            <el-input v-model="departmentGroupForm.name" auto-complete="off"/>
                        </el-form-item>
                    </el-col>
                </el-form-item>
                <el-form-item label="是否分享" :label-width="formLabelWidth" prop="share">
                    <el-checkbox v-model="departmentGroupForm.share"></el-checkbox>
                </el-form-item>
                <el-form-item label="群組成員檔案" :label-width="formLabelWidth" prop="fileList">
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
                                   :file-list="departmentGroupForm.fileList"
                                   :with-credentials="withCredentials">
                            <!-- 是否支持發送cookie訊息 -->
                            <el-button size="small" type="primary" :disabled="processing">{{uploadTip}}</el-button>
                            <div slot="tip" class="el-upload__tip">僅限 .xls .xlsx 格式</div>
                        </el-upload>
                        <div class="download-template">
                        <a href="javascript:void(0)" class="btn-download" @click="download" style="text-decoration:none;color: #00afff;">
                        範本下載
                        </a>
                        </div>
                    </div>
                    <div :class="{'import-failure': importFlag === 2, 'hide-dialog': importFlag !== 2}">
                        <div class="failure-tips">
                            <i class="el-icon-warning"></i>導入失敗
                        </div>
                        <div class="failure-reason">
                            <h4>失敗原因</h4>
                            <ul>
                                <li v-for="(error,index) in errorResults" :key="index">第{{error.rowIdx +
                                    1}}行，錯誤：{{error.column}},{{error.value}},{{error.errorInfo}}
                                </li>
                            </ul>
                        </div>
                    </div>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="cancelImport">取 消</el-button>
                <el-button type="primary" @click="submitDepartmentGroup('匯入群組資料')">確 定</el-button>
            </div>
        </el-dialog>

        <el-dialog title="新增群組" :visible.sync="departmentGroupsFormVisible" @close="cancelImport">
            <el-form :model="departmentGroupForm" :rules="rules" ref="departmentGroupForm">
                <el-form-item label="業務單位" :label-width="formLabelWidth" prop="department_id">
                    <el-select v-model="departmentGroupForm.department_id" placeholder="">
                        <template v-for="(item) in unit_sum_array">
                            <el-option :label="item.name" :value="item.id.toString()"
                                       :key="item.id.toString()"></el-option>
                        </template>
                    </el-select>
                </el-form-item>
                <el-form-item label="群組名稱" :label-width="formLabelWidth" prop="name">
                    <el-col :span="15">
                        <el-input v-model="departmentGroupForm.name" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="是否分享群組" :label-width="formLabelWidth" prop="share">
                    <el-checkbox v-model="departmentGroupForm.share"></el-checkbox>
                </el-form-item>
                <el-form-item label="問卷關鍵字查詢" :label-width="formLabelWidth" prop="questionKey">
                    <el-col :span="15">
                        <el-input v-model="departmentGroupForm.questionKey" auto-complete="off"></el-input>
                    </el-col>
                    <el-radio-group v-model="departmentGroupForm.radio">
                        <el-radio :label="1">備選項1</el-radio>
                        <el-radio :label="2">備選項2</el-radio>
                        <el-radio :label="3">備選項3</el-radio>
                    </el-radio-group>
                </el-form-item>
                已選取的條件為 {{count}} 人
                <el-form-item label="" :label-width="formLabelWidth" prop="result">
                    <el-col :span="15">
                        <el-input v-model="departmentGroupForm.result" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="cancelImport">取 消</el-button>
                <el-button type="primary" @click="submitDepartmentGroup('新增群組')">確 定</el-button>
            </div>
        </el-dialog>
        <department-group-search-add ref="searchAdd" v-on:searchAdded="searchAdd"></department-group-search-add>
    </div>
</template>

<script>
    import DepartmentGroupSearchAdd from './DepartmentGourpSearchAdd'
    import {DepartmentGroupRule, DepartmentGroupSearchRule} from '../../tools/element-ui-validate';
    import Tools from "../../tools/vue-common-tools";

    export default {
        name: "departmentIndexComponent",
        components: {DepartmentGroupSearchAdd},
        data: function () {
            return {
                total: 1,
                dialog: new Tools.Dialog(this),
                departmentGroups: [],
                departmentGroupsFormVisible: false,
                departmentGroupsFormImportVisible: false,
                formLabelWidth: '120px',
                departmentGroupForm: {
                    department_id: '',
                    name: '',
                    share: false,
                    questionKey: '',
                    result: '',
                    radio: 1,
                    code: '',
                    version_number: '',
                    group_number: 0,
                    valid_group_number: 0,
                    file_path: '',
                    groupRadio: 1,
                    selectId: '',
                    users: [],
                    fileList: []
                },
                searchForm: {
                    page: 1,
                    name: '',
                    date_type: '0',
                    time: [],
                    limit: 10,
                    use_state: '-1',
                },
                searchRules: DepartmentGroupSearchRule,
                rules: DepartmentGroupRule,
                multipleSelection: [],
                deleteIds: {
                    id: []
                },
                count: 0,
                unit_sum_array: [],
                importHeaders: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                groups: [],
                importFlag: 1,
                unitType: '1',
                name: 'file',

                withCredentials: true,
                processing: false,
                uploadTip: '選擇檔案',
                errorResults: [],
                importUrl: '/department/group/file',
                loading: true
            }
        },

        created: function () {
            this.loadData(1);
        },

        methods: {
            cancelImport() {
                this.departmentGroupsFormImportVisible = false
                this.departmentGroupsFormVisible = false
                this.$refs.departmentGroupForm.resetFields();
                this.departmentGroupForm.file_path = ''
                this.departmentGroupForm.selectId = '';
            },
            submitDepartmentGroup(actionName) {
                if (!this.departmentGroupForm.file_path) {
                    return this.dialog.openError(null, '尚未上傳成員檔案');
                }
                if (!this.departmentGroupForm.department_id ) {
                    return this.dialog.openError(null, '尚未選擇業務單位');
                }
                let that=this;
                if (this.departmentGroupForm.groupRadio === 1) {
                    if (!this.departmentGroupForm.selectId) {
                        return this.dialog.openError(null, '請選擇要匯入的群組');
                    } else {
                        let groupName;
                        this.groups.forEach(function (item, key) {
                            if (item.id === that.departmentGroupForm.selectId) {
                                groupName = item.name;
                            }
                        })
                        this.requestApi(actionName, groupName);
                    }
                } else {
                    this.departmentGroupForm.selectId = ''
                    // 表單驗證方法
                    this.$refs.departmentGroupForm.validate(function (result) {
                        if (result) {
                            this.requestApi(actionName, that.departmentGroupForm.name)
                        } else {
                            console.log(result)
                        }
                    }.bind(this));
                }
            },
            requestApi(actionName, groupName) {
                const h = this.$createElement;
                let that = this;
                //驗證通過,調用module裏的setUserInfo方法
                //this.$store.dispatch("setUserInfo");
                this.$msgbox({
                    title: '提示',
                    message: h('p', null, [
                        h('span', null, '確定要創建' + actionName),
                        h('i', {style: 'color: teal'}, groupName),
                        h('span', null, ' 嗎？')
                    ]),
                    showCancelButton: true,
                    confirmButtonText: '確定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            instance.confirmButtonLoading = true;
                            instance.confirmButtonText = '創建中...';
                            // let url = that.departmentGroupForm.id > 0 ? '/department/group/update?id=' + that.departmentGroupForm.id : '/department/group/create';
                            let url = '/department/group/create';
                            axios.post(url, that.departmentGroupForm)
                                .then(function (response) {
                                    if (response.data.code === 0) {
                                        that.$refs.departmentGroupForm.resetFields();
                                        that.departmentGroupForm.file_path = ''
                                        that.departmentGroupForm.selectId = '';

                                        that.total += 1;
                                        if (Math.ceil(that.departmentGroups.length / that.pageSize) === that.currentPage) {
                                            that.departmentGroupForm.create_time = response.data.response.create_time;
                                            that.departmentGroupForm.update_time = response.data.response.update_time;
                                            that.departmentGroups.push(that.departmentGroupForm);
                                        }
                                        that.departmentGroupsFormVisible = false;
                                        that.departmentGroupsFormImportVisible = false;
                                        instance.confirmButtonLoading = false;
                                        that.$message({
                                            showClose: true,
                                            message: '匯入完畢，成功'+response.data.response.count+'筆，失敗0筆',
                                            type: 'success'
                                        });
                                        setTimeout(() => {
                                            done();
                                            that.loadData(1);
                                        }, 2000);
                                    } else {
                                        that.openDepartmentWarning()
                                        instance.confirmButtonLoading = false;
                                        done();
                                    }
                                }).catch(function (error) {
                                that.openDepartmentWarning();
                                instance.confirmButtonLoading = false;
                                done();
                            });
                        } else {
                            instance.confirmButtonLoading = false;
                            done();
                            //that.openDepartmentWarning();
                        }
                    }
                });
            },
            openDepartmentSuccess: function () {
                this.$emit('success', () => {
                    this.loadData(1);
                });
            },

            openDepartmentWarning: function () {
                this.$message({
                    showClose: true,
                    message: '匯入失敗，匯入資料錯誤',
                    type: 'warning'
                });
            },

            limitChange: function (limit) {
                this.searchForm.limit = limit;
                this.loadData(1);
            },

            loadData: function (page) {
                if(this.searchForm.page!==page){
                    this.searchForm.page = page;
                }
                let search = JSON.parse(JSON.stringify(this.searchForm));
                if (this.searchForm.name && this.unitType === '1') {
                    search.department_name = this.searchForm.name;
                    search.name = '';
                }

                if (this.searchForm.time && this.searchForm.time.length === 2) {
                    search.date_start = this.searchForm.time[0].getTime().toString();
                    search.date_end = this.searchForm.time[1].getTime().toString();
                    search.time = '';
                }

                let queryString = Tools.BuildQueryString(search, page);

                axios.get('/department/group/count' + queryString)
                    .then((response) => {
                        this.total = response.data.response.count;
                    });

                this.loading = true;
                axios.get('/department/group/select' + queryString)
                    .then((response) => {
                        this.loading = false;
                        this.departmentGroups = response.data.response.data;
                    }).catch(() => {
                    this.loading = false;
                });
            },

            addDepartmentGroup: function () {
                this.departmentGroupsFormVisible = true;
            },
            changeDepartment: function (id) {
                axios.get('/department/group/getGroupsOfDepartment?id=' + id)
                    .then((response) => {
                        this.groups = response.data.response.list;
                        if(!this.groups||this.groups.length===0) {
                            this.departmentGroupForm.selectId='';
                        }
                    }).catch(() => {
                    this.loading = false;
                });
            },
            importDepartmentGroup: function () {
                if(this.$refs.departmentGroupForm!==undefined){
                    this.$refs.departmentGroupForm.resetFields();
                }
                this.unit_sum_array=[];
                this.departmentGroupsFormImportVisible = true;
                axios.get('/department/getAllUnit')
                    .then((response) => {
                        for (let key in response.data.response) {
                            this.unit_sum_array.push({id: key, name: response.data.response[key]});
                        }
                        if (this.unit_sum_array.length > 0) {
                            this.changeDepartment(this.unit_sum_array[0].id);
                        }
                    }).catch(() => {
                    this.loading = false;
                });
            },

            deleteDepartmentGroup: function () {
                if (!this.multipleSelection||this.multipleSelection.length===0) {
                    return this.dialog.openError(null, '請至少選擇一筆資料');
                }
                let that = this;
                this.$msgbox({
                    title: '提示',
                    message: '確定刪除嗎？資料刪除後將無法復原。',
                    showCancelButton: true,
                    confirmButtonText: '確定',
                    cancelButtonText: '取消',
                    beforeClose: (action, instance, done) => {
                        if (action === 'confirm') {
                            //選獲取所有中行的id組成的字符串，以逗號分隔
                            let ids = that.multipleSelection.map(item => item.id).join();
                            axios.delete('/department/group/delete',
                                {
                                    data: JSON.stringify({id: ids})
                                })
                                .then(function (response) {
                                    if (response.data.code === 0) {
                                        if (response.data.response.row !== undefined && response.data.response.row.length > 0) {
                                            that.openDepartmentSuccess();
                                        }
                                        if (response.data.response.length === 0) {
                                            that.openDepartmentWarning();
                                        }
                                    } else {
                                        that.openDepartmentWarning();
                                    }
                                    done()
                                })
                                .catch(function (error) {
                                    console.log(error);
                                    done()
                                });
                        }else{
                            done();
                        }}});

            },

            handleSelectionChange: function (val) {
                this.multipleSelection = val;
            },

            handlePreview: function (file) {

            },

            handleRemove: function (file, fileList) {
                this.departmentGroupForm.fileList=[]
            },

            beforeUpload: function (file) {
                this.departmentGroupForm.fileList.push(file)
                //上傳前配置
                let excelfileExtend = ".xls,.xlsx";
                let fileExtend = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
                if (excelfileExtend.indexOf(fileExtend) <= -1) {
                    this.$message.error('匯入失敗，匯入資料錯誤');
                    return false
                }
                this.uploadTip = '正在處理中...';
                this.processing = true
            },

            uploadFail: function (err, file, fileList) {
                this.uploadTip = '選擇檔案'
                this.processing = false
                this.$message.error(err)
            },

            uploadSuccess: function (response, file, fileList) {
                let that = this;
                this.uploadTip = '選擇檔案';
                this.processing = false;
                if (response.status === -1) {
                    this.errorResults = response.data;
                    if (this.errorResults) {
                        this.importFlag = 2
                    } else {
                        this.$message.error(response.errorMsg)
                    }
                } else {
                    that.departmentGroupForm.file_path = response.response.path;
                }
            },

            download: function () {
                window.location.href = '/department/group/download';
            },

            openMember: function (id, type) {
                Tools.OpenNewWindow(
                    "/#/departmentGroup/members/" + id+"?type="+type,
                    "縣民群組名單",
                    800,
                    1024
                );
            },
            searchDepartmentGroup () {
                this.$refs.searchAdd.open()
            },
            searchAdd () {
                this.loadData(1)
            }
        }
    }
</script>

<style scoped>
    .el-checkbox-group {
        font-size: 12px;
    }

    .hide-dialog {
        display: none;
    }
</style>
