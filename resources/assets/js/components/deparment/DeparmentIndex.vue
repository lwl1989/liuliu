<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div style="padding-top:5px;">
                    <el-col :span="4">
                        <el-button-group>
                            <el-button type="primary" class="el-icon-circle-plus" @click="addDepartment">新增</el-button>
                            <el-button type="primary" class="el-icon-remove" @click="deleteDepartment">刪除</el-button>
                        </el-button-group>
                    </el-col>
                    <el-col :span="20">
                        <el-form :inline="true" :model="searchForm" :rules="searchRules" ref="searchForm" class="demo-form-inline">
                            <el-form-item>
                                <el-select v-model="searchForm.type" placeholder="業務單位">
                                    <el-option label="業務單位" value="1"></el-option>
                                    <el-option label="地址" value="2"></el-option>
                                    <el-option label="電話" value="3"></el-option>
                                    <el-option label="傳真" value="4"></el-option>
                                    <el-option label="最後變動者" value="5"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-input v-model="searchForm.name" auto-complete="off"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary" icon="el-icon-search" @click="searchContentClick(1)">查詢</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>
                </div>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="departments" stripe style="width: 100%" v-loading="loading" @selection-change="handleSelectionChange">
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column prop="id" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="業務單位">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;" @click="editDepartment(scope.row.id)">{{ scope.row.name }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column label="隸屬單位" prop="parent_name"></el-table-column>
                    <el-table-column prop="address" label="地址">
                        <template slot-scope="scope">
                            {{scope.row.mail_code}}  {{ scope.row.address }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="phone" label="電話">
                    </el-table-column>
                    <el-table-column prop="facsimile" label="傳真">
                    </el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10,20,30,50,100]"
                            @size-change="handleSizeChange"
                            @current-change="handleCurrentChange"
                            :current-page="currentPage"
                            :page-size="pageSize"
                            layout="sizes,total,prev,pager,next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <department-detail ref="DepartmentDetail" v-on:add="addDepartmentDetail"></department-detail>
    </div>
</template>

<script>
    import { DepartmentSearchRule, DepartmentRule } from '../../tools/element-ui-validate';
    import DepartmentDetail from  './DepartmentDetail'
    import Tools from "../../tools/vue-common-tools";

    export default {
        name: "departmentIndexComponent",
        components:{DepartmentDetail},
        data : function() {
            return {
                dialog: new Tools.Dialog(this),
                currentPage: 1,
                pageSize: 10,
                total: 1,
                departments: [],
                departmentFormVisible: false,
                formLabelWidth: '120px',
                searchForm:{
                    type : '1',
                    name : ''
                },
                searchRules :DepartmentSearchRule,
                rules: DepartmentRule,
                loading:true,
                multipleSelection : [],
                deleteIds : {
                    id : []
                }
            }
        },
        created:function () {

        },
        mounted: function() {
            this.$nextTick(function() {
                this.handleCurrentChange(1);
            })
        },
        methods: {
            openDepartmentSuccess : function () {
                let that = this;
                that.$emit('success',function () {
                    that.handleCurrentChange(1);
                });
            },
            openDepartmentWarning : function () {
                let that = this;
                that.$emit('warning',function () {
                    that.handleCurrentChange(1);
                });
            },
            getMaxPage : function() {
                let that = this;
                axios.get('/department/count')
                        .then(function (response) {
                            that.total = response.data.response.count;
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
            },
            handleSizeChange : function (page) {
                this.pageSize = page;
                this.handleCurrentChange(1);
            },
            handleCurrentChange : function(currentPage) {
                if(this.currentPage !== currentPage){
                    this.currentPage = currentPage;
                }
                let that = this;
                let url = '/department/select?page='+currentPage+'&limit='+that.pageSize;

                if(that.searchForm.name !== ''){
                    url += '&type='+ that.searchForm.type + '&keys=' + that.searchForm.name;
                }

                this.loading = true;
                axios.get(url).then(function (response) {
                    that.loading = false;
                    that.departments = response.data.response.data;

                }).catch(function (error) {
                    that.loading = false;
                });
                this.getMaxPage();
            },
            searchContentClick : function(currentPage) {
                let that = this;
                if(this.currentPage !== currentPage){
                    this.currentPage = currentPage;
                }
                let ext_url = that.searchForm.type + '&keys=' + that.searchForm.name;
                let count_url = '/department/count?type='+ ext_url;
                let list_url = '/department/select?page='+ currentPage+'&limit='+that.pageSize+'&type='+ ext_url;

                that.loading = true;
                axios.get(count_url)
                        .then(function (response) {
                            that.total = response.data.response.count;
                        })
                        .catch(function (error) {
                        });

                axios.get(list_url)
                        .then(function (response) {
                            that.loading = false;
                            that.departments = response.data.response.data;
                        }).catch(function (error) {
                            that.loading = false;
                        });
            },
            addDepartment : function () {
                this.$refs.DepartmentDetail.showDepartment();
            },

            deleteDepartment : function() {
                let ids = this.multipleSelection.map(item => item.id).join();

                if (!ids||ids.length===0) {
                    return this.dialog.openError(null, '請至少選擇一筆資料');
                }
                let that = this;

               axios.delete('/department/delete', {data: JSON.stringify({id: ids})})
                   .then(function (response) {
                       if(response.data.code === 0) {
                            if(response.data.response.row !== undefined && response.data.response.row.length > 0) {
                                that.openDepartmentSuccess();
                            }

                            if (response.data.response.length === 0) {
                                that.openDepartmentWarning();
                            }
                       } else {
                           that.openDepartmentWarning();
                       }
                   });
            },

            handleSelectionChange : function(val) {
                this.multipleSelection = val;
            },

            editDepartment : function(id) {
               this.$refs.DepartmentDetail.editDepartment(id);
            },

            addDepartmentDetail(item) {
                this.total += 1;
                if(Math.ceil(this.departmentGroups.length / this.pageSize) === this.currentPage) {
                    this.departmentGroups.push(item);
                }
            }
        }
    }
</script>

<style scoped>
    .el-checkbox-group{
        font-size: 12px;
    }
</style>