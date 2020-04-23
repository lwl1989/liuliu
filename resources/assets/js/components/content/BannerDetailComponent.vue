<template>
    <div id="app">
        <el-dialog title="置頂公告" :visible.sync="bannerVisible">
            <el-form :model="banner" :rules="rules" ref="banner" v-loading="loading">
                <el-form-item label="名稱" :label-width="formLabelWidth" prop="title">
                    <el-col :span="15">
                        <el-input v-model="banner.title" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="公告圖片" :label-width="formLabelWidth" prop="cover">
                    <img v-if="cover_url !== ''" :src="cover_url" width="320" height="150" />
                    <el-upload
                            action="/content/banner/cover"
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
                <el-form-item label="上架時間" :label-width="formLabelWidth" prop="select_time">
                    <el-date-picker v-model="banner.select_time" type="datetimerange" range-separator="-" start-placeholder="開始時間" end-placeholder="結束時間" >
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="連結網址" :label-width="formLabelWidth" prop="target_url">
                    <el-col :span="15">
                        <el-input v-model="banner.target_url" auto-complete="off"></el-input>
                    </el-col>
                </el-form-item>
                <el-form-item label="最後變動資訊" :label-width="formLabelWidth" v-if="banner.updater !== ''">
                    <el-col :span="15">
                        <el-input :value="banner.update_time+' '+banner.updater" auto-complete="off" disabled style="border: none;"></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="bannerVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitBanner">確 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {AdRule} from '../../tools/element-ui-validate';
    import NewDialog from '../../tools/element-ui-dialog';

    export default {
        name: "banner-detail-component",

        data: function () {
            return {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                formLabelWidth: '120px',
                bannerVisible: false,
                banner: {
                    id: 0,
                    title:'',
                    cover:'',
                    target_url:'',
                    online_time:'',
                    offline_time:'',
                    update_time:'',
                    select_time:'',
                    cover_url:'',
                    updater:''
                },
                cover_url:'',
                covers:[],
                rules: AdRule,
                dialog: NewDialog(this),
                except_width: 1210,
                except_height: 580,
                except_size: 2*1024*1024,
                loading: false
            }
        },

        watch: {
            bannerVisible(current) {
                if (current === false) {
                    this.resetBanner();
                }
            },

            'banner.select_time' : function () {
                this.$nextTick(() => {
                    this.$refs.banner.validateField('select_time');
                });
            }
        },

        methods: {
            resetBanner() {
                this.banner ={
                    id: 0,
                    title:'',
                    cover:'',
                    target_url:'',
                    online_time:'',
                    offline_time:'',
                    update_time:'',
                    select_time:'',
                    updater:''
                };
                this.covers = [];
                this.cover_url = '';
            },

            submitBanner() {
                const h = this.$createElement;

                this.$refs.banner.validate((result) => {
                    if (result) {
                        this.banner.online_time = this.banner.select_time[0].toString();
                        this.banner.offline_time = this.banner.select_time[1].toString();

                        this.dialog.openSelfDialog((callback) => {
                            let url = this.banner.id > 0
                                ? '/content/banner/update?id='+this.banner.id
                                : '/content/banner/create';

                            axios.post(url, this.banner).then((response) => {
                                if (response.data.code > 0) {
                                    this.dialog.openError(null, '儲存失敗');
                                    return;
                                }

                                this.dialog.openSuccess(callback, '儲存成功');

                                this.bannerVisible = false;
                                this.$refs.banner.resetFields();
                                this.$parent.handleBannerCurrentChange(this.$parent.currentPage);
                            }).catch(() => {
                                this.dialog.openError(null, '儲存失敗');
                            });

                        }, h('p', null, [
                            h('span', null, '確定要儲存 '),
                            h('span', {style: 'color: teal'}, this.banner.title),
                            h('span', null, ' 嗎？')
                        ]), '創建中...');
                    }
                });
            },

            handleRemove : function(file) {
                let index = this.banner.image.indexOf(file);
                if(index !== -1) {
                    this.banner.image.splice(index);
                }
            },

            handleError : function(err, file) {
                let index = this.banner.image.indexOf(file);
                if(index !== -1) {
                    this.banner.image.splice(index);
                }
            },

            handleSuccess : function(res) {
                this.banner.cover = res.response.path;
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

            editBanner : function(id) {
                let that = this;
                this.bannerVisible = true;
                if(id > 0) {
                    this.loading = true;
                    axios.get('/content/banner/get?id='+id).then(function (response) {
                            that.loading = false;

                            response.data.response.data.select_time = [
                                new Date(response.data.response.data.online_time),
                                new Date(response.data.response.data.offline_time)
                            ];
                            that.banner = response.data.response.data;
                            that.cover_url = that.banner.cover_url;
                        }).catch(function (error) {
                            that.loading = false;
                            that.$message.error(error.toString());
                        });
                }
            }
        }
    }
</script>

<style scoped>

</style>