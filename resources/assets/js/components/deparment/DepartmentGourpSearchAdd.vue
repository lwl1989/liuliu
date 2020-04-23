<template>
    <div>
        <el-dialog title="搜尋加入" :visible.sync="preferVisible" :modal-append-to-body="false" :close-on-click-modal="false" class="dialog-import">

            <el-form size="medium" :label-width="formLabelWidth" style="margin-top:20px" stripe v-loading="loading">

                <el-row>
                    <el-col :span="12">

                        <el-form :model="departmentGroupForm" :rules="rules" ref="departmentGroupForm">
                            <el-form-item label="業務單位" prop="department_id">
                                <el-select v-model="departmentGroupForm.department_id" @change="changeDepartment"
                                           placeholder="請選擇">
                                    <template v-for="(item) in unit_sum_array">
                                        <el-option :label="item.name" :value="item.id.toString()"
                                                   :key="item.id.toString()"></el-option>
                                    </template>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="群組名稱" prop="name">
                                <el-col :span="15">
                                    <el-form-item>
                                        <el-radio v-model="departmentGroupForm.groupRadio" :label="1">加入現有群組</el-radio>
                                        <el-select v-model="departmentGroupForm.selectId" placeholder="選擇群組">
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
                                        <el-input v-model="departmentGroupForm.name" auto-complete="off"
                                                  style="width: 200px"/>
                                    </el-form-item>
                                </el-col>
                            </el-form-item>
                            <el-form-item label="是否分享" prop="share">
                                <el-checkbox v-model="departmentGroupForm.share"></el-checkbox>
                            </el-form-item>
                        </el-form>
                    </el-col>
                </el-row>
                <el-row>
                    <el-form>
                        <el-form-item label="問卷關鍵字查詢:" style="margin-left: 0px">
                            <el-input v-model="questionnaireSearch.name" auto-complete="off" style="width: 200px"/>
                            <el-button type="primary" icon="el-icon-search" @click="searchQuestion">搜尋</el-button>
                        </el-form-item>
                        <el-form-item>
                            <div v-for="item in questionnaires" :key="item.id">
                                <a href="javascript:void(0)" @click="openMessage(item.id,0)"
                                   style="text-decoration:none;color: #00afff;">{{ item.title }}</a>
                            </div>
                        </el-form-item>
                    </el-form>
                    <el-form>
                        <el-form-item label="活動關鍵字查詢:" style="margin-left: 0px">
                            <el-input v-model="activitySearch.name" auto-complete="off" style="width:200px"/>
                            <el-button type="primary" icon="el-icon-search" @click="searchActivity">搜尋</el-button>
                        </el-form-item>
                        <el-form-item>
                            <div v-for="item in activities" :key="item.id">
                                <a href="javascript:void(0)" @click="openMessage(item.id,1)"
                                   style="text-decoration:none;color: #00afff;">{{ item.name }}</a>
                            </div>
                        </el-form-item>

                    </el-form>

                </el-row>
                <el-row>
                    <el-form>
                        <el-form-item label="已選取的條件為">
                            <span style="text-decoration:none;color: #00afff;">{{chooseNum}}人</span>
                            <div v-for="(question,index) in chooses">
                                <el-button type="danger" icon="el-icon-delete" circle @click="delChoose(index)"
                                           style="width: 20px; height: 20px; padding: 0px"/>
                                {{question.title}}({{question.chooseNum}})
                            </div>
                        </el-form-item>
                        <el-form-item label="扣除掉重複的人總共為">
                            <span style="text-decoration:none;color: #00afff;">{{realNum}}人</span>
                        </el-form-item>
                    </el-form>
                </el-row>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" icon="el-icon-save" @click="save">儲存</el-button>
            </div>

        </el-dialog>
        <department-group-message ref="messageDetail" v-on:choose="chooseAnswers"></department-group-message>
    </div>
</template>

