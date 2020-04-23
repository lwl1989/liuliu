<template>
    <div id="app">
        <div class="page">
            <div class="main_box">
                <div class="header">
                    <div class="h_word">臺東縣政府</div>
                </div>
                <div class="header_line"></div>

                <el-form v-loading="loading" :model="loginForm" :rules="rules" ref="loginForm" label-position="left" class="login_box">
                    <el-form-item prop="account">
                        <el-input type="text" @keyup.enter.native='login' auto-complete="off" placeholder="帳號"
                                  v-model="loginForm.account"></el-input>
                    </el-form-item>
                    <el-form-item prop="checkPass">
                        <el-input type="password" @keyup.enter.native='login' prop='password' auto-complete="off" placeholder="密碼"
                                  v-model="loginForm.password"></el-input>
                    </el-form-item>
                    <el-form-item prop="verify" v-if="src !== ''">
                        <el-row>
                            <el-col :span="16">
                                <el-input type="text" @keyup.enter.native='login' auto-complete="off" placeholder="驗證碼"
                                          v-model="loginForm.verify"></el-input>
                            </el-col>
                            <el-col :span="6">
                                <img :src="src" @click="refreshVerify()" height="40" width="100">
                            </el-col>
                        </el-row>
                    </el-form-item>
                    <el-form-item style="width:100%;" v-if="login_before">
                        <el-button type="primary" style="width:100%;" @click="login">登入</el-button>
                    </el-form-item>
                    <el-form-item style="width:100%;" v-else>
                        <el-button type="info" style="width:100%;" disabled>登入中</el-button>
                    </el-form-item>
                    <div style="width:100%; margin-left: 15%; font-size: large" >
                        請使用Chrome瀏覽器登入
                    </div>
                </el-form>
            </div>

            <footer class="footer">
                <div class="contact">如果您有任何操作問題　請撥打資訊發展科：089-340785</div>
                <div class="version">Version：V20180227-2</div>
            </footer>
        </div>
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools';

    export default {
        name: "login-component",
        data() {
            return {
                loginForm: {
                    account: "",
                    password: "",
                    verify: '',
                    device_uuid: ''
                },
                rules: {
                    account: [{required: true, message: '帳號不能爲空'}, {min: 6, max: 30, message: '長度需要在 6 到 30 個字符'}], //, trigger: 'blur'
                    password: [{required: true, message: '密碼不能爲空'}, {min: 6, max: 30, message: '長度需要在 6 到 30 個字符'}],
                    verify: [{required: true, message: '驗證碼不能為空'}],
                },
                src: '',
                loading: true,
                login_before: true
            }
        },
        mounted: function () {
            this.$nextTick(function () {
                this.refreshVerify();
            })
        },
        methods: {
            refreshVerify() {
                axios.get('/login/verify').then((response) => {
                    this.src = 'data:image/png;base64,'+response.data.response.data.content;
                    this.loginForm.device_uuid = response.data.response.data.device_uuid;
                    this.loading = false;
                }).catch((error) => {
                    Tools.Dialog(this).openError(null, error.toString());
                });
            },

            login() {
                this.$refs.loginForm.validate((result) => {
                    if (result) {
                        this.login_before = false;
                        axios.post('/login', this.loginForm).then((response) => {
                            if (response.data.code === 0) {
                                this.$refs.loginForm.resetFields();

                                Tools.Dialog(this).openSuccess(() => {
                                    sessionStorage.setItem('router', JSON.stringify(response.data.response.router));
                                    sessionStorage.setItem('google_map_key', JSON.stringify(response.data.response.google_map_key));

                                    window.location.href = '/';
                                }, '登入成功');
                            } else {
                                if (response.data.code === 500332) {
                                    Tools.Dialog(this).openError(null, '驗證碼錯誤');
                                } else if (response.data.code === 500336 || response.data.code === 500334) {
                                    Tools.Dialog(this).openError(() => {
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 2000);
                                    }, '您的帳號已被停用，如有問題請洽資訊發展科');
                                } else if(response.data.code === 500335) {
                                    Tools.Dialog(this).openError(() => {
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 2000);
                                    }, '帳號不存在');
                                } else {
                                    Tools.Dialog(this).openError(() => {
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 2000);
                                    }, '帳號不存在或密碼輸入錯誤');
                                }

                                this.loginForm.verify = '';
                                this.login_before = true;
                                this.refreshVerify();
                            }
                        }).catch((error) => {
                            this.login_before = true;
                            this.refreshVerify();
                            Tools.Dialog(this).openError(null, error.toString());
                        });
                    }
                });
            },
        }
    }
</script>

<style scoped>
    body {
        margin: 0px;
        padding: 0px;
        background: #1F2D3D;
    }

    .login-container {
        box-shadow: 0 0px 8px 0 rgba(0, 0, 0, 0.06), 0 1px 0px 0 rgba(0, 0, 0, 0.02);
        -webkit-border-radius: 5px;
        border-radius: 5px;
        -moz-border-radius: 5px;
        background-clip: padding-box;
        /**margin: 180px auto;
        width: 350px; **/
        padding: 35px 35px 15px 35px;
        background: #fff;
        border: 1px solid #eaeaea;
        box-shadow: 0 0 2px #cac6c6;
    }

    .title {
        margin: 0px auto 40px auto;
        text-align: center;
        color: #505458;
    }
</style>