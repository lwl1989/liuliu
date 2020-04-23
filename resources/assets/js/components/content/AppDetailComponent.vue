<template>
    <div id="app">
        <el-dialog title="App小舖" :visible.sync="AppVisible">
            <el-form :model="app" :rules="rules" ref="app" v-loading="loading">
                <el-form-item label="APP名稱" :label-width="formLabelWidth" prop="title">
                    <el-col :span="15">
                        <el-input v-model="app.title" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="APP icon" :label-width="formLabelWidth" prop="cover" required>
                    <img v-if="cover_url !== ''" :src="cover_url" width="150" height="150" />
                    <el-upload
                            action="/content/app/icon"
                            :multiple=false
                            :limit=1
                            :headers="headers"
                            accept="image/jpg,image/jpeg,image/png"
                            ref="upload"
                            :on-remove="handleRemove"
                            :file-list="covers"
                            :on-success="handleSuccess"
                            :on-change="handleFileChange"
                            :show-file-list="false"
                            :auto-upload=false>
                        <el-button size="small" type="primary">選取圖片</el-button>
                        <div slot="tip" class="el-upload__tip">
                            圖檔建議尺寸{{except_width}}*{{except_height}}像素，僅限 .jpg .jpeg .png 格式，只能夾帶一個檔案，檔案大小不可超過2M
                        </div>
                    </el-upload>
                </el-form-item>
                <el-form-item label="APP 版本" :label-width="formLabelWidth" prop="platform">
                    <el-col :span="15">
                        <el-radio v-model="app.platform" label="2">iOS</el-radio>
                        <el-radio v-model="app.platform" label="1">Android</el-radio>
                        <el-radio v-model="app.platform" label="3">URL</el-radio>
                    </el-col>
                </el-form-item>
                <el-form-item label="APP ID" :label-width="formLabelWidth" prop="target_url">
                    <el-col :span="15">
                        <el-input v-model="app.target_url" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="APP SCHEME" :label-width="formLabelWidth" prop="scheme_url">
                    <el-col :span="15">
                        <el-input v-model="app.scheme_url" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="最後變動資訊" :label-width="formLabelWidth" v-if="app.updater !== ''">
                    <el-col :span="15">
                        <el-input :value="app.update_time+' '+app.updater" auto-complete="off" disabled style="border: none;"></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="AppVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitApp">確 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {AppRule} from '../../tools/element-ui-validate';
    import NewDialog from '../../tools/element-ui-dialog';

    export default {
        name: "app-detail-component",
        data: function () {
            return {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                formLabelWidth: '120px',
                AppVisible: false,
                app: {
                    id: 0,
                    title:'',
                    cover:'',
                    platform:'1',
                    target_url:'',
                    update_time:'',
                    updater:'',
                    scheme_url : ''
                },
                cover_url:'',
                covers:[],
                rules: AppRule,
                dialog: NewDialog(this),
                except_width: 250,
                except_height: 250,
                except_size: 2*1024*1024,
                loading: false
            }
        },
        watch: {
            AppVisible(current) {
                if (current === false) {
                    this.resetApp();
                }
            },

            'app.cover' : function() {
                this.$nextTick(() => {
                    this.$refs.app.validateField('cover');
                })
            }
        },
        methods: {
            resetApp() {
                this.app ={
                    id: 0,
                    title:'',
                    cover:'',
                    platform:'1',
                    target_url:'',
                    update_time:'',
                    updater:'',
                    scheme_url : ''
                };
                this.covers = [];
                this.cover_url = '';
            },

            copyObj() {
                let data = {};
                let that = this;
                Object.keys(that.app).forEach(function(key){
                    data[key] = that.app[key];
                });
                return data;
            },

            submitApp() {
                const h = this.$createElement;
                let data = this.copyObj();

                this.$refs.app.validate((result) => {
                    if (result) {
                        this.dialog.openSelfDialog((callback) => {
                            let url = this.app.id > 0
                                ? '/content/app/update?id='+this.app.id
                                : '/content/app/create';

                            axios.post(url, data).then((response) => {
                                if (response.data.code === 0) {
                                    this.AppVisible = false;
                                    this.$refs.app.resetFields();
                                    this.dialog.openSuccess(() => {
                                        if(data.id === 0) {
                                            data.id = response.data.response.id;
                                            data.create_time = data.update_time = response.data.response.create_time;
                                            this.$emit('add', data);
                                        }

                                        callback();
                                        window.location.reload();
                                    }, '儲存成功');

                                    return;
                                }

                                this.dialog.openWarning(callback, '儲存失敗');
                            }).catch(() => {
                                this.dialog.openWarning(callback, '儲存失敗');
                            });
                        }, h('p', null, [
                            h('span', null, '確定要新增 '),
                            h('span', {style: 'color: teal'}, this.app.title),
                            h('span', null, ' 嗎？')
                        ]));
                    }
                });
            },

            handleRemove : function(file) {
                let index = this.app.image.indexOf(file);
                if(index !== -1) {
                    this.app.image.splice(index);
                }
            },

            handleError : function(err, file) {
                let index = this.app.image.indexOf(file);
                if(index !== -1) {
                    this.app.image.splice(index);
                }
            },

            handleSuccess : function(res) {
                this.app.cover = res.response.path;
                this.cover_url = res.response.url;
                this.covers = [];
            },

            createReader : function(file, error, success) {
                let reader = new FileReader;
                let that = this;
                reader.onload = function (evt) {
                    let image = new Image();
                    image.onload = function () {
                        let imageType = ['image/jpeg', 'image/png', 'image/jpg'];
                        if (imageType.indexOf(file.type) < 0) {
                            error('上傳的文件是不正確的文件類型[image/jpg|image/jpeg|image/png]');
                            return;
                        }

                        if (file.size > that.except_size) {
                            error('上傳的圖片大小不能超過 2M!');
                            return;
                        }

                        success();
                    };
                    image.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            },

            handleFileChange : function(file, files) {
                if (!("checked" in file)) {
                    let that = this;
                    this.createReader(file.raw, function (message) {
                        that.$message.error(message);
                        files.splice(-1);
                    }, function () {
                        file.checked = true;
                        that.$refs.upload.submit();
                    });
                }
            },

            editApp : function(id) {
                let that = this;
                this.AppVisible = true;
                if(id > 0) {
                    this.loading = true;
                    axios.get('/content/app/get?id=' + id).then(function (response) {
                            that.loading = false;

                            that.app = response.data.response.data;
                            that.cover_url = that.app.cover_url;
                            that.app.platform = that.app.platform.toString();
                        }).catch(function () {
                            that.loading = false;
                        });
                }
            }
        }
    }
</script>

<style scoped>

</style>