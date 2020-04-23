<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div style="padding-top:5px;">
                    <el-col :span="4">
                        <el-button-group>
                            <el-button type="primary" class="el-icon-circle-plus" @click="openWindows(0)">新 增</el-button>
                            <el-button type="primary" class="el-icon-remove" @click="deleteAdmin">刪 除</el-button>
                        </el-button-group>
                    </el-col>
                    <el-col :span="20">
                        <el-form :inline="true"  :model="searchAdminFrom" :rules="searchRules" ref="searchAdminFrom" class="demo-form-inline">
                            <el-form-item>
                                <el-select v-model="searchAdminFrom.profile" placeholder="帳號">
                                    <el-option label="帳號" value="account"></el-option>
                                    <el-option label="姓名" value="admin_name"></el-option>
                                    <el-option label="業務單位" value="department"></el-option>
                                    <el-option label="手機號碼" value="admin_mobile"></el-option>
                                    <el-option label="電子郵件" value="admin_email"></el-option>
                                </el-select>
                            </el-form-item>

                            <el-form-item>
                                <el-input v-model="searchAdminFrom.profileValue"></el-input>
                            </el-form-item>

                            <el-form-item>
                                <el-select v-model="searchAdminFrom.type" placeholder="狀態">
                                    <el-option label="狀態" value="status"></el-option>
                                    <el-option label="角色" value="role"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                    <el-select v-model="searchAdminFrom.typeValue" placeholder="請選擇">
                                        <template v-if="searchAdminFrom.type === 'status'">
                                        <el-option label="啓用" value="1"></el-option>
                                        <el-option label="停用" value="2"></el-option>
                                        </template>
                                        <template v-else>
                                            <el-option label="縣府員工" value="2"></el-option>
                                            <el-option label="管理員" value="3"></el-option>
                                        </template>
                                    </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary" icon="el-icon-search" @click="loadData(1, true)">查詢</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>
                </div>
            </el-col>

            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="admin" stripe style="width: 100%" v-loading="loading"  @selection-change="adminSelect">
                    <el-table-column type="selection"></el-table-column>
                    <el-table-column type="index" label="項次" width="50">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="account" label="帳號">
                        <template slot-scope="scope">
                            <el-button type="text" size="small" @click="openWindows(scope.row.id)">{{scope.row.account}}</el-button>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="狀態" :formatter="adminStatusFormat"></el-table-column>
                    <el-table-column prop="role" label="角色" :formatter="adminRoleFormat"></el-table-column>
                    <el-table-column prop="name" label="姓名"></el-table-column>
                    <el-table-column prop="department_name" label="業務單位"></el-table-column>
                    <el-table-column prop="mobile" label="手機號碼">
                        <template slot-scope="scope">
                            <span v-if="scope.row.mobile">
                                +{{scope.row.code}}{{scope.row.mobile}}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="email" label="電子郵件"></el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10,20,30,50,100]"
                            @size-change="limitChange"
                            @current-change="loadData"
                            :current-page="searchAdminFrom.page"
                            :page-size="searchAdminFrom.limit"
                            layout="sizes,total,prev,pager,next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import { AdminSearchRule } from '../../tools/element-ui-validate';
    import Tools from '../../tools/vue-common-tools'

    export default {
        data() {
            return {
                searchContent: '',
                userListEntryRecord: false,
                userListUserInfo: false,
                total: 1,
                admin: [],
                searchAdminFrom:{
                    page : 1,
                    limit : 10,

                    profile:"account",
                    profileValue:"",
                    type:"status",
                    typeValue:"",

                    isSearch : 0
                },
                searchRules :AdminSearchRule,
                loading:true,
                selectIds:[],
                notice:Tools.Dialog(this)
            }
        },

        watch : {
            'searchAdminFrom.type' : function (value) {
                if (value === 'status') {
                    this.searchAdminFrom.typeValue = '1';
                } else {
                    this.searchAdminFrom.typeValue = '2';
                }
            }
        },

        created() {
            this.loadData();
        },

        methods: {
            limitChange (val) {
                this.searchAdminFrom.limit = val;
                this.loadData();
            },

            loadData(page, isSearch) {
                if (isSearch) {
                    this.searchAdminFrom.isSearch = 1;
                }

                if (!this.searchAdminFrom.profileValue && !this.searchAdminFrom.typeValue) {
                    this.searchAdminFrom.isSearch = 0;
                }

                this.searchAdminFrom.page = page ? page : 1;
                let search = JSON.parse(JSON.stringify(this.searchAdminFrom));

                this.loading = true;
                axios.get('/admin/select'+Tools.BuildQueryString(search)).then((response) => {
                    this.loading = false;
                    this.total = response.data.response.count;
                    this.admin = response.data.response.admin;
                }).catch((error) => {
                    this.loading = false;
                    this.notice.openWarning(null, error.toString());
                });
            },

            adminRoleFormat(item) {
                let value = item.role;

                if (value === 1) {
                    return "特約商店"
                }
                if (value === 2) {
                    return "縣府員工"
                }

                if (value === 3) {
                    return "管理員"
                }
            },

            adminStatusFormat(item) {
                let value = item.status;
                if (value === 0) {
                    return "新申請，待審核"
                }
                if (value === 1) {
                    return "啓用"
                }
                if (value === 2) {
                    return "停用"
                }
            },

            openWindows(id) {
                let title = id > 0 ? '修改帳號' : '新增帳號';
                let url = '/#/edit/admin/'+id;

                Tools.OpenNewWindow(url, title, 800, 1024);
            },

            deleteAdmin() {
                if (this.selectIds.length === 0) {
                    return this.notice.openWarning(()=> {}, '請至少選擇一筆資料');
                }

                this.notice.openDelDialog((callback) => {
                    axios.delete('/admin/delete',{data:{id:this.selectIds}}) .then((response) => {
                        if (parseInt(response.data.code) === 0) {
                            let admin = [];
                            this.admin.forEach( (item) => {
                                if (this.selectIds.indexOf(item.id) === -1) {
                                    admin.push(item);
                                }
                            });

                            this.admin = admin;
                            this.notice.openSuccess(() => {
                                this.loading = false;
                                this.selectIds = [];
                            });
                            this.loadData();
                        } else {
                            this.notice.openWarning(() => {
                                this.loading = false;
                            });
                        }

                        callback();
                    }).catch((error) => {
                        this.notice.openWarning(() => {
                            this.loading = false;
                        },error.toString())
                    });
                }, '確定刪除嗎？該帳號將無法再進入管理平台。');
            },

            adminSelect(rows) {
                let ids = [];

                rows.forEach(function (item) {
                    ids.push(item.id);
                });

                this.selectIds = ids;
            }
        }
    }
</script>

<style scoped>
    .el-checkbox-group{
        font-size: 12px;
    }
</style>