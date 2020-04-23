<template>
    <div>
        <div id="app" style="padding:20px;">
            <el-row>
                <el-card class="box-card">
                    <div style="display: inline;float: left;">
                        <div style="font-size:28px;font-weight:bold"><span style="font-weight:bold">{{activity.name}}</span></div>
                        <div>活動時間：{{activity.open_time}}</div>
                        <div>線上報名人數：{{online_registration}}人</div>
                        <div v-if="is_offline === 1">已報到人數：{{offline_registration}}人</div>
                        <div v-if="is_offline === 1 && is_online === 1">尚未報到人數：{{remain_registration}}人</div>
                        <div v-if="qr_type === 1">現場報到QR碼：單一QR碼</div>
                        <div v-else>現場報到QR碼：多個QR碼</div>
                        <div v-if="down_log.length > 0" style="color: #0c7cbb">下載記錄 ：</div>
                        <div>
                            <template  v-for="(item, index) in down_log">
                                <div style="color: #0c7cbb">
                                    {{item.down_info}}
                                </div>
                            </template>
                        </div>
                    </div>
                    <div v-if="activityQr !== '' && is_offline === 1" style="display: inline;float: right;margin-right: 20px;">
                        <img :src="activityQr" width="200" height="200"/>
                    </div>
                    <div v-if="activityQr !== '' && is_offline === 1" style="display: inline;float: right;margin-right: -200px; margin-top: 200px;">
                        現場報到QR Code&nbsp;<el-button type="primary" size="mini" @click="downQRCode">下載</el-button>
                    </div>

                    <!--提交前拿掉-->
                    <!--<div v-if="activityQr !== ''" style="display: inline;float: right;margin-right: 20px;">-->
                    <!--<img :src="activityQr" width="200" height="200"/>-->
                    <!--</div>-->
                    <!--<div v-if="activityQr !== ''" style="display: inline;float: right;margin-right: -200px; margin-top: 200px;">-->
                    <!--現場報到QR Code&nbsp;<el-button type="primary" size="mini" @click="downQRCode">下載</el-button>-->
                    <!--</div>-->
                </el-card>

                <el-col style="margin-top:20px;">
                    <el-row>
                        <el-button type="primary" size='small' @click="reloadPage">重新載入</el-button>
                        <el-button type="primary" size='small' @click="exportExcel">下載清冊</el-button>
                        <el-button type="primary" size='small' v-if="lists.length > 0 && !downLoading" @click="exportZip">下載報名表</el-button>
                        <el-button type="primary" size='small' v-if="downLoading">下載報名表中...</el-button>
                        <el-button type="primary" size='small' v-if="lists.length > 0" @click="createGroup">建立報到群組</el-button>
                    </el-row>
                </el-col>

                <el-col style="margin-top:20px;">
                    <el-form :inline="true" class="demo-form-inline">
                        <el-form-item>
                            <el-select v-model="search.type">
                                <el-option label="手機號碼" value="mobile"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item>
                            <el-input v-model="search.keys" auto-complete="off"></el-input>
                        </el-form-item>

                        <el-form-item label="性別:">
                            <el-select v-model="search.sex">
                                <el-option label="全部" value="-1"></el-option>
                                <el-option label="男" value="1"></el-option>
                                <el-option label="女" value="2"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary" icon="el-icon-search" @click="doSearch">查詢</el-button>
                        </el-form-item>
                    </el-form>
                </el-col>

                <el-col style="margin-top:20px;">
                    <el-table :data="lists" stripe v-loading="loading">
                        <el-table-column prop="id" label="項次">
                            <template slot-scope="scope">
                                {{ scope.$index+1 }}
                            </template>
                        </el-table-column>
                        <el-table-column prop="mobile" label="手機號碼">
                            <template slot-scope="scope">
                                <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;"
                                   @click="showUserInfo(scope.row.user_id, activity.name, scope.row.mobile, scope.row.name, scope.row.sex, scope.row.offline_create_time, id)">
                                    {{ scope.row.mobile }}
                                </a>
                            </template>
                        </el-table-column>
                        <el-table-column prop="name" label="姓名"></el-table-column>
                        <el-table-column prop="sex" label="性別"></el-table-column>
                        <el-table-column prop="user_create_time" label="註冊時間">
                        </el-table-column>
                        <el-table-column prop="create_time" label="報名時間">
                            <template slot-scope="scope">
                            <span v-if="scope.row.content">
                                {{ scope.row.create_time }}
                            </span>
                            </template>
                        </el-table-column>
                        <el-table-column prop="offline_create_time" label="報到時間">

                        </el-table-column>
                    </el-table>
                </el-col>

                <el-col v-if="lists.length > 0">
                    <div class="app-pagination">
                        <el-pagination
                            :page-sizes="[10,20,30,50,100]"
                            @size-change="changeLimit"
                            @current-change="loadData"
                            :current-page="search.page"
                            :page-size="search.limit"
                            layout="sizes,total,prev,pager,next"
                            :total="total">
                        </el-pagination>
                    </div>
                </el-col>
            </el-row>

            <div id="pdfFile" style="padding:20px;" v-show="showPdf">
                <el-row>
                    <el-card class="box-card">
                        <div style="display: inline; float: left;">
                            <div style="font-size:28px;font-weight:bold"><span style="font-weight:bold">{{activity.name}}</span></div>
                        </div>
                    </el-card>

                    <el-col style="margin-top:20px;" :label-width="formLabelWidth">
                        <el-table :data="pdfLists" stripe>
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
        </div>
    </div>
