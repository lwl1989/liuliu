<template>
    <div id="app" v-loading="loading > 0" style="margin:20px 0 0;">
        <el-form :model="adminForm" :rules="rules" ref="adminForm" label-width="150px" size="medium">
            <el-form-item>
                <el-button plain type='warning' @click="closeAd">取 消</el-button>
                <el-button plain type="primary" @click="submitAdmin">確 定</el-button>
                <el-button plain v-if='id > 0' type="primary" @click="resetShopPassword">重設密碼</el-button>
            </el-form-item>

            <el-form-item label="帳號" prop="account" >
                <el-input v-model="adminForm.account" max="30" :disabled="disabled"></el-input>
            </el-form-item>

            <el-form-item v-if='id <= 0' label="密碼" prop="password">
                <el-input v-model="adminForm.password" max="30" :disabled="disabled"></el-input>
            </el-form-item>

            <el-form-item label="狀態" prop="status">
                <el-select v-model="adminForm.status" placeholder="啓用">
                    <el-option label="啓用" value="1"></el-option>
                    <el-option label="停用" value="2"></el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="角色" prop="role">
                <el-select v-model="adminForm.role" placeholder="縣府員工">
                    <el-option label="縣府員工" value="2"></el-option>
                    <el-option v-if='loginRole != 2' label="管理員" value="3"></el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="姓名" prop="name">
                <el-input v-model="adminForm.name"></el-input>
            </el-form-item>

            <el-form-item label="別名" prop="alias">
                <el-input v-model="adminForm.alias"></el-input>
            </el-form-item>

            <el-form-item label="業務單位" prop="department_id" v-if="adminForm.role == 2">
                <el-select  v-model="adminForm.department_id" placeholder="請選擇">
                    <el-option
                            v-for="department in departments"
                            :key="department.id"
                            :label="department.name"
                            :value="department.id">
                    </el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="電話">
                <el-col :span="6">
                    <el-form-item prop="tel">
                        <el-input v-model="adminForm.tel"></el-input>
                    </el-form-item>
                </el-col>
                <el-col class="line" :span="2" :offset="1">分機</el-col>
                <el-col :span="6">
                    <el-form-item prop="tel_ext">
                        <el-input v-model="adminForm.tel_ext"></el-input>
                    </el-form-item>
                </el-col>
            </el-form-item>

            <el-form-item label="國碼" prop="code">
                <el-select v-model="adminForm.code">
                    <el-option
                            v-for="(item,index) in countryCode"
                            :label="item.zh_name+' +'+item.code"
                            :value="item.id"
                            :key="index"
                    ></el-option>
                </el-select>
            </el-form-item>

            <el-form-item prop="mobile" label="手機號碼">
                <el-input v-model="adminForm.mobile"></el-input>
            </el-form-item>

            <el-form-item label="電子郵件" prop="email">
                <el-input v-model="adminForm.email"></el-input>
            </el-form-item>

            <el-form-item label="可使用功能" prop="permissions">
                <el-checkbox-group v-model="adminForm.permissions">
                    <template v-for="(item,index) in permissions">
                        <el-row>
                            {{index+1}}. {{item.name}}
                        </el-row>
                        <el-row>
                            <el-col v-for="perm in item.actions" :key="perm.en" :span="6">
                                <el-checkbox :label="perm.en">{{perm.name}}</el-checkbox>
                            </el-col>
                        </el-row>
                    </template>
                </el-checkbox-group>
            </el-form-item>
        </el-form>

        <change-admin-password ref="ChangeAdminPassword"></change-admin-password>
    </div>
</template>

