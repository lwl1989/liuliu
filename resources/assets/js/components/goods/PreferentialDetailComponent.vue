<template>
    <div id="app">
        <el-dialog :title="dialogTitle" :visible.sync="preferVisible" :close-on-click-modal="false" >
            <el-form :model="prefer" :rules="rules" ref="prefers" v-loading="loading">
                <el-row justify="space-between">
                    <el-col :span="16">
                        <el-form-item label="景點圖片" prop="cover" required :label-width="formLabelWidth">
                            <el-upload
                                    action="/preferential/cover"
                                    :multiple=false
                                    :limit=10
                                    :headers="headers"
                                    accept="'image/jpeg','image/png'"
                                    ref="upload"
                                    :on-remove="handleRemove"
                                    :file-list="prefer.cover"
                                    :on-success="handleSuccess"
                                    :on-change="handleFileChange"
                                    :auto-upload=false
                                    :on-exceed="handleLimit"
                                    list-type="picture"
                                    :disabled="isUploading">
                                <el-button type="primary" >選取圖片</el-button>
                                <div slot="tip" class="el-upload__tip">
                                    圖檔限制尺寸必須為800像素*500像素,僅限jpg.jpeg.png格式，最多上傳5張，點擊X可移除圖片
                                </div>
                            </el-upload>
                        </el-form-item>

                        <el-form-item label="優惠內容" :label-width="formLabelWidth" prop="title">
                            <el-col :span="15">
                                <el-input v-model="prefer.title" auto-complete="off"></el-input>
                            </el-col>
                        </el-form-item>

                        <el-form-item label="備註內容" :label-width="formLabelWidth" prop="desc">
                            <el-col :span="25">
                                <el-upload
                                        class="avatar-uploader"
                                        action="/preferential/content"
                                        name="file"
                                        :headers="headers"
                                        :show-file-list="false"
                                        :on-success="uploadSuccess"
                                        :on-error="uploadError"
                                        :before-upload="beforeUpload">
                                </el-upload>

                                <input type="file" id="quill-upload" style="display:none" accept="image/png,image/jpeg,image/gif,image/jpg"/>
                                <quill-editor
                                        class="editor"
                                        v-model="prefer.desc"
                                        ref="myQuillEditor"
                                        :options="editorOption"
                                        @focus="onEditorFocus($event)"
                                        @change="onEditorChange($event)">
                                </quill-editor>
                            </el-col>
                        </el-form-item>
                        <el-form-item label="最後變動資訊" :label-width="formLabelWidth" v-if="prefer.updater !== ''">
                            <el-col :span="15">
                                <el-input :value="prefer.update_time+' '+prefer.updater" auto-complete="off" disabled
                                          style="border: none;"></el-input>
                            </el-col>
                        </el-form-item>
                    </el-col>
                    <el-col :offset='1' :span="7" v-if="type==='edit'">
                        <div style="margin:0 0 0 0;text-align:center">
                            <img width="100%" :src="prefer.qrUrl"/>
                            優惠資訊QR Code
                            <el-button type="primary" size="mini" @click="downloadQr">下載</el-button>
                        </div>
                    </el-col>
                </el-row>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="preferVisible = false">取 消</el-button>
                <el-button type="primary" @click="submitPrefer">儲 存</el-button>
                <el-button v-if="type==='edit' && showDown === false" type="primary" @click="download">下 載</el-button>
                <el-button v-if="type==='edit'" type="primary" @click="changeState">{{statusTip}}</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {PerferentialRule} from '../../tools/element-ui-validate';
    import NewDialog from '../../tools/element-ui-dialog';
    import Tools from "../../tools/vue-common-tools";

    import { quillEditor } from "vue-quill-editor"; //调用编辑器
    import 'quill/dist/quill.core.css';
    import 'quill/dist/quill.snow.css';
    import 'quill/dist/quill.bubble.css';

    const toolbarOptions = [
        ['bold', 'italic', 'underline'],//, 'strike'
        //['blockquote', 'code-block'],

        [{'header': 1}],//, {'header': 2}
        [{'list': 'ordered'}, {'list': 'bullet'}],
        //[{'script': 'sub'}, {'script': 'super'}],
        //[{'indent': '-1'}, {'indent': '+1'}],
        //[{'direction': 'rtl'}],

        [{'size': ['small', false, 'large', 'huge']}],
        //[{'header': [1, 2, 3, 4, 5, 6, false]}],

        [{'color': []}, {'background': []}],
        //[{'font': []}],
        //[{'align': []}],
        ['link', 'image', ],//'video'
        //['clean']
    ];

    export default {
        name: "preferential-detail-component",
        component : {
            quillEditor,
        },

        data: function () {
            return {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                formLabelWidth: '120px',
                preferVisible: false,
                prefer: {
                    id: 0,
                    title: '',
                    desc: '',
                    status: 1,
                    update_time: '',
                    updater: '',
                    cover : []
                },
                type: 'add',
                dialogTitle: '新增優惠',
                loading: false,
                rules: PerferentialRule,
                dialog: NewDialog(this),
                isUploading : false,
                editorOption: {
                    placeholder: '',
                    theme: 'snow',
                    modules: {
                        toolbar: {
                            container: toolbarOptions,  // 工具栏
                            handlers: {
                                'image': function (value) {
                                    if (value) {
                                        document.querySelector('.avatar-uploader input').click();
                                    } else {
                                        this.quill.format('image', false);
                                    }
                                }
                            }
                        }
                    }
                },
                addRange: [],
                uploadData : {},
                photoUrl : '',      // 上传图片地址
                uploadType : 'image',  // 上传的文件类型（图片、视频）
                fullscreenLoading : false,
                quillUpdateImg: false,
                showDown : true
            }
        },

        watch: {
            preferVisible(current) {
                if (current === false) {
                    this.resetPrefer();
                }
            }
        },
        computed: {
            statusTip() {
                if (this.prefer.status === 1) {
                    this.showDown = false;
                    return '停 用';
                } else {
                    this.showDown = true;
                    return '啓 用'
                }
            }
        },
        methods: {
            // 富文本图片上传前
            beforeUpload() {
                // 显示loading动画
                this.quillUpdateImg = true
            },

            // 富文本上传图片成功
            uploadSuccess(res, file) {
                console.log(res);
                // res为图片服务器返回的数据
                // 获取富文本组件实例
                let quill = this.$refs.myQuillEditor.quill;
                // 如果上传成功
                if (res.code == 0) {
                    // 获取光标所在位置
                    let length = quill.getSelection().index;

                    if (res.response.type === 'video/mp4') {
                        this.uploadType = 'video';
                    }

                    // 插入图片  res.info为服务器返回的图片地址
                    quill.insertEmbed(length, this.uploadType, res.response.url);
                    // 调整光标到最后
                    quill.setSelection(length + 1);
                } else {
                    this.$message.error('文件插入失败')
                }
                // loading动画消失
                this.quillUpdateImg = false
            },

            // 富文本图片上传失败
            uploadError() {
                // loading动画消失
                this.quillUpdateImg = false
                this.$message.error('文件插入失败')
            },

            // 获得焦点事件
            onEditorFocus(editor) {
                console.log('editor focus!', editor)
            },

            onEditorReady(editor) {
                console.log('editor ready!', editor)
            },

            // 内容改变事件
            onEditorChange({ editor, html, text }) {

            },

            resetPrefer() {
                this.prefer = {
                    id: 0,
                    title: '',
                    desc: '',
                    time: '',
                    status: 1,
                    updater: '',
                    cover : []
                };
                this.type= 'add';
                this.dialogTitle= '新增優惠';
                this.loading= false
            },

            submitPrefer() {
                const h = this.$createElement;
                let data = this.copyObj();

                this.$refs.prefers.validate((result) => {
                    if (result) {
                        this.dialog.openSelfDialog((callback) => {
                            let url = this.prefer.id > 0
                                ? '/preferential/update?id=' + this.prefer.id
                                : '/preferential/create';

                            axios.post(url, data).then((response) => {
                                if (response.data.code === 0) {
                                    this.preferVisible = false;
                                    this.$refs.prefers.resetFields();
                                    this.dialog.openSuccess(() => {
                                        if (data.id === 0) {
                                            data.id = response.data.id;
                                            data.create_time = response.data.create_time;
                                            data.update_time = response.data.update_time;
                                            data.updater = response.data.updater;
                                            data.status = '1';
                                            this.$emit('add', data);
                                        }

                                        callback();
                                        window.location.reload();
                                    }, '儲存成功');

                                    return;
                                }

                                this.dialog.openWarning(callback, '儲存失敗');
                            }).catch((err) => {
                                console.log(err);
                                this.dialog.openWarning(callback, '儲存失敗');
                            });
                        }, h('p', null, [
                            h('span', null, '確定要新增 '),
                            h('span', {style: 'color: teal'}, this.prefer.title),
                            h('span', null, ' 嗎？')
                        ]));
                    }
                });
            },
            downloadQr() {
                window.location.href = '/qr/preferential/' + this.prefer.id + '?type=down&preferential_name=' + this.prefer.title;
            },
            download() {
                window.location.href = '/preferential/download?id=' + this.prefer.id + '&title=' + this.prefer.title;// + '&desc=' + this.prefer.desc
            },
            changeState() {
                let url = '/preferential/pause';
                let msg = '確定停用？';
                if (this.prefer.status < 1) {
                    url = '/preferential/resume';
                    msg = '確定啓用？'
                }

                this.dialog.openSelfDialog((callback) => {
                    axios.post(url, {id: this.prefer.id}).then((response) => {
                        if (parseInt(response.data.code) === 0) {
                            this.dialog.openSuccess(() => {
                                callback();
                                this.$emit('edit');
                            });
                            this.preferVisible = false;
                            return;
                        }

                        this.dialog.openWarning(() => {
                            callback();
                        });
                    }).catch((error) => {
                        this.dialog.openWarning(() => {
                            callback();
                        }, error.toString());
                    });
                }, msg);
            },
            editPrefer: function (id) {
                let that = this;
                this.preferVisible = true;
                if (id > 0) {
                    this.type = 'edit';
                    this.loading = true;
                    axios.get('/preferential/get?id=' + id).then(function (response) {
                        that.loading = false;
                        that.prefer = response.data.response.data;
                        if (this.prefer.status === 1) {
                            that.dialogTitle = '編輯優惠(使用中)';
                        } else {
                            that.dialogTitle = '編輯優惠(停用)';
                        }
                    }).catch(function () {
                        that.loading = false;
                    });
                }else {
                    this.type='add'
                }
            },

            copyObj() {
                let data = {};
                let that = this;
                Object.keys(that.prefer).forEach(function (key) {
                    data[key] = that.prefer[key];
                });
                return data;
            },

            handleSuccess(res) {
                this.isUploading = false
                this.prefer.cover.push(res.response);
            },

            handleLimit() {
                Tools.Dialog(this).openWarning(null, '圖片最多可上傳10張');
            },

            handleRemove(file) {
                let index = this.prefer.cover.indexOf(file);
                if(index !== -1) {
                    this.prefer.cover.splice(index, 1)
                }
            },

            handleError(err, file) {
                this.isUploading = false
                let index = this.prefer.cover.indexOf(file);
                if(index !== -1) {
                    this.prefer.cover.splice(index, 1)
                }
            },

            handleFileChange(file, files) {
                if (!("checked" in file)) {
                    let that = this;
                    this.createReader(file.raw, function (message) {
                        that.$message.error(message);
                        files.splice(-1);
                    }, function () {
                        that.isUploading = true
                        file.checked = true;
                        that.$refs.upload.submit();
                    });
                }
            },

            createReader(file, error, success) {
                let reader = new FileReader;
                reader.onload = function (evt) {
                    let image = new Image();
                    image.onload = function () {
                        let imageType = ['image/jpeg', 'image/png'];
                        if (imageType.indexOf(file.type) < 0) {
                            error('圖片格式不符，請重新選擇');
                            return;
                        }

                        if (file.size / 1024 / 1024 > 2) {
                            error('上傳的圖片大小不能超過 2MB!');
                            return;
                        }

                        success();
                    };
                    image.src = evt.target.result;
                };
                reader.readAsDataURL(file);
            },
        }
    }
</script>

<style scoped>
    .el-form-item .el-col {
        width: 100%
    }

</style>

<style>
    .quill-editor,
    .quill-code {
        width: 100%;
        float: left;
    }
</style>