</template>

<script>
    import html2canvas from 'html2canvas';
    import jsPDF from 'jspdf';
    import JSZip from 'jszip';
    import FileSaver from 'file-saver';
    import Tools from '../../tools/vue-common-tools';
    import Dialog from '../../tools/element-ui-dialog';

    export default {
        name: "MessageActivityOfflineList",

        data : function () {
            return {
                id:  this.$route.params.id,
                loading : true,
                search : {
                    aid : this.$route.params.id,
                    sex : '-1',
                    page : 1,
                    type : 'mobile',
                    limit : 10,
                    keys : ''
                },
                lists : [],
                count : {},
                total : 0,
                activity : {},
                activityQr:'',
                department_id : 1,
                online_registration : 0,
                offline_registration : 0,
                remain_registration : 0,
                downLoading : false,
                pdfLists : [{
                    mobile : '',
                    name : '',
                    sex : '',
                    offline_create_time : '',
                    offline_user_create_time : ''
                }],
                formLabelWidth : '100px',
                answers : {},
                file_type : 0,
                file_number : 0,
                showPdf : false,
                is_offline : 0,
                is_online : 0,
                qr_type : 1,
                down_log : {}
            };
        },

        mounted: function () {
            this.$nextTick(function () {
                //this.getQr();
            })
        },
        methods : {
            exportExcel(){
                let type = 0;

                window.open('/activity/offlineUserExport/' + this.id + '/' + null + '/' + this.activity.open_time
                    + '/' + this.online_registration + '/' + this.offline_registration + '/' + this.remain_registration + '/' + type);
            },
            // getQr(){
            //     axios.get('/api/qr/active/'+this.id).then((response) => {
            //         if(response.data.code === '0') {
            //             let content = response.data.response.content;
            //             this.activityQr = 'data:image/png;base64,'+content;
            //         }
            //     }).catch(function (error) {
            //     });
            // },
            doSearch : function () {
                this.search.page = 1;
                this.loadData(this.search.page);
            },
            loadData : function (page) {
                let queryString = Tools.BuildQueryString(this.search, page);

                axios.get('/activity/onlineUserList'+queryString + '&mode=offline').then((response) => {
                    this.changeLoadStatus(false);
                    this.lists = response.data.response.lists;
                }).catch((error) => {

                });

                this.changeLoadStatus(true);

                axios.get('/activity/offlineUserList?sex=-1&page=1&type=mobile&limit=1&aid=' + this.id).then((response) => {
                    this.changeLoadStatus(false);
                    this.activity = response.data.response.activity;
                    this.deparment_id = this.activity.department_id;
                    this.online_registration = this.activity.online_registration;
                    this.offline_registration = this.activity.offline_registration;
                    this.remain_registration = this.activity.no_report;
                    this.is_offline = this.activity.is_offline;
                    this.is_online = this.activity.is_online;
                    this.qr_type = this.activity.is_live_type;
                    this.activityQr = this.activity.qr_url;
                    this.down_log = response.data.response.down_log;
                }).catch((error) => {
                    this.changeLoadStatus(false);
                });

                axios.get('/activity/onlineUserCount'+queryString + '&mode=offline').then((response) => {
                    this.total = response.data.response.count;
                }).catch((error) => {

                });

            },

            changeLimit : function (limit) {
                this.search.limit = limit;
                this.loadData(1);
            },

            changeLoadStatus : function (status) {
                this.loading = status;
            },

            reloadPage : function () {
                window.location.reload();
            },

            exportZip : function () {
                let that = this;
                that.downLoading = true;

                let zip = new JSZip();

                let arr = [];
                let tempApi = [];

                that.lists.forEach(function (value, index) {
                    tempApi.push(axios.get('/activity/isFile/' + that.id + '/' + value.user_id));
                });

                axios.all(tempApi).then(axios.spread(function (...res) {
                    for (let i = 0; i < res.length; i++) {
                        if (res[i].data.code === -1) {
                            arr.push(-1);
                        }
                    }

                    //判斷所有用戶是否夾帶附件
                    //0 無夾帶 1全夾帶 2二者都有
                    if (arr.length === 0) {
                        that.file_type = 1;
                        that.file_number = that.lists.length;
                    } else {
                        if (arr.length === that.lists.length) {
                            that.file_type = 0;
                            that.file_number = 0;
                        }

                        if (arr.length < that.lists.length) {
                            that.file_type = 2;
                            that.file_number = that.lists.length - arr.length;//幾個用戶夾帶文件
                        }
                    }

                    let i = 0;

                    that.lists.forEach(function (value, index) {
                        let title = '報名表_' + value.mobile;
                        let PDF = new jsPDF('', 'pt', 'a4');
                        let img = zip.folder(value.mobile);
                        // if (!value.content) {
                        //     return;
                        // }

                        //gif
                        axios.get('/activity/getAnswers/' + that.id + '/' + value.user_id).then((response) => {
                            if (response.data.code === 0) {
                                that.showPdf = true;
                                that.answers = {};
                                that.answers = response.data.response;

                                that.pdfLists = [{
                                    mobile : value.mobile,
                                    name : value.name,
                                    sex : value.sex,
                                    offline_create_time : value.offline_create_time == null ? '否' : '是',
                                    offline_user_create_time : value.user_create_time
                                }];

                                html2canvas(document.querySelector('#pdfFile'), {
                                    allowTaint: true
                                }).then(function (canvas) {
                                    let contentWidth  = canvas.width;
                                    let contentHeight = canvas.height;
                                    let pageHeight = contentWidth / 592.28 * 841.89;
                                    let leftHeight = contentHeight;
                                    let position = 0;
                                    let imgWidth = 595.28;
                                    let imgHeight = 592.28 / contentWidth * contentHeight;
                                    let pageData  = canvas.toDataURL('image/jpeg', 1.0);

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

                                    img.file(title + '.pdf', PDF.output('blob'));
                                    that.showPdf = false;

                                    if (that.file_type === 0) {
                                        if (index === that.lists.length - 1) {
                                            zip.generateAsync({type:"blob"}).then(function (blob) {
                                                FileSaver.saveAs(blob, that.activity.name + '.zip');
                                            });

                                            that.downLoading = false;
                                        }
                                    }
                                });
                            }
                        });

                        if (that.file_number > 0) {
                            //file
                            axios.get('/activity/downloadFile/' + that.id + '/' + value.user_id + '/' + value.mobile).then((response) => {
                                if (response.data.code === 0) {
                                    if (that.file_type === 2) {
                                        i += 1;
                                    }

                                    let data = response.data.data;
                                    data.forEach(function (values, indexs) {
                                        img.file(values.filename, values.url, {base64: true});

                                        if (that.file_type === 2) {
                                            if (i === that.file_number) {
                                                //兩者都帶  重複zip
                                                if (indexs === data.length - 1) {
                                                    zip.generateAsync({type:"blob"}).then(function (blob) {
                                                        FileSaver.saveAs(blob, that.activity.name + '.zip');
                                                    });

                                                    that.downLoading = false;
                                                }
                                            }
                                        }

                                        //全附件
                                        if (that.file_type === 1) {
                                            if (index === that.lists.length - 1) {
                                                if (indexs === data.length - 1) {
                                                    zip.generateAsync({type:"blob"}).then(function (blob) {
                                                        FileSaver.saveAs(blob, that.activity.name + '.zip');
                                                    });

                                                    that.downLoading = false;
                                                }
                                            }
                                        }
                                    });
                                }

                                if (response.data.code === -1) {
                                    //全沒附件
                                    if (that.file_type === 0) {
                                        if (index === that.lists.length - 1) {
                                            zip.generateAsync({type:"blob"}).then(function (blob) {
                                                FileSaver.saveAs(blob, that.activity.name + '.zip');
                                            });

                                            that.downLoading = false;
                                        }
                                    }
                                }
                            });
                        }
                    });
                }));
            },

            getFile : function () {
                axios.get('/activity/downloadFile/' + this.id + '/' + value.user_id + '/' + value.mobile).then((response) => {
                    if (response.data.code === 0) {
                        return response.data.data;
                    }

                    if (response.data.code === -1) {
                        return {};
                    }
                });
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

            createGroup : function () {
                let type = 1;

                axios.get('/activity/offlineUserExport/' + this.id + '/' + null + '/' + this.activity.open_time
                    + '/' + this.online_registration + '/' + this.offline_registration + '/' + this.remain_registration + '/' + type).then((response) => {
                    if (response.data.response.length > 0) {
                        let data = {
                            'data' : response.data.response,
                            'department_id': this.deparment_id,
                            'name' : this.activity.name
                        };

                        axios.post('/activity/createDepartmentGroup', data).then((response) => {
                            if(response.data.code === 0) {
                                alert('縣民群組已建立');
                            }
                        }).catch((error) => {

                        });
                    }
                }).catch((error) => {
                    this.changeLoadStatus(false);
                });
            },

            showUserInfo : function (user_id, activityName, mobile, name, sex, offline_create_time, id) {
                if (name === '') {
                    name = ' ';
                }

                Tools.OpenNewWindow('/#/activity/online/'+ user_id + '/' + activityName + '/' + mobile + '/' + name + '/'
                    + sex + '/' + offline_create_time + '/' + id, '活動報名表', window.screen.height - 500, window.screen.width - 1200);
            },

            downQRCode() {
                let that = this;
                let zip = new JSZip();
                let img = zip.folder(that.activity.name);
                let url = '';

                if (that.qr_type === 1) {
                    url= '/api/qr/active/' + that.id + '?type=down&activity_name=' + that.activity.name;
                } else {
                    url = '/api/qr/multipleActive?id=' + that.id;
                }

                axios.get(url).then((response) => {
                    if (that.qr_type === 1) {//單一QR
                        //下載zip
                        let single_pic = response.data.response.content;
                        img.file(that.activity.name + '_現場報到QR碼' + '.jpg', single_pic, {base64: true});
                        zip.generateAsync({type:"blob"}).then(function (blob) {
                            FileSaver.saveAs(blob, that.activity.name + '.zip');
                        });

                        //寫記錄
                        axios.get('/activity/make/record?activity_id=' + that.id + '&qr_type=' + that.qr_type).then((res) => {
                            if (res.data.code > 0) {
                                Dialog(this).openSuccess(() => {
                                    setTimeout(() => {
                                        window.location.reload();
                                        //window.opener.location.reload(true);
                                        //that.closeWindowFuc();
                                    }, 1000);
                                }, '下載成功');
                            }
                        }).catch((error) => {
                            Dialog(this).openError(callback, error);
                        });
                    } else {
                        if (response.data.code === '-1') {
                            Tools.Dialog(this).openWarning(null, '已下載過QR碼');
                            return ;
                        }

                        if (response.data.code === '-2') {
                            Tools.Dialog(this).openWarning(null, '領取人數不能為0，請重新設定');
                            return ;
                        }

                        if (response.data.code === '-3') {
                            Tools.Dialog(this).openWarning(null, 'QR數量過多，明日再試吧');
                            return ;
                        }

                        if (response.data.code === '0') {
                            //下載zip
                            let multiple_pic = response.data.response.content;//多個
                            //循環壓縮
                            multiple_pic.forEach(function (item, key) {
                                img.file(that.activity.name + '_現場報到QR碼_' + item.key + '.jpg', item.content, {base64: true});
                                if (key === multiple_pic.length - 1) {
                                    zip.generateAsync({type:"blob"}).then(function (blob) {
                                        FileSaver.saveAs(blob, that.activity.name + '.zip');
                                    });
                                }
                            });

                            //寫記錄
                            axios.get('/activity/make/record?activity_id=' + that.id + '&qr_type=' + that.qr_type).then((res) => {
                                if (res.data.code > 0) {
                                    Dialog(this).openSuccess(() => {
                                        setTimeout(() => {
                                            window.location.reload();
                                            //window.opener.location.reload(true);
                                            //that.closeWindowFuc();
                                        }, 1000);
                                    }, '下載成功');
                                }
                            }).catch((error) => {
                                Dialog(this).openError(callback, error);
                            });
                        }
                    }
                });
            },

            closeWindowFuc : function(){
                let userAgent = navigator.userAgent;
                if (userAgent.indexOf("Firefox") != -1 || userAgent.indexOf("Chrome") !=-1) {
                    window.location.href="about:blank";
                    window.close();
                } else {
                    window.opener = null;
                    window.open("", "_self");
                    window.close();
                }
            }
        },

        created : function () {
            this.loadData(1);
        },
    }
</script>

<style scoped>

</style>