<script>
    import {AdminRule} from '../../tools/element-ui-validate'
    import NewDialog from '../../tools/element-ui-dialog'
    import ChangeAdminPassword from './ChangeAdminPasswordComponent'

    export default {
        components: {ChangeAdminPassword},

        data: function () {
            return {
                disabled:false,
                allPerm :{},
                id: this.$route.params.id,
                rules: AdminRule,
                permissions: [],
                adminForm : {
                    account:'',
                    password:'',
                    role: '2',
                    status: '1',
                    name: '',
                    alias:'',
                    department_id : '',
                    tel:'',
                    tel_ext:'',
                    mobile:'',
                    email:'',
                    permissions: [],
                    code:2
                },
                updateBeforeAccount : '',
                defaultDepartment:'0',
                notice:NewDialog(this),
                departments:[],
                countryCode:[],
                loading: 0,
                loginRole:0,
            }
        },

        computed: {
            roleChange() {
                return this.adminForm.role;
            }
        },

        watch:{
            roleChange(current) {
               this.changePerm(current);
            },
            'adminForm.code' : function (val) {
                this.adminForm.code = val;
            }
        },

        created: function () {
            this.getCountryCode();
            this.getAllPerm();
            this.getDepartment();
            if (parseInt(this.id) > 0) {
                this.disabled = true;
                this.getUser();
            }
            this.getLoginRole();
        },

        methods : {
            resetShopPassword(){
                this.$refs.ChangeAdminPassword.doOpenPasswordPage(this.id);
            },

            changePerm(current) {
                let role = current.toString();
                this.permissions = this.allPerm[role];
            },

            getLoginRole() {
                axios.get('/admin/info').then((response) => {
                    this.loginRole = response.data.response.role;
                }).catch(error => {

                })
            },

            getCountryCode() {
                axios.get('/country/code').then((response) => {
                    this.countryCode = response.data.response;
                }).catch(error => {
                    NewDialog(this).openError(null, error.toString())
                })
            },

            getDepartment() {
                this.loading++;
                axios.get('/department/select?limit=1000000&field=id,name').then((response) => {
                    this.loading--;
                    this.departments = response.data.response.data;
                });
            },

            getAllPerm() {
                this.loading++;
                axios.get('/admin/perm').then((response) => {
                    this.allPerm = response.data.response;
                    this.changePerm(this.adminForm.role);
                    this.loading--;
                });
            },

            getUser() {
                this.loading++;
                axios.get('/admin/get?id='+this.id).then((response) => {
                    this.loading--;

                    this.adminForm = response.data.response.data;
                    this.updateBeforeAccount = this.adminForm.account;

                    this.adminForm.role = this.adminForm.role.toString();
                    this.adminForm.status = this.adminForm.status.toString();
                    this.adminForm.deparment_id = this.adminForm.deparment_id.toString();
                    this.old_department_id = this.adminForm.deparment_id.toString();
                    this.adminForm.code = this.adminForm.code.toString();
                }).catch(() => {
                    this.loading--;
                });
            },

            submitAdmin() {
                this.$refs.adminForm.validate((result) => {
                    if (result) {
                        axios.get('/admin/check/username?u='+this.adminForm.account).then((res) => {
                            if ((parseInt(this.id) === 0 || this.updateBeforeAccount !== this.adminForm.account) &&
                                res.data.response.has
                            ) {
                                NewDialog(this).openWarning(null, '帳號重複');
                            } else {
                                if (this.adminForm.role.toString() === '3') {
                                    this.adminForm.deparment_id = '0';
                                }

                                let tipMsg = '確定要儲存帳號嗎？';
                                if (this.adminForm.permissions.length === 0) {
                                    tipMsg = "尚未勾選可使用功能，是否要儲存？";
                                }

                                this.notice.openSelfDialog((callback) => {
                                    let url = this.id > 0 ? '/admin/update' : '/admin/create';
                                    axios.post(url, this.adminForm).then((response) => {
                                        callback();

                                        if (response.data.code === -200) {
                                            return NewDialog(this).openError(null, '該手機號碼已被其他帳號綁定，請重新輸入')
                                        }
                                        


                                        if(response.data.code === 0) {
                                            this.openADDialog('success', () => {
                                                setTimeout(()=> {
                                                    this.closeAdminDetailWindowFuc();
                                                },3000)

                                            }, '儲存成功');
                                        }else{
                                            return this.openADWarning(callback);
                                        }

                                    }).catch((error) => {
                                        this.openADWarning(error.toString());
                                    });
                                }, tipMsg);
                            }
                        });
                    }
                });
            },

            closeAdminDetailWindowFuc(){
                let userAgent = navigator.userAgent;

                if (userAgent.indexOf("Firefox") !== -1 || userAgent.indexOf("Chrome") !== -1) {
                    window.opener.location.reload(true);
                    window.location.href="about:blank";
                    window.close();
                } else {
                    window.opener.location.reload(true);
                    window.opener = null;
                    window.open("", "_self");
                    window.close();
                }
            },

            closeAd() {
                this.notice.openSelfDialog((callback)=>{
                    this.closeAdminDetailWindowFuc();
                    callback();
                }, "資料尚未儲存，是否要關閉本頁面？");
            },

            openADWarning(callback,message){
                if(typeof(message) === 'undefined') {
                    message = '操作失敗，請檢查'
                }
                this.openADDialog('warning',callback,message);
            },

            openADDialog(type,callback,message) {
                this.$message({
                    type: type,
                    message: message
                });
                if(typeof(callback) === 'function') {
                    callback();
                }
            },
        }
    }
</script>

<style scoped>
    .el-checkbox-group{
        font-size: 12px;
    }
    .el-form{
         width: 80%;
         margin: 0 auto;
    }
</style>