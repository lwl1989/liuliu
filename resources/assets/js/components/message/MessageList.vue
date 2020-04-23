<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div style="padding-top:5px;">
                    <el-col :span="100">
                        <el-form :inline="true" :model="searchForm" :rules="searchRules" ref="searchForm" class="demo-form-inline" size="small">
                            <el-form-item label="推播對象" prop="send_object">
                                <el-select v-model="searchForm.send_object" placeholder="所有用戶" style="width:100px">
                                    <el-option label="所有用戶" value="1"></el-option>
                                    <el-option label="個人" value="2"></el-option>
                                    <el-option label="指定群組" value="3"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="推播時間" prop="send_time_type">
                                <el-select v-model="searchForm.send_time_type" placeholder="立即傳送" style="width:140px">
                                    <el-option label="立即傳送" value="1"></el-option>
                                    <el-option label="指定日期與時間" value="3"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="searchForm.search_type" placeholder="業務單位" style="width:110px">
                                    <el-option label="業務單位" value="1"></el-option>
                                    <el-option label="業務名稱" value="2"></el-option>
                                    <el-option label="設定內容" value="3"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-input v-model="searchForm.search_type_key" @keyup.enter.native='doSearch'></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="searchForm.remain_gold_type" placeholder="剩餘滿意度金幣" style="width:140px">
                                    <el-option label="剩餘滿意度金幣" value="1"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="searchForm.symbol_gold_type" placeholder="請選擇" style="width:85px">
                                    <el-option label=">" value="1"></el-option>
                                    <el-option label=">=" value="2"></el-option>
                                    <el-option label="=" value="3"></el-option>
                                    <el-option label="<=" value="4"></el-option>
                                    <el-option label="<" value="5"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item prop="symbol_gold_key">
                                <el-input-number v-model="searchForm.symbol_gold_key" @keyup.enter.native='doSearch'></el-input-number>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="searchForm.search_time_type" placeholder="最後異動日期" style="width:140px">
                                    <el-option label="最後異動日期" value="1"></el-option>
                                    <!--<el-option label="新增日期" value="2"></el-option>-->
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-date-picker
                                        style="width:230px"
                                        v-model="searchForm.date_time"
                                        type="daterange"
                                        range-separator="~"
                                        start-placeholder="開始日期"
                                        end-placeholder="結束日期"
                                        align="right">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary" icon="el-icon-search" @click="doSearch">查詢</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>
                    <el-col :span="8">
                        <el-button-group>
                            <el-button type="primary" class="el-icon-remove" @click="stopMessageSend()">停止發送訊息</el-button>
                        </el-button-group>
                    </el-col>
                </div>
            </el-col>
            <el-col>
                總筆數：{{total}}
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="messageSends" stripe v-loading="loading" @selection-change="handleSelectionChange">
                    <el-table-column type="selection"></el-table-column>
                    <el-table-column prop="id" label="項次" width="80">
                        <template slot-scope="scope">{{ scope.$index+1 }}</template>
                    </el-table-column>
                    <el-table-column prop="department_name" label="業務單位"></el-table-column>
                    <el-table-column prop="name" label="業務名稱" show-overflow-tooltip>
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color:#00afff;"
                               @click="editMessage(scope.row.id)">{{ scope.row.name }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="send_object_tag" label="推播對象" width="100"></el-table-column>
                    <el-table-column prop="send_object_unit" label="推播對象單位"></el-table-column>
                    <el-table-column prop="sent_status_tag" label="推播狀態"></el-table-column>
                    <el-table-column prop="read_number" label="已讀人數">
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color:#00afff;"
                               @click="openMember(scope.row)" v-if="scope.row.send_group_id > 0">{{scope.row.read_number}}</a>
                            <span v-else> {{scope.row.read_number}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="online_answers" label="線上報名">
                        <template slot-scope="scope" v-if="scope.row.online_answers">
                            <a href="javascript:void(0)" style="text-decoration:none;color:#00afff;"
                               @click="onlineRegistration(scope.row.activity_id)">{{ scope.row.online_answers }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="score" label="滿意度回饋">
                        <template slot-scope="scope" v-if="scope.row.score > 0">
                            <a href="javascript:void(0)" style="text-decoration:none;color:#00afff;"
                               @click="openSatisfaction(scope.row)">{{scope.row.score}}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="remain_gold" label="剩餘滿意度金幣">
                        <template slot-scope="scope">
                            <div v-if="scope.row.remain_gold > 0">
                                {{scope.row.remain_gold}}
                                <el-button size="small" type="warning"
                                           @click="recycleGold(scope.row.id, scope.row.remain_gold)"
                                           v-show="scope.row.remain_gold > 0">回收
                                </el-button>
                            </div>
                            <span v-else-if="scope.row.recycle_status == 2">
                                {{scope.row.recycle_gold}} 已回收
                            </span>

                        </template>
                    </el-table-column>
                    <el-table-column prop="send_time" label="發送時間"></el-table-column>
                    <el-table-column prop="admin_info" label="最後異動資訊"></el-table-column>
                </el-table>
            </el-col>

            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10,20,30,50,100]"
                            @size-change="changeLimit"
                            @current-change="loadData"
                            :current-page="searchForm.page"
                            :page-size="searchForm.limit"
                            layout="sizes,total,prev,pager,next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <el-dialog title="新增訊息設定" :visible.sync="addMessageSettingVisible" :modal-append-to-body="false"
                   :close-on-click-modal="false" class="dialog-import">
            <el-radio-group v-model="labelChoose">
                <el-radio-button label="message">推播訊息主要設定</el-radio-button>
                <el-radio-button label="questionnaire">活動及問卷調查設定</el-radio-button>
                <el-radio-button label="period">推播週期設定</el-radio-button>
            </el-radio-group>
            <div style="margin: 20px;"></div>

            <el-form :model="messageSettingForm" :rules="rules" ref="messageSettingForm">
                <div v-if="labelChoose === 'message'">
                    <el-form-item label="業務單位" :label-width="formLabelWidth" prop="department_id">
                        <el-select v-model="messageSettingForm.department_id" placeholder="請選擇" @change="changeUnit"
                                   :disabled="disableEdit">
                            <template v-for="item in unit_sum_list">
                                <el-option :label="item.name" :value="item.id" :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="業務名稱" :label-width="formLabelWidth" prop="name">
                        <el-col :span="10">
                            <el-input v-model="messageSettingForm.name" auto-complete="off"
                                      :disabled="disableEdit"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="推播對象" :label-width="formLabelWidth" prop="send_object">
                        <el-select v-model="messageSettingForm.send_object" placeholder="所有用戶" :disabled="disableEdit">
                            <el-option label="所有用戶" value="1"></el-option>
                            <el-option label="個人" value="2"></el-option>
                            <el-option label="指定群組" value="3"></el-option><!--需顯示縣民群組-->
                        </el-select>
                        <el-select v-model="messageSettingForm.send_group_id" placeholder="所有用戶"
                                   v-if="messageSettingForm.send_object == 3" :disabled="disableEdit">
                            <template v-for="item in group_sum">
                                <el-option :label="item.name" :value="item.id" :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="推播內容" :label-width="formLabelWidth" prop="content">
                        <el-input type="textarea" :autosize="{minRows: 3, maxRows: 4}"
                                  v-model="messageSettingForm.content" :disabled="disableEdit"></el-input>
                        <!--<i class="el-icon-upload2" style="color:#0000F0" @click="insertContent"></i>-->
                        <!--<el-select v-model="messageSettingForm.content_id" placeholder="" :disabled="disableEdit">-->
                        <!--<el-option label="發起人" value="發起人"></el-option>-->
                        <!--<el-option label="申請人" value="申請人"></el-option>-->
                        <!--<el-option label="會議日期" value="會議日期"></el-option>-->
                        <!--</el-select>-->
                        <!--<div style="margin: 20px;"></div>-->
                        <!--<el-button size="small" type="primary" @click="saveExample" :disabled="disableEdit">存成範本</el-button>-->
                        <!--（請編輯您所要推播內容，若選擇自動帶入，於發送訊息時，自動帶入將轉換為實際資料內容）-->
                    </el-form-item>
                    <el-form-item label="發送圖片" :label-width="formLabelWidth" prop="image">
                        <el-upload
                                action="/message/setting/image"
                                :multiple=false
                                :limit=1
                                :headers="headers"
                                accept=".jpg,.jpeg,.png,.PNG,.JPG,.JPEG"
                                ref="upload"
                                :on-remove="handleRemove"
                                :file-list="messageSettingForm.image"
                                :on-success="handleSuccess"
                                :on-change="handleFileChange"
                                :auto-upload=false
                                :disabled="disableEdit"
                                list-type="picture">
                            <el-button size="small" type="primary" v-show="disableEdit === false">選擇檔案</el-button>
                            <div slot="tip" class="el-upload__tip">
                                圖檔建議尺寸180像素*120像素，僅限.jpg .gif .png格式，只能夾帶一個檔案，檔案大小不可超過600kb
                            </div>
                        </el-upload>
                    </el-form-item>
                </div>

                <div v-if="labelChoose === 'questionnaire'">
                    <el-form-item label="問卷設定" :label-width="formLabelWidth" prop="question_unit_id">
                        <el-checkbox v-model="messageSettingForm.is_question" :disabled="disableEdit">夾帶問卷
                        </el-checkbox>
                        <div style="margin: 20px;"></div>
                        <el-select v-model="messageSettingForm.question_unit_id" @change="setQuestionUnit"
                                   :disabled="disableEdit">
                            <el-option key="0" value="0" label="請選擇"></el-option>
                            <template v-for="(item) in unit_sum_list" v-if="unit_sum_list">
                                <el-option :label="item.name" :value="item.id" :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                        <div style="margin: 20px;"></div>
                        <el-select v-model="messageSettingForm.question_id" :disabled="disableEdit">
                            <el-option key="0" value="0" label="請選擇"></el-option>
                            <template v-for="(item) in question_sum" v-if="question_sum">
                                <el-option :label="item.name" :value="item.id" :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                        <el-col :span="20" style="color:#409EFF;">
                            提醒您先至3.4建立問卷
                        </el-col>
                    </el-form-item>
                    <el-form-item label="活動設定" :label-width="formLabelWidth" prop="activity_unit_id">
                        <el-checkbox v-model="messageSettingForm.is_activity" :disabled="disableEdit">夾帶活動
                        </el-checkbox>
                        <div style="margin: 20px;"></div>
                        <el-select v-model="messageSettingForm.activity_unit_id" @change="setActivityUnit"
                                   :disabled="disableEdit">
                            <el-option key="0" value="0" label="請選擇"></el-option>
                            <template v-for="(item) in unit_sum_list">
                                <el-option :label="item.name" :value="item.id" :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                        <div style="margin: 20px;"></div>
                        <el-select v-model="messageSettingForm.activity_id" placeholder="" :disabled="disableEdit">
                            <el-option key="0" value="0" label="請選擇"></el-option>
                            <template v-for="(item) in activity_sum" v-if="activity_sum">
                                <el-option :label="item.name" :value="item.id" :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                        <el-col :span="20" style="color: #409EFF;">
                            提醒您先至3.5建立活動
                        </el-col>
                    </el-form-item>
                    <el-form-item label="調查設定" :label-width="formLabelWidth" prop="is_survey">
                        <el-checkbox v-model="messageSettingForm.is_survey " :disabled="disableEdit">夾帶滿意度調查
                        </el-checkbox>
                        <el-button v-show='messageSettingForm.is_survey' style='margin:0 0 0 15px;'
                                   type="primary" size="mini" @click="showSurveySetting()">
                            滿意度金幣
                        </el-button>
                    </el-form-item>
                </div>

                <div v-if="labelChoose === 'period'">
                    <el-form-item label="推播時間" :label-width="formLabelWidth" prop="send_time_type">
                        <template>
                            <el-radio-group v-model="messageSettingForm.send_time_type" :disabled="disableEdit">
                                <el-radio label="1">立即傳送</el-radio>
                                <div style="margin: 10px;"></div>
                                <el-radio label="2">每天固定於
                                    <el-time-picker
                                            type="time"
                                            placeholder="選擇時間"
                                            v-model="messageSettingForm.everyday_time"
                                            @change="timeEverydayChange"
                                            format="HH:mm"
                                            value-format="HH:mm"
                                            style="width: 50%;">
                                    </el-time-picker>
                                    傳送
                                </el-radio>
                                <div style="margin: 10px;"></div>
                                <el-radio label="3">指定日期與時間
                                    <el-date-picker
                                            v-model="messageSettingForm.set_datetime"
                                            type="datetime"
                                            placeholder="選擇日期時間"
                                            @change="timeDateTimeChange"
                                            :disabled="disableEdit"
                                    >
                                    </el-date-picker>
                                </el-radio>
                            </el-radio-group>
                        </template>
                    </el-form-item>
                    <el-form-item label="推播次數" :label-width="formLabelWidth" prop="send_count">
                        <el-col :span="10">
                            <el-input v-model="messageSettingForm.send_count" auto-complete="off"
                                      :disabled="disableEdit"></el-input>
                        </el-col>
                        次
                    </el-form-item>
                    <el-form-item label="間隔時間" :label-width="formLabelWidth" prop="space_time">
                        <el-col :span="10">
                            <el-input v-model="messageSettingForm.space_time" auto-complete="off"
                                      :disabled="disableEdit"></el-input>
                        </el-col>
                        <el-select v-model="messageSettingForm.space_time_type" placeholder="" :disabled="disableEdit">
                            <el-option label="天" value="1"></el-option>
                            <el-option label="時" value="2"></el-option>
                            <el-option label="分" value="3"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="有效時間" :label-width="formLabelWidth" prop="valid_time_type">
                        <template>
                            <el-radio-group v-model="messageSettingForm.valid_time_type" :disabled="disableEdit">
                                <el-radio label="1">於
                                    <el-input v-model="messageSettingForm.end_time" auto-complete="off"
                                              style="width: 40%"></el-input>
                                    <el-select v-model="messageSettingForm.end_time_type" placeholder="">
                                        <el-option label="天" value="1"></el-option>
                                        <el-option label="時" value="2"></el-option>
                                        <el-option label="分" value="3"></el-option>
                                    </el-select>
                                    停止
                                </el-radio>
                                <div style="margin: 10px;"></div>
                                <el-radio label="2">於
                                    <el-date-picker
                                            v-model="messageSettingForm.end_datetime"
                                            type="datetime"
                                            placeholder="選擇日期時間"
                                            @change="timeValidateChange"
                                            :disabled="disableEdit"
                                    >
                                    </el-date-picker>
                                    停止
                                </el-radio>
                            </el-radio-group>
                        </template>
                    </el-form-item>
                </div>
            </el-form>

            <div slot="footer" class="dialog-footer" v-show="disableEdit === false">
                <el-button type="primary" @click="stopMessageSend(messageSettingForm.id)">取消發送</el-button>
                <el-button type="primary" @click="submitSaveSendMessage('推播此則訊息')">儲存並推播</el-button>
                <el-button type="primary" @click="addMessageSettingVisible = false">取消</el-button>
            </div>
        </el-dialog>

        <el-dialog title="金幣發放" :visible.sync="showGoldGrant" :modal-append-to-body="false"
                   :close-on-click-modal="false">
            <el-form label-width="200px">
                <el-form-item label="發放期別(發放日/有效期限)">
                    <el-select v-model="messageSettingForm.stage_id"
                               @change="setRemainGold" style="width: 350px;" :disabled="disableEdit||disableStage">
                        <el-option key="0" value="0" label="請選擇"></el-option>
                        <template v-for="item in send_stage">
                            <el-option :key="item.id.toString()" :value="item.id.toString()"
                                       :label="item.id + '(' + item.issue_time+' ~ '+ item.expire_time + ')'">
                            </el-option>
                        </template>
                    </el-select>
                </el-form-item>
                <el-form-item label="設定發放金幣">
                    <el-col :span="20" style="color:#F56C6C">帳號目前可設定發放金幣為 {{remain_gold}}</el-col>
                    <el-col class="line" :span="5" :offset="0" prop="fans_number">每個人發放</el-col>
                    <el-col :span="6">
                        <el-input v-model="messageSettingForm.person_gold" auto-complete="off"
                                  :disabled="disableEdit||disableStage"></el-input>
                    </el-col>
                    <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                    <el-col :span="20"></el-col>
                    <el-col style="margin:5px 0 0 0;" class="line" :span="5" :offset="0" prop="fans_number">限制前</el-col>
                    <el-col style="margin:5px 0 0 0;" :span="6">
                        <el-input v-model="messageSettingForm.person_limit" auto-complete="off"
                                  :disabled="disableEdit"></el-input>
                    </el-col>
                    <el-col style="margin-top:5px" class="line" :span="3" :offset="1" prop="fans_number">名領取</el-col>
                    <el-col :span="20">總計 {{ totalSendGold }} 枚</el-col>
                </el-form-item>
                <el-form-item label="已發放金幣">
                    <el-col :span="15">
                        <el-input v-model="messageSettingForm.sent_gold" auto-complete="off" disabled></el-input>
                    </el-col>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="goldSubmit">確 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {MessageSettingRule, MessageSettingSearchRule} from '../../tools/element-ui-validate';
    import Tools from '../../tools/vue-common-tools';
    import ElRadioGroup from "element-ui/packages/radio/src/radio-group";

    export default {
        components: {ElRadioGroup},
        name: "message-send",

        data: function () {
            return {
                total: 0,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                loading: true,
                unit_sum: this.setUnitSum(),
                action: 1,
                message: '',
                unit_sum_list: [],
                question_sum: [],
                activity_sum: [],
                group_sum: [],
                remain_gold: 0,
                send_stage: [],
                messageSends: [],
                totalSendGold: 0,
                formLabelWidth: '120px',
                labelChoose: 'message',
                showGoldGrant: false,
                isShowGoldBut: false,
                sendMessageVisible: false,
                addMessageSettingVisible: false,
                messageSettingForm: {
                    id: 0,
                    department_id: '',
                    name: '',
                    send_object: '1',
                    send_person: [],
                    send_group_id: '',
                    content: '',
                    content_id: '發起人',
                    send_content: '',
                    uploadImage: [],
                    image: [],
                    send_time_type: 1,
                    everyday_time: '',
                    set_datetime: '',
                    send_count: 1,
                    sent_status: 1,
                    space_time: '',
                    space_time_type: '1',
                    valid_time_type: '1',
                    end_time: '',
                    end_time_type: '1',
                    end_datetime: '',
                    is_question: false,
                    is_activity: false,
                    is_survey: false,
                    question_unit_id: '',
                    question_id: '0',
                    activity_unit_id: '',
                    activity_id: '0',
                    survey_id: '0',
                    stage_id: '0',
                    person_gold: 0,
                    person_limit: 0,
                    sent_gold: 0,
                    setting_gold: 0
                },
                searchForm: {
                    send_object: '',
                    send_time_type: '',
                    search_type: '1',
                    search_type_key: '',
                    search_time_type: '1',
                    date_time: [
                        new Date(new Date().getTime() - 3600 * 1000 * 24 * 30),
                        new Date()
                    ],
                    date_start: '',
                    date_end: '',
                    remain_gold_type: '1',
                    symbol_gold_type: '',
                    symbol_gold_key: '0',

                    /**! 分頁專用字段 !**/
                    page: 1,
                    limit: 10
                },
                searchRules: MessageSettingSearchRule,
                rules: MessageSettingRule,
                multipleSelection: [],
                deleteIds: {
                    id: []
                },
                data: [],
                disableEdit: true,
                disableStage: false,
                dialog: new Tools.Dialog(this)
            }
        },

        watch: {
            'messageSettingForm.question_id': function (value) {
                if (value === '0') {
                    this.messageSettingForm.is_question = false;
                } else {
                    this.messageSettingForm.is_question = true;
                }
            },

            'messageSettingForm.activity_id': function (value) {
                if (value === '0') {
                    this.messageSettingForm.is_activity = false;
                } else {
                    this.messageSettingForm.is_activity = true;
                }
            },
            'messageSettingForm.survey_id': function (value) {
                if (value === '0') {
                    this.messageSettingForm.is_survey = false;
                } else {
                    this.messageSettingForm.is_survey = true;
                }
                this.isShowGoldBut = this.messageSettingForm.is_survey;
            },

            'messageSettingForm.person_gold': function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (this.showGoldGrant && !re.test(val)) {
                    this.messageSettingForm.person_gold = '';
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    return false;
                }
                this.setTotalSendGold();
            },

            'messageSettingForm.person_limit': function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (this.showGoldGrant && !re.test(val)) {
                    this.messageSettingForm.person_limit = '';
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    return false;
                }
                this.setTotalSendGold();
            }
        },

        created: function () {
            this.loadData(1);
        },

        methods: {
            dealMessageSuccess: function () {
                this.$emit('success', () => {
                    this.loadData(this.searchForm.page);
                });
            },

            dealMessageWarning: function () {
                this.$emit('warning', () => {
                    this.loadData(this.searchForm.page);
                });
            },

            loadData: function (page) {

                if (this.searchForm.date_time && this.searchForm.date_time.length > 0) {
                    this.searchForm.date_start = this.searchForm.date_time[0].getTime().toString()
                    this.searchForm.date_end = this.searchForm.date_time[1].getTime().toString()
                } else {
                    this.searchForm.date_start = this.searchForm.date_end = ''
                }

                let queryString = Tools.BuildQueryString(this.searchForm, page);

                axios.get('/message/send/count' + queryString)
                    .then((response) => {
                        this.total = response.data.response.count;
                    });

                this.changeLoadStatus(true);
                axios.get('/message/send/select' + queryString)
                    .then((response) => {
                        this.changeLoadStatus(false);

                        this.messageSends = response.data.response.list;
                    }).catch((error) => {
                    this.changeLoadStatus(false);
                });
            },

            changeLimit: function (limit) {
                this.searchForm.limit = limit;
                this.loadData(1);
            },

            doSearch: function () {
                this.loadData(1);
            },

            editMessage: function (id) {
                this.addMessageSettingVisible = true;
                this.disableEdit = true;
                this.disableStage = false;
                axios.get('/message/send/messageInfo?id=' + id).then((res) => {
                    if (res.data.response.data) {
                        this.messageSettingForm = res.data.response.data;
                        if (this.messageSettingForm.stage_id !== undefined) {
                            this.messageSettingForm.stage_id = this.messageSettingForm.stage_id.toString();
                            this.setRemainGold(this.messageSettingForm.stage_id);
                        } else {
                            this.messageSettingForm.stage_id = '0';
                            this.messageSettingForm.person_gold = 0;
                            this.messageSettingForm.person_limit = 0;
                        }

                        if (this.messageSettingForm.sent_status === 2) {
                            let that = this;
                            let h = this.$createElement;
                            this.$msgbox({
                                title: '提示',
                                message: h('p', null, [
                                    h('span', null, '要編輯嗎？編輯將停止發送，編輯完請重新選擇發送時間。')
                                ]),
                                showCancelButton: true,
                                confirmButtonText: '編輯',
                                cancelButtonText: '僅查看',
                                beforeClose: (action, instance, done) => {
                                    if (action === 'confirm') {
                                        that.readyToEdit(id)
                                    }
                                    done()
                                }
                            });
                        }
                        let that = this;
                        that.setStageGold(this.messageSettingForm.department_id);

                        if (this.messageSettingForm.is_question) {
                            this.setQuestionUnit(this.messageSettingForm.question_unit_id)
                        }
                        if (this.messageSettingForm.is_activity) {
                            this.setActivityUnit(this.messageSettingForm.activity_unit_id)
                        }

                        if (this.messageSettingForm.is_survey) {
                            this.messageSettingForm.is_survey = true
                        }

                        if (!this.group_sum||this.group_sum.length===0) {
                            this.setGroupSum()
                        }
                    }

                    this.messageSettingForm.id = id;
                    this.messageSettingForm.content_id = '發起人';

                });
            },

            readyToEdit: function (id) {
                let that = this
                axios.post('/message/send/stopTask', JSON.stringify({id: [id]}))
                    .then(function (response) {
                        if (response.data.code == 0) {
                            that.disableEdit = false;
                            if (that.messageSettingForm.stage_id > 0) {
                                that.disableStage = true;
                            }
                        } else {
                            that.dealMessageWarning();
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                        that.dealMessageWarning();
                    });
            },

            stopMessageSend: function (id) {
                //獲取所有選中行的id組成的字符串，以逗號分隔
                let ids;
                if (id !== undefined) {
                    ids = [id];
                } else {
                    ids = this.multipleSelection.map(item => item.id).join();;
                }

                let that = this;
                if (ids.length === 0) {
                    return this.dialog.openError(null, '請至少選擇一筆資料');
                }else if(this.multipleSelection.length === 1) {
                    ids = [ids]
                }
                let h = this.$createElement;
                this.dialog.openSelfDialog((closeCallback) => {
                    this.addMessageSettingVisible = false
                    axios.post('/message/send/stopTask', JSON.stringify({id: ids}))
                        .then(function (response) {
                            if (response.data.code == 0) {
                                that.dealMessageSuccess();
                                closeCallback();
                            } else {
                                that.dealMessageWarning();
                                closeCallback();
                            }
                        })
                        .catch(function (error) {
                            console.log(error);
                            that.dealMessageWarning();
                            closeCallback();
                        });
                }, h('p', null, [
                    h('span', null, '確定停止嗎？停止發送後將無法再重新發送。')
                ]), '執行中...', '確定');
            },

            handleSelectionChange: function (val) {
                this.multipleSelection = val;
            },

            setStageGold: function (id) {
                if(id===undefined||id===''){
                    return
                }
                let that = this;
                let action = 'add'
                if(this.disableEdit){
                    action = 'edit'
                }
                axios.get('/gold/account/departmentStage?id=' + id + '&event_id=' + this.messageSettingForm.id + '&action=' + action)
                    .then((response) => {
                        that.send_stage = response.data.response.list;
                    });
            },
            showSurveySetting: function () {
                this.showGoldGrant = true
                if (this.messageSettingForm.stage_id !== undefined) {
                    this.setRemainGold(this.messageSettingForm.stage_id)
                } else {
                    this.messageSettingForm.stage_id = '0';
                }
            },
            setRemainGold: function (stageId) {
                if (parseInt(stageId) === 0) {
                    this.remain_gold = 0;
                    return;
                }
                axios.get('/gold/send/getRemainGold?stage_id=' + stageId + '&unit_id=' + this.messageSettingForm.department_id)
                    .then((response) => {
                        this.remain_gold = response.data.response.remain_gold;
                    })
            },

            setGroupSum: function () {
                axios.get('/message/setting/getGroupId').then((response) => {
                    for (let key in response.data.response) {
                        this.group_sum.push({
                            id: key,
                            name: response.data.response[key]
                        });
                    }
                });
            },

            setUnitSum: function () {
                axios.get('/department/getAllUnit').then((response) => {
                    for (let key in response.data.response) {
                        this.unit_sum_list.push({
                            id: key,
                            name: response.data.response[key]
                        });
                    }
                });
            },

            setTotalSendGold: function (action) {
                let personLimit = this.messageSettingForm.person_limit ? this.messageSettingForm.person_limit : 0;
                let personGold = this.messageSettingForm.person_gold ? this.messageSettingForm.person_gold : 0;

                this.totalSendGold = personLimit * personGold;

                if (this.remain_gold > 0 && this.totalSendGold > this.remain_gold) {
                    Tools.Dialog(this).openWarning(null, '總發放金幣不得高於可發放金幣');
                    return false;
                }

                this.messageSettingForm.setting_gold = this.totalSendGold;

                if (action == 'submit') {
                    this.showGoldGrant = false;
                }
            },

            goldSubmit: function () {
                let that = this;
                that.setTotalSendGold('submit');
            },

            setQuestionUnit: function (val) {
                if(this.disableEdit){
                    val=val+'&action=view'
                }
                axios.get('/question/getAllName?department_id=' + val).then((response) => {
                    if (response.data.response !== []) {
                        this.question_sum = [];
                        let questionId =  this.messageSettingForm.question_id
                        let contain = false
                        for (let key in response.data.response) {
                            if(questionId>0&&questionId==key){
                                contain = true;
                            }
                            this.question_sum.push({
                                id: key,
                                name: response.data.response[key]
                            });
                        }
                        if(questionId>0&&!contain){
                            this.messageSettingForm.question_id = '0'
                            this.dialog.openError(null, '夾帶的問卷已過期，請重新設定');
                        }

                    } else {
                        this.question_sum.push({id: '0', name: ''});
                        let questionId =  this.messageSettingForm.question_id
                        if(questionId>0){
                            this.messageSettingForm.question_id = '0'
                            this.dialog.openError(null, '夾帶的問卷已過期，請重新設定');
                        }
                    }
                });
            },

            setActivityUnit: function (val) {
                if(this.disableEdit){
                    val=val+'&action=view'
                }
                axios.get('/activity/getAllName?department_id=' + val).then((response) => {
                    if (response.data.response !== []) {
                        this.activity_sum = [];
                        let activityId =  this.messageSettingForm.activity_id
                        let contain = false
                        for (let key in response.data.response) {
                            if(activityId>0&&activityId==key){
                                contain=true
                            }
                            this.activity_sum.push({
                                id: key,
                                name: response.data.response[key]
                            });
                        }

                        if(activityId>0&&!contain){
                            this.messageSettingForm.activity_id = '0'
                            this.dialog.openError(null, '夾帶的活動已過期，請重新設定');
                        }

                    } else {
                        this.activity_sum.push({id: '0', name: ''});
                        let activityId =  this.messageSettingForm.activity_id
                        if(activityId>0){
                            this.messageSettingForm.activity_id = '0'
                            this.dialog.openError(null, '夾帶的活動已過期，請重新設定');
                        }
                    }
                });
            },

            handleRemove: function (file) {
                let index = this.messageSettingForm.image.indexOf(file);

                if (index !== -1) {
                    this.messageSettingForm.image.splice(index);
                }
            },

            handleSuccess: function (res) {
                this.messageSettingForm.image.push(res.response);
            },

            createReader: function (file, error, success) {
                let reader = new FileReader;
                let that = this;
                reader.onLoad = function (evt) {
                    let image = new Image();
                    image.onLoad = function () {
                        let imageType = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (imageType.indexOf(file.type) < 0) {
                            error('上傳的文件是不正確的文件類型[image/jpg|image/jpeg|image/png|image/gif]');
                            return;
                        }

                        if (file.size / 1024 / 1024 > 2) {
                            error('上傳的圖片大小不能超過 2M!');
                            return;
                        }

                        success();
                    };
                    image.src = evt.target.result;
                };

                reader.readAsDataURL(file);
            },

            handleFileChange: function (file, files) {
                if (!("checked" in file)) {
                    this.createReader(file.raw, (message) => {
                        this.$message.error(message);
                        files.splice(-1);
                    }, () => {
                        this.checked = true;
                        this.$refs.upload.submit();
                    });
                }
            },

            saveExample: function () {
                let exampleData = {
                    'department_id': this.messageSettingForm.department_id,
                    'name': this.messageSettingForm.name,
                    'content': this.messageSettingForm.content
                };

                axios.post('/message/setting/saveExample', exampleData).then((response) => {
                    alert('存成範本成功');
                }).catch((error) => {
                    alert('存成範本失敗');
                });
            },

            verify: function () {
                if (!this.messageSettingForm.name) {
                    Tools.Dialog(this).openWarning(null, '業務名稱為必填欄位');

                    return false;
                }

                if (!this.messageSettingForm.content) {
                    Tools.Dialog(this).openWarning(null, '推播內容為必填欄位');

                    return false;
                }

                if (this.messageSettingForm.send_time_type == 2 &&
                    !this.messageSettingForm.everyday_time
                ) {
                    Tools.Dialog(this).openWarning(null, '每天固定發送時間為必填欄位');

                    return false;
                }

                if (parseInt(this.messageSettingForm.send_time_type) === 3 &&
                    !this.messageSettingForm.set_datetime
                ) {
                    Tools.Dialog(this).openWarning(null, '指定日期與時間為必填欄位');

                    return false;
                }

                if (parseInt(this.messageSettingForm.send_time_type) === 3 &&
                    this.messageSettingForm.set_datetime && new Date(this.messageSettingForm.set_datetime) < new Date()
                ) {
                    Tools.Dialog(this).openWarning(null, '指定日期與時間不能小於當前時間');

                    return false;
                }

                if (!this.messageSettingForm.send_count) {
                    Tools.Dialog(this).openWarning(null, '推播次數為必填欄位');

                    return false;
                } else {
                    if (parseInt(this.messageSettingForm.send_count) === 0) {
                        Tools.Dialog(this).openWarning(null, '推播次數欄位至少為1次');

                        return false;
                    }

                    if (this.messageSettingForm.send_time_type !== '2') {
                        if (this.messageSettingForm.send_count > 1 && !this.messageSettingForm.space_time) {
                            Tools.Dialog(this).openWarning(null, '間隔時間為必填欄位');

                            return false;
                        }
                    }
                }

                if (parseInt(this.messageSettingForm.valid_time_type) === 1 && !this.messageSettingForm.end_time) {
                    Tools.Dialog(this).openWarning(null, '有效時間為必填欄位');

                    return false;
                }

                if (parseInt(this.messageSettingForm.valid_time_type) === 2 && !this.messageSettingForm.end_datetime) {
                    Tools.Dialog(this).openWarning(null, '有效時間為必填欄位');

                    return false;
                }

                if (this.messageSettingForm.send_time_type == '3') {
                    let send_time = this.messageSettingForm.set_datetime;

                    if (this.messageSettingForm.valid_time_type == 2) {
                        let valid_time = this.messageSettingForm.end_datetime;

                        if (send_time > valid_time) {
                            this.$alert('有效時間不得早於推播時間', '提示', {
                                confirmButtonText: '確定'
                            });

                            return false;
                        }
                    }
                }

                if (this.messageSettingForm.is_question == true && this.messageSettingForm.question_id == 0) {
                    this.$alert('尚未選擇要夾帶的問卷', '提示', {
                        confirmButtonText: '確定'
                    });

                    return false;
                }

                if (this.messageSettingForm.is_activity == true && this.messageSettingForm.activity_id == 0) {
                    this.$alert('尚未選擇要夾帶的活動', '提示', {
                        confirmButtonText: '確定'
                    });

                    return false;
                }

                if (this.messageSettingForm.send_object == '3' && this.messageSettingForm.send_group_id == 0) {
                    this.$alert('請選擇群組', '提示', {
                        confirmButtonText: '確定'
                    });

                    return false;
                }

                if (this.messageSettingForm.is_survey === false || this.messageSettingForm.setting_gold === undefined) {
                    this.messageSettingForm.setting_gold = 0;
                    this.messageSettingForm.stage_id = '0';
                    this.messageSettingForm.person_gold = 0;
                    this.messageSettingForm.person_limit = 0;
                }

                return true;
            },

            submitSaveSendMessage: function () {
                this.action = 2;
                this.message = '推播此則訊息';
                if (!this.verify()) {
                    return;
                }

                if (parseInt(this.messageSettingForm.send_object) === 2) {
                    this.sendMessageVisible = true;
                    return false;
                }

                this.send(this.message);
            },

            send: function (actionName) {
                let that = this;
                const h = this.$createElement;

                console.log(that.messageSettingForm)

                this.$refs.messageSettingForm.validate(function (result) {
                    if (result) {
                        this.$msgbox({
                            title: '提示',
                            message: h('p', null, [
                                h('span', null, '確定要' + actionName),
                                h('i', {style: 'color: teal'}, that.messageSettingForm.name),
                                h('span', null, '嗎？')
                            ]),
                            showCancelButton: true,
                            confirmButtonText: '確定',
                            cancelButtonText: '取消',
                            beforeClose: (action, instance, done) => {
                                if (action === 'confirm') {
                                    instance.confirmButtonLoading = true;
                                    instance.confirmButtonText = '創建中...';

                                    let url = '/message/send/updateSend';
                                    axios.post(url, that.messageSettingForm)
                                        .then(function (response) {
                                            if (response.data.code === 0) {
                                                that.$refs.messageSettingForm.resetFields();
                                                that.sendMessageVisible = false;
                                                that.addMessageSettingVisible = false;
                                                instance.confirmButtonLoading = false;
                                                that.dealMessageSuccess();
                                                setTimeout(() => {
                                                    done();
                                                }, 1000);
                                            } else {
                                                that.dealMessageWarning()
                                                done();
                                                instance.confirmButtonLoading = false
                                            }
                                        }).catch(function (error) {
                                        console.error(error)
                                        that.dealMessageWarning();
                                        done()
                                        instance.confirmButtonLoading = false
                                    });
                                    instance.confirmButtonLoading = false;
                                } else {
                                    done();
                                }
                            }
                        });
                    } else {
                        console.log(result);
                    }
                }.bind(this));
            },

            insertContent: function () {
                this.messageSettingForm.content = this.messageSettingForm.content + this.messageSettingForm.content_id + ':';
            },

            timeEverydayChange: function () {
                this.messageSettingForm.send_time_type = '2';
            },

            timeDateTimeChange: function () {
                this.messageSettingForm.send_time_type = '3';
            },

            timeValidateChange: function () {
                this.messageSettingForm.valid_time_type = '2';
            },

            changeLoadStatus: function (status) {
                this.loading = status;
            },
            changeUnit: function (val) {
                let that = this;
                that.unit_id = val;

                axios.get('/gold/send/getRemainGold?stage_id=' + that.messageSettingForm.stage_id + '&unit_id=' + that.unit_id)
                    .then(function (response) {
                        that.remain_gold = response.data.response.remain_gold;

                        if (that.remain_gold === undefined) {
                            that.remain_gold = 0;
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });

                that.setStageGold(that.unit_id);
            },
            recycleGold: function (id, remain_gold) {
                let that = this;
                let h = this.$createElement;
                this.dialog.openSelfDialog((closeCallback) => {
                    axios.get('/message/send/recycleGold?id=' + id + '&remain_gold=' + remain_gold)
                        .then(function (response) {
                            if (response.data.code == 0) {
                                that.loadData(that.searchForm.page);
                            }
                            closeCallback()
                        })
                        .catch(function (error) {
                            console.log(error);
                            closeCallback()
                        });
                }, h('p', null, [
                    h('span', null, '確定要回收嗎?')
                ]), '執行中...', '確定');

            },
            openMember: function (row) {
                Tools.OpenNewWindow(
                    "/#/message/readGroupMembers/" + row.id + "/" + row.send_group_id,
                    "群組成員名單",
                    800,
                    1024
                );
            },
            openSatisfaction: function (row) {
                Tools.OpenNewWindow(
                    "/#/message/satisfaction/" + row.id + "/" + row.survey_id + "?title=" + row.name + "&send_time=" + row.send_time,
                    "滿意度回饋清冊",
                    800,
                    1024
                );
            },
            onlineRegistration: function (activityId) {
                Tools.OpenNewWindow('/#/activity/online/' + activityId, '活動報名列表', window.screen.height - 200, window.screen.width - 100);
            },
        }
    }
</script>

<style scoped>
    .el-checkbox-group {
        font-size: 12px;
    }

    .hide-dialog {
        display: none;
    }
</style>
