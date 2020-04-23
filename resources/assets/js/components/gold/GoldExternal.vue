<template>
    <div id="app" style="margin-top: 20px;">
        <el-row>
                <el-button size='small' type="primary" icon="el-icon-circle-plus" @click="onAdd">新增</el-button>
        </el-row>

        <el-row>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="external" stripe style="width:100%" v-loading="loading">
                    <el-table-column type="index" label="項次">
                        <template slot-scope="scope">{{ scope.$index+1 }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="外部應用程式">
                        <template slot-scope="scope" >
                            <div v-if="scope.row.id === -1 || scope.row.edit !== 0">
                                <el-input size="small" v-model="scope.row.open_name" placeholder="1-10 個中文、英文、數字、符號" ></el-input>
                            </div>
                            <div v-else>
                                {{ scope.row.open_name }}
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column width='130' prop="open_id" label="open_id"></el-table-column>
                    <el-table-column width='150' prop="secure_iv" label="secure_iv"></el-table-column>
                    <el-table-column prop="secure_key" label="secure_key"></el-table-column>
                    <el-table-column width='200' label="操作">
                        <template slot-scope="scope">
                            <template v-if="scope.row.id === -1 || scope.row.edit > 0">
                                <el-button type="success" size="mini" @click="onSave(scope.$index)">儲存</el-button>
                                <el-button type="warning" size="mini" @click="onDel(scope.$index)">取消</el-button>
                            </template>

                            <template v-else-if="scope.row.id > 0">
                                <el-button type="primary" size="mini" @click="onEdit(scope.$index)">編輯</el-button>
                                <el-button type="danger" size="mini" @click="onDel(scope.$index)">刪除</el-button>
                            </template>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import Tools from '../../tools/vue-common-tools';

    export default {
        data: function () {
            return {
                external:[],
                loading : true,
                isSubmit: false,
            }
        },

        created() {
            this.$nextTick(() => {
                this.loadData();
            })
        },

        methods: {
            loadData() {
                axios.get('/gold/external/select').then((res) => {
                    this.loading = false;
                    if (res.data.code > 0) {
                        return Tools.Dialog(this).openWarning(null, '加載失敗...');
                    }

                    this.external = res.data.response;
                }).catch(error => {
                    this.loading = false;
                    Tools.Dialog(this).openWarning(null, error.toString());
                });
            },

            onAdd() {
                this.external.splice(0, 0, {
                    id: -1,
                    open_name: '',
                    open_id: '',
                    secure_iv: '',
                    secure_key: '',
                    edit: 0 //0正常 1編輯 2提交中
                });
            },

            onSave(index) {
                if (!this.external[index].open_name) {
                    return Tools.Dialog(this).openError(null, '外部應用程式為必填欄位');
                }

                if (this.external[index].open_name.length > 50) {
                    return Tools.Dialog(this).openError(null, '只能是1-50個中文、英文、數字、符號');
                }

                if (this.external[index].edit === 2) {
                    return;
                }

                let url = '/gold/external/update';
                if (this.external[index].id === -1) {
                    url = '/gold/external/create';
                }

                this.external[index].edit = 2;
                axios.post(url, this.external[index]).then((res) => {
                    this.external[index].edit = 1;
                    if(res.data.code > 0 || res.data.response > 0) {
                        return Tools.Dialog(this).openError(null, '儲存失敗');
                    }

                    this.external[index].edit = 0;
                    Tools.Dialog(this).openSuccess(() => {
                        if (this.external[index].id === -1) {
                            this.external[index] = res.data.response;

                            this.external.splice(this.external.length - 1, 0, this.external[index]);
                            this.external.splice(index, 1);

                            this.loadData();
                        }
                    }, '儲存成功');
                }).catch((error) => {
                    Tools.Dialog(this).openError(null, error.toString());
                });
            },

            onEdit(index){
                this.external[index].edit = 1;
            },

            onDel(index) {
                if (this.external[index].id === -1) {
                    this.external.splice(index, 1);
                } else {
                    if (this.external[index].edit !== 0) { //編輯中、提交中不給刪除
                        this.external[index].edit = 0;
                        return;
                    }

                    Tools.Dialog(this).openSelfDialog((callback) => {
                        axios.delete('/gold/external/delete', {data:{ids:[this.external[index].id]}}).then((res) =>{
                            if (res.data.code > 0) {
                                return Tools.Dialog(this).openError(null, '操作失敗');
                            }

                            callback();
                            Tools.Dialog(this).openSuccess(null, '操作成功');
                            this.loadData();
                        }).catch((error)=> {
                            Tools.Dialog(this).openWarning(null, error.toString());
                        });
                    },'確認刪除?刪除後該應用程式將無法再調用TTpush的金幣。');
                }
            }
        }
    }
</script>