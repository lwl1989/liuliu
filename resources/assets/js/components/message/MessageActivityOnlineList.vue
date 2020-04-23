<template>
    <div id="app" style="padding:20px;">
        <el-row>
            <el-card class="box-card">
                <div style="font-size:28px;font-weight:bold"><span style="font-weight:bold">{{activity.name}}</span></div>
                <div>活動時間：{{activity.open_time}}</div>
                <div>線上報名人數：{{total}}人</div>
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
                               @click="showUserInfo(scope.row.user_id, activity.name, scope.row.mobile, scope.row.name, scope.row.sex, scope.row.offline_create_time, scope.row.activities_id)">
                                {{ scope.row.mobile }}
                            </a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="姓名"></el-table-column>
                    <el-table-column prop="sex" label="性別"></el-table-column>
                    <el-table-column prop="user_create_time" label="註冊時間">
                    </el-table-column>
                    <el-table-column prop="create_time" label="報名時間"></el-table-column>
                    <!--<el-table-column prop="mobile" label="報到時間"></el-table-column>-->
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
                    <el-table :data="pdfLists">
                        <el-table-column prop="mobile" label="手機號碼" min-width="100px"></el-table-column>
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
</template>

<script>
    import Tools from '../../tools/vue-common-tools';
    import html2canvas from 'html2canvas';
    import jsPDF from 'jspdf';
    import JSZip from 'jszip';
    import FileSaver from 'file-saver';

    export default {
        name: "MessageActivityOnlineList",

        data : function () {
            return {
                loading : true,
                id:  this.$route.params.id,
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
                downLoading : false,
                showPdf : false,
                pdfLists : [{
                    mobile : '',
                    name : '',
                    sex : '',
                    offline_create_time : '',
                    offline_user_create_time : ''
                }],
                formLabelWidth : '100%',
                answers : {},
            };
        },
        methods : {
            exportExcel(){
                let type = 0;
                window.open('/activity/onlineUserExport/' + this.id + '/' + null + '/' + this.activity.open_time
                    + '/' + this.total + '/' + type);
            },

            doSearch : function () {
//                if(this.search.keys === '') {
//                    Dialog(this).openWarning(null, '搜索關鍵字不能為空');
//                    return;
//                }

                this.search.page = 1;
                this.loadData(this.search.page);
            },

            loadData : function (page) {
                let queryString = Tools.BuildQueryString(this.search, page);

                axios.get('/activity/onlineUserCount'+queryString + '&mode=online').then((response) => {
                    this.total = response.data.response.count;
                }).catch((error) => {

                });

                this.changeLoadStatus(true);
                axios.get('/activity/onlineUserList'+queryString + '&mode=online').then((response) => {
                    this.changeLoadStatus(false);
                    this.lists = response.data.response.lists;
                    this.activity = response.data.response.activity;
                }).catch((error) => {
                    this.changeLoadStatus(false);
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

            showUserInfo : function (user_id, activityName, mobile, name, sex, offline_create_time, id) {
                if (name === '') {
                    name = ' ';
                }

                Tools.OpenNewWindow('/#/activity/online/'+ user_id + '/' + activityName + '/' + mobile + '/' + name + '/'
                    + sex + '/' + offline_create_time + '/' + id, '活動報名表', window.screen.height - 500, window.screen.width - 1200);
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

                        let img = zip.folder(value.mobile);
                        let PDF = new jsPDF('', 'pt', 'a4');

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

                                console.log(value.offline_create_time);

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

            createGroup : function () {
                let type = 1;

                axios.get('/activity/onlineUserExport/' + this.id + '/' + null + '/' + this.activity.open_time
                    + '/' + this.total + '/' + type).then((response) => {
                        console.log(response);
                    if (response.data.response.length > 0) {
                        let data = {
                            'data' : response.data.response,
                            'department_id': this.activity.department_id,
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

        },

        created : function () {
            this.loadData(1);
        },
    }
</script>

<style scoped>

</style>