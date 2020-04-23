<template>
    <div id="app" style="padding:20px;">
        <el-row>
            <el-card class="box-card">
                <div style="display: inline; float: left;">
                    <div style="font-size:28px;font-weight:bold"><span style="font-weight:bold">{{activity_name}}</span></div>
                </div>
            </el-card>

            <el-col style="margin-top:20px;">
                <el-row>
                    <el-button type="primary" size='small' @click="printPage">列印</el-button>
                    <el-button type="primary" size='small' @click="downPage">下載</el-button>
                    <el-button type="primary" size='small' v-if="loading">下載中...</el-button>
                    <el-button type="primary" size='small' v-else @click="downZip">下載附件</el-button>
                </el-row>
            </el-col>

            <el-col style="margin-top:20px;" :label-width="formLabelWidth">
                <el-table :data="lists" stripe>
                    <el-table-column prop="mobile" label="手機號碼"></el-table-column>
                    <el-table-column prop="name" label="姓名"></el-table-column>
                    <el-table-column prop="sex" label="性別"></el-table-column>
                    <el-table-column prop="offline_create_time" label="是否已報到"></el-table-column>
                </el-table>
            </el-col>
            <el-col style="margin-top:20px;" :label-width="formLabelWidth" v-if="answers.length > 0">
                <div style="margin-left:5px; padding:20px; color: #409EFF">
                    活動報名表
                </div>
                <template  v-for="(item, index) in answers">
                    <div v-if="item.type === 1 || item.type === 2" style="margin-left:10px; padding:10px; ">
                        {{index + 1}}. {{item.title}}
                    </div>
                    <div v-if="item.type === 1 || item.type === 2" style="margin-left:30px; padding:10px; ">
                        {{item.answer?item.answer:item.options}}
                    </div>
                    <div v-if="item.type === 4" style="margin-left:10px; padding:10px; ">
                        {{index + 1}}. {{item.title}}
                    </div>
                    <div v-if="item.type === 4" style="margin-left:30px; padding:10px; ">
                        {{item.answer}}
                    </div>
                    <div v-if="item.type === 5" style="margin-left:10px; padding:10px; ">
                        {{index + 1}}. {{item.title}}
                    </div>
                    <div v-if="item.type === 5" style="margin-left:30px; padding:10px; ">
                        <!--這邊要根據上傳類型做區分-->
                        <span v-if="item.form === 1">
                            <img :src="item.answer" height="160" width="160" alt="" class="el-upload-list__item-thumbnail">
                        </span>
                        <span v-else>
                            <a id="pdf" class="pdf" :href="item.url">{{item.answer}}</a>
                        </span>
                    </div>
                </template>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import html2canvas from 'html2canvas';
    import jsPDF from 'jspdf';
    import JSZip from 'jszip';
    import FileSaver from 'file-saver';

    export default {
        name: "activity-user-component",

        data: function () {
            return {
                activity_id : this.$route.params.activityId,
                user_id:  this.$route.params.userId,
                activity_name:  this.$route.params.activityName,
                lists : [{
                    mobile : this.$route.params.mobile,
                    name : this.$route.params.name === 'null' ? '' : this.$route.params.name,
                    sex : this.$route.params.sex,
                    offline_create_time : this.$route.params.offlineCreateTime === 'null' ? '否' : '是'
                }],
                answers : {},
                formLabelWidth : '100px',
                loading : false
            }
        },

        mounted: function () {
            this.$nextTick(function () {
                this.getAnswers();
            })
        },

        methods: {
            getAnswers : function() {
                axios.get('/activity/getAnswers/' + this.activity_id + '/' + this.user_id).then((response) => {
                    if (response.data.code === 0) {
                        this.answers = response.data.response;
                    }
                }).catch(function (error) {

                });
            },

            printPage : function () {
                window.print();
            },

            downPage : function () {
                let title = '報名表_' + this.$route.params.mobile;

                html2canvas(document.querySelector('#app'), {
                    allowTaint: true
                }).then(function (canvas) {
                    let contentWidth = canvas.width;
                    let contentHeight = canvas.height;
                    let pageHeight = contentWidth / 592.28 * 841.89;
                    let leftHeight = contentHeight;
                    let position = 0;
                    let imgWidth = 595.28;
                    let imgHeight = 592.28 / contentWidth * contentHeight;
                    let pageData = canvas.toDataURL('image/jpeg', 1.0);
                    let PDF = new jsPDF('', 'pt', 'a4');
                    if (leftHeight < pageHeight) {
                        PDF.addImage(pageData, 'JPEG', 0, 0, imgWidth, imgHeight);
                    } else {
                        while (leftHeight > 0) {
                            PDF.addImage(pageData, 'JPEG', 0, position, imgWidth, imgHeight);
                            leftHeight -= pageHeight;
                            position -= 841.89;
                            if (leftHeight > 0) {
                                PDF.addPage();
                            }
                        }
                    }

                    PDF.save(title + '.pdf');
                });
            },

            downZip : function () {
                let that = this;

                that.loading = true;

                axios.get('/activity/downloadFile/' + this.activity_id + '/' + this.user_id + '/' + this.$route.params.mobile).then((response) => {
                    if (response.data.code === -1) {
                        that.loading = false;
                        alert('沒有可下載的附件');
                    }

                    if (response.data.code === 0) {
                        let data = response.data.data;

                        var zip = new JSZip();
                        data.forEach(function (value, index) {
                            zip.file(value.filename, value.url, {base64: true});
                            if (index === data.length - 1) {
                                zip.generateAsync({type:"blob"}).then(function (blob) {
                                    FileSaver.saveAs(blob, "附件_" + that.$route.params.mobile + '.zip');
                                });
                            }
                        });

                        that.loading = false;
                    }
                }).catch(function (error) {

                });
            }
        }
    }
</script>