<script>
    import ElRow from "element-ui/packages/row/src/row";
    import Tools from "../../tools/vue-common-tools";
    import {DepartmentGroupRule, DepartmentGroupSearchRule} from '../../tools/element-ui-validate';
    import DepartmentGroupMessage from './DepartmentGroupMessage'

    export default {
        components: {ElRow, DepartmentGroupMessage},
        name: "department-gourp-search-add",
        data: function () {
            return {
                dialog: new Tools.Dialog(this),
                formLabelWidth: '120px',
                departmentGroups: [],
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
                    groupRadio: 1,
                    selectId: '',
                    file_path: '',
                    users: []
                },
                questionnaireSearch: {
                    name: ''
                },
                activitySearch: {
                    name: ''
                },
                questionnaires: [],
                activities: [],
                unit_sum_array: [],
                groups: [],
                unitType: '1',
                searchRules: DepartmentGroupSearchRule,
                rules: DepartmentGroupRule,
                chooseNum: 0,
                realNum: 0,
                chooses: [],
                loading: true,
                preferVisible: false
            }
        },
        methods: {
            open() {
                this.preferVisible = true;
                if (this.$refs.departmentGroupForm !== undefined) {
                    this.$refs.departmentGroupForm.resetFields();
                }
                this.deparment_id = ''
                this.departmentGroupForm.selectId = ''
                this.departmentGroups = []
                this.questionnaires = []
                this.activities = []
                this.unit_sum_array = []
                this.groups = []
                this.unitType = '1'
                this.chooseNum = 0
                this.realNum = 0
                this.chooses = []
                this.questionnaireSearch = {
                    name: ''
                }
                this.activitySearch = {
                    name: ''
                }
                this.loadData();
            },
            changeDepartment: function (id) {
                axios.get('/department/group/getGroupsOfDepartment?id=' + id)
                    .then((response) => {
                        this.groups = response.data.response.list;
                        if (!this.groups || this.groups.length === 0) {
                            this.departmentGroupForm.selectId = '';
                        }
                    }).catch(() => {
                    this.loading = false;
                });
            },
            loadData: function () {
                axios.get('/department/getAllUnit')
                    .then((response) => {
                        for (let key in response.data.response) {
                            this.unit_sum_array.push({id: key, name: response.data.response[key]});
                        }
                        this.loading = false;
                        // if (this.unit_sum_array.length > 0) {
                        //     this.changeDepartment(this.unit_sum_array[0].id);
                        // }
                    }).catch(() => {
                    this.loading = false;
                });
            },
            searchQuestion() {
                axios.get('/department/group/searchQuestionnaire?name=' + this.questionnaireSearch.name)
                    .then((response) => {
                        this.questionnaires = response.data.response.list;
                        if (!this.questionnaires || this.questionnaires.length === 0) {
                            this.dialog.openWarning(null, '無符合條件')
                        }
                    }).catch((error) => {
                    console.error(error);
                });
            },
            searchActivity() {
                axios.get('/department/group/searchActivity?name=' + this.activitySearch.name)
                    .then((response) => {
                        this.activities = response.data.response.list;
                        if (!this.activities || this.activities.length === 0) {
                            this.dialog.openWarning(null, '無符合條件')
                        }
                    }).catch((error) => {
                    console.error(error);
                });
            },
            openMessage(id, type) {
                this.$refs.messageDetail.openMsg(id, type);
            },
            delChoose(index) {
                let tmp = this.chooses[index]
                this.chooseNum -=tmp.chooseNum
                this.chooses.splice(index, 1);
                this.departmentGroupForm.users = []
                let that = this
                this.chooses.forEach(function (item) {
                    that.departmentGroupForm.users = that.departmentGroupForm.users.concat(item.users)
                })
                this.departmentGroupForm.users = this.uniq(this.departmentGroupForm.users);
                this.realNum = this.departmentGroupForm.users.length;
            },
            chooseAnswers(data) {
                this.chooses.push(data)
                this.chooseNum += data.chooseNum;
                this.departmentGroupForm.users = this.departmentGroupForm.users.concat(data.users);
                this.departmentGroupForm.users = this.uniq(this.departmentGroupForm.users);
                this.realNum = this.departmentGroupForm.users.length;
            },
            uniq(array) {
                var temp = []; //一個新的臨時數組
                for (var i = 0; i < array.length; i++) {
                    if (temp.indexOf(array[i]) == -1) {
                        temp.push(array[i]);
                    }
                }
                return temp;
            },
            save() {
                let that = this;
                if (this.chooses.length === 0 || this.realNum === 0) {
                    return this.dialog.openError(null, '請選擇要匯入的成員');
                }
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
                        this.requestApi(groupName);
                    }
                } else {
                    // 表單驗證方法
                    this.$refs.departmentGroupForm.validate(function (result) {
                        if (result) {
                            this.requestApi(that.departmentGroupForm.name)
                        } else {
                            console.log(result)
                        }
                    }.bind(this));
                }
            },
            requestApi(groupName) {
                const h = this.$createElement;
                let that = this;
                //驗證通過,調用module裏的setUserInfo方法
                //this.$store.dispatch("setUserInfo");
                this.$msgbox({
                    title: '提示',
                    message: h('p', null, [
                        h('span', null, '確定要匯入'),
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
                                        that.total += 1;
                                        if (Math.ceil(that.departmentGroups.length / that.pageSize) === that.currentPage) {
                                            that.departmentGroupForm.create_time = response.data.response.create_time;
                                            that.departmentGroupForm.update_time = response.data.response.update_time;
                                            that.departmentGroups.push(that.departmentGroupForm);
                                        }
                                        instance.confirmButtonLoading = false;
                                        that.$message({
                                            showClose: true,
                                            message: '匯入完畢，成功' + response.data.response.count + '筆，失敗0筆',
                                            type: 'success'
                                        });
                                        setTimeout(() => {
                                            done();
                                            that.preferVisible = false;
                                            that.$emit('searchAdded')
                                        }, 2000);
                                    } else {
                                        that.openDepartmentWarning()
                                        setTimeout(() => {
                                            instance.confirmButtonLoading = false;
                                            done();
                                        }, 2000);
                                    }
                                }).catch(function (error) {
                                that.openDepartmentWarning();
                                setTimeout(() => {
                                    instance.confirmButtonLoading = false;
                                    done();
                                }, 2000);
                            });
                        } else {
                            done();
                            //that.openDepartmentWarning();
                        }
                    }
                });
            },
            openDepartmentWarning: function () {
                this.$emit('warning', () => {

                });
            },
        }
    }
</script>

<style scoped>

</style>