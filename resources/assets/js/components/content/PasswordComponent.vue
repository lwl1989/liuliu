<template>
    <div id="app" v-loading="loading > 0" style="margin:20px 0 0;">
        <el-form :model="passwordForm" ref="passwordForm" label-width="150px" size="medium">
            <el-form-item label="國碼" prop="code">
                <el-select v-model="passwordForm.code">
                    <el-option
                        v-for="(item,index) in countryCode"
                        :label="item.zh_name+' +'+item.code"
                        :value="item.id"
                        :key="index"
                    ></el-option>
                </el-select>
            </el-form-item>

            <el-form-item prop="mobile" label="手機號碼" style="width: 400px;">
                <el-input v-model="passwordForm.mobile"></el-input>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="doSearch">查詢</el-button>
            </el-form-item>

            <el-form-item prop="mobile" label="查詢結果">
               {{verify_code}}
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    import NewDialog from '../../tools/element-ui-dialog'

    export default {
        data : function () {
            return {
                permissions : [],
                passwordForm : {
                    mobile : '',
                    code : 2,
                    device_uuid : 'backstage',
                    is_search : 1,
                },
                notice : NewDialog(this),
                countryCode : [],
                loading : 1,
                verify_code : '',
                code_origin : 2
            }
        },

        watch : {
            'passwordForm.code' : function (val) {
                this.passwordForm.code = val;
            },
            
            'passwordForm.mobile' : function (val) {
                if (val && !val.match(/^[0-9]{1,20}$/)) {
                    NewDialog(this).openWarning(null, '手機號碼格式有誤');
                    return;
                }
            }
        },

        created : function () {
            this.getCountryCode();
        },

        methods : {
            getCountryCode() {
                axios.get('/country/code').then((response) => {
                    this.countryCode = response.data.response;
                    this.loading = 0;
                }).catch(error => {
                    NewDialog(this).openError(null, error.toString())
                })
            },

            doSearch() {
                this.loading = 1;
                this.$refs.passwordForm.validate((result) => {
                    if (result) {
                        if (this.passwordForm.mobile === '') {
                            NewDialog(this).openWarning(null, '請輸入手機號碼');
                            this.loading = 0;
                            return;
                        }

                        if (this.passwordForm.mobile && !this.passwordForm.mobile.match(/^[0-9]{1,20}$/)) {
                            NewDialog(this).openWarning(null, '手機號碼格式有誤');
                            this.loading = 0;
                            return;
                        }

                        if (this.passwordForm.mobile.length <= 1 || parseInt(this.passwordForm.mobile) === 0) {
                            NewDialog(this).openWarning(null, '手機號碼格式有誤');
                            this.loading = 0;
                            return;
                        }

                        this.code_origin = this.passwordForm.code;

                        axios.get('/turn/country/code/' + this.passwordForm.code).then((response) => {
                            this.passwordForm.code = response.data.code;
                            axios.post('/api/client/send', this.passwordForm).then((res) => {
                                this.passwordForm.code = this.code_origin;
                                this.loading = 0;
                                if (res.data.response.verify_code.toString() !== '無發送驗證碼') {
                                    this.verify_code = res.data.response.verify_code;
                                }

                                if (res.data.response.verify_code.toString() === '無發送驗證碼' || res.data.response.verify_code === '') {
                                    NewDialog(this).openWarning(null, '無發送驗證碼');
                                    this.verify_code = '';
                                    return;
                                }
                            }).catch(() => {
                                NewDialog(this).openWarning(null, '驗證碼查詢失敗');
                            });
                        });
                    }
                });
            }
        }
    }
</script>