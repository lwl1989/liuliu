<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div style="padding-top:5px;">
                    <el-col :span="4">
                        <el-button-group>
                            <el-button type="primary" class="el-icon-circle-plus" @click="openWindows(0)">新 增</el-button>
                            <el-button type="primary" class="el-icon-remove" @click="deleteCompany">刪 除</el-button>
                        </el-button-group>
                    </el-col>
                    <el-col :span="20">
                        <el-form :inline="true"  :model="searchAdminFrom" :rules="searchRules" ref="searchAdminFrom" class="demo-form-inline">
                            <el-form-item>
                                <el-select v-model="searchAdminFrom.profile" placeholder="帳號">
                                    <el-option label="統一編號" value="account"></el-option>
                                    <el-option label="商店名稱" value="shop_name"></el-option>
                                    <el-option label="電話" value="shop_tel"></el-option>
                                    <el-option label="通訊地址" value="address"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-input v-model="searchAdminFrom.profileValue" @keyup.enter.native='loadData(1, true)' auto-complete="off"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="searchAdminFrom.type" placeholder="狀態">
                                    <el-option label="狀態" value="status"></el-option>
                                    <el-option label="類型" value="type"></el-option>
                                </el-select>
                            </el-form-item>

                            <el-form-item>
                                <el-select v-model="searchAdminFrom.typeValue" placeholder="請選擇">
                                    <el-option label="全部" value="0" key="0"></el-option>
                                    <template v-if="searchAdminFrom.type === 'status'">
                                        <el-option label="啓用" value="1"></el-option>
                                        <el-option label="停用" value="2"></el-option>
                                    </template>

                                    <template v-else>
                                        <el-option label="特約商店" value="1"></el-option>
                                        <el-option label="公益團體" value="2"></el-option>
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
                <el-table :data="admin"
                          @selection-change="adminSelect"
                          empty-text="目前沒有符合資料"
                          stripe
                          style="width: 100%"
                          v-loading="loading"
                          :default-sort = "{prop: 'update_time', order: 'descending'}"
                >
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column label="項次" type="index" width="50" sortable>
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="account" label="統一編號" sortable>
                        <template slot-scope="scope">
                            <el-button type="text" size="small" @click="openWindows(scope.row.id)" >{{ scope.row.account }}</el-button>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status" label="狀態" :formatter="adminStatusFormat" sortable>
                    </el-table-column>
                    <el-table-column prop="status" label="角色" :formatter="typeFormat" sortable>
                    </el-table-column>
                    <el-table-column prop="name" label="商店名稱" sortable show-overflow-tooltip>
                    </el-table-column>
                    <el-table-column prop="address" label="地址">
                    </el-table-column>
                    <el-table-column label="電話" sortable>
                        <template slot-scope="scope">
                            {{ scope.row.tel }}{{scope.row.tel_ext?'#'+scope.row.tel_ext:''}}
                        </template>
                    </el-table-column>
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
                loading: true,
                searchAdminFrom:{
                    page : 1,
                    limit : 10,

                    profile:"account",
                    profileValue:"",
                    type:"status",
                    typeValue:"0",

                    role : 1,
                    isSearch : 0
                },
                searchRules : AdminSearchRule,
                notice:Tools.Dialog(this),
                emptyText : '暫無數據',
                selectIds : []
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

            adminStatusFormat(item) {
                let value = item.status;
                if (value === 0) {
                    return "停用"
                }
                if (value === 1) {
                    return "啓用"
                }
                if (value === 2) {
                    return "停用"
                }
            },

            typeFormat(item) {
                let value = item.type;

                if (value === 1) {
                    return "特約商店"
                }

                if (value === 2) {
                    return "公益團體"
                }
            },

            openWindows(id) {
                let title = id > 0 ? '修改特約商店' : '新增特約商店';
                let url = '/#/edit/company/'+id;

                Tools.OpenNewWindow(url, title, 800, 1024);
            },

            deleteCompany() {
                if (this.selectIds.length === 0) {
                    return this.notice.openWarning(null, '請至少選擇一筆資料');
                }

                this.notice.openSelfDialog((callback) => {
                    axios.delete('/admin/delete', {data:{id:this.selectIds}}).then((response) => {
                        if (response.data.code === 0) {
                            let admin = [];
                            this.admin.forEach((item) => {
                                if (this.selectIds.indexOf(item.id) === -1) {
                                    admin.push(item);
                                }
                            });

                            this.admin = admin;
                            this.notice.openSuccess(() => {
                                this.loading = false;
                                this.selectIds = [];
                            });
                        } else {
                            this.notice.openWarning(() => {
                                this.loading = false;
                            });
                        }
                        callback();
                    }).catch((error) => {
                        this.notice.openWarning(() => {
                            this.loading = false;
                        }, error.toString())
                    });
                }, '確定刪除嗎？資料刪除後將無法復原，且該店家的商品將全數一起刪除。');
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