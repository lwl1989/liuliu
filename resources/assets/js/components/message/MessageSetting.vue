<template>
    <div id="app">
        <el-row>
            <el-col :span="24">
                <div style="padding-top:5px;">
                    <el-col :span="100">
                        <el-form :inline="true" :model="searchForm" :rules="searchRules" ref="searchForm"
                                 class="demo-form-inline">
                            <el-form-item label="推播對象" prop="send_object">
                                <el-select v-model="searchForm.send_object" placeholder="所有用戶">
                                    <el-option label="所有用戶" value="1"></el-option>
                                    <el-option label="個人" value="2"></el-option>
                                    <el-option label="指定群組" value="3"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="推播時間" prop="send_time_type">
                                <el-select v-model="searchForm.send_time_type" placeholder="立即">
                                    <el-option label="立即" value="1"></el-option>
                                    <!--<el-option label="每天固定幾點傳送" value="2"></el-option>-->
                                    <el-option label="指定日期與時間" value="3"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="searchForm.search_type" placeholder="業務單位">
                                    <el-option label="業務單位" value="1"></el-option>
                                    <el-option label="業務名稱" value="2"></el-option>
                                    <el-option label="設定內容" value="3"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-input v-model="searchForm.search_type_key" @keyup.enter.native='loadData(1)'
                                          auto-complete="off"></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-select v-model="searchForm.search_time_type" placeholder="最後異動日期">
                                    <el-option label="最後異動日期" value="1"></el-option>
                                    <!--<el-option label="新增日期" value="2"></el-option>-->
                                </el-select>
                            </el-form-item>
                            <el-form-item>
                                <el-date-picker
                                        v-model="searchForm.time"
                                        type="datetimerange"
                                        range-separator="~"
                                        start-placeholder="開始日期"
                                        end-placeholder="結束日期"
                                        align="right">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary" icon="el-icon-search" @click="loadData(1)">查詢</el-button>
                            </el-form-item>
                        </el-form>
                    </el-col>
                    <el-col :span="8">
                        <el-button size='small' type="primary" class="el-icon-circle-plus" @click="addMessageSetting">
                            新增訊息設定
                        </el-button>
                        <el-tooltip content="傳送到 3.2 推播訊息排程推播" placement="top" effect="dark">
                            <el-button size='small' type="primary" class="el-icon-circle-plus" @click="sendMessage" v-if="!clickSend">
                                推播訊息
                            </el-button>
                            <el-button size='small' type="primary" class="el-icon-circle-plus" style="color: #ac2925" v-else>
                                推播訊息
                            </el-button>
                        </el-tooltip>
                        <el-button size='small' type="primary" class="el-icon-remove" @click="deleteMessageSetting">
                            刪除訊息設定
                        </el-button>
                    </el-col>
                </div>
            </el-col>
            <el-col :span="24" style="margin-top: 20px;">
                <el-table :data="messageSettings" stripe v-loading='loading' @selection-change="handleSelectionChange">
                    <el-table-column type="selection">
                    </el-table-column>
                    <el-table-column prop="business_name" label="業務單位">
                    </el-table-column>
                    <el-table-column prop="name" label="業務名稱" show-overflow-tooltip>
                        <template slot-scope="scope">
                            <a href="javascript:void(0)" style="text-decoration:none;color: #00afff;"
                               @click="editMessage(scope.row.id)">{{ scope.row.name }}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="send_object_tag" label="推播對象">
                    </el-table-column>
                    <el-table-column prop="question_title" label="問卷設定" show-overflow-tooltip>
                    </el-table-column>
                    <el-table-column prop="activity_name" label="活動設定" show-overflow-tooltip>
                    </el-table-column>
                    <el-table-column prop="survey" label="調查設定" show-overflow-tooltip>
                    </el-table-column>
                    <el-table-column prop="admin_info" label="最後異動資訊">
                    </el-table-column>
                </el-table>
            </el-col>
            <el-col :span="24">
                <div class="app-pagination">
                    <el-pagination
                            :page-sizes="[10, 20, 30, 50, 100]"
                            @size-change="limitChange"
                            @current-change="loadData"
                            :current-page="searchForm.page"
                            :page-size="searchForm.limit"
                            layout="sizes, total, prev, pager, next"
                            :total="total">
                    </el-pagination>
                </div>
            </el-col>
        </el-row>

        <el-dialog :title="dialogTitle" :visible.sync="addMessageSettingVisible" :modal-append-to-body="false"
                   :close-on-click-modal="false" class="dialog-import" @close="closeDialog">
            <el-radio-group v-model="labelChoose">
                <el-radio-button label="message">推播訊息主要設定</el-radio-button>
                <el-radio-button label="questionnaire">活動及問卷調查設定</el-radio-button>
                <el-radio-button label="period">推播週期設定</el-radio-button>
            </el-radio-group>
            <div style="margin: 20px;"></div>

            <el-form :model="messageSettingForm" :rules="rules" ref="messageSettingForm">
                <div v-if="labelChoose === 'message'">
                    <el-form-item label="業務單位" :label-width="formLabelWidth" prop="department_id">
                        <el-select v-model="messageSettingForm.department_id" placeholder="請選擇" @change="changeUnit">
                            <template v-for="(item, index) in unit_sum_array">
                                <el-option :label="item.name" :value="item.id" :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="業務名稱" :label-width="formLabelWidth" prop="name">
                        <el-col :span="10">
                            <el-input v-model="messageSettingForm.name" auto-complete="off"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-form-item label="推播對象" :label-width="formLabelWidth" prop="send_object">
                        <el-select v-model="messageSettingForm.send_object" placeholder="所有用戶">
                            <el-option label="所有用戶" value="1"></el-option>
                            <el-option label="個人" value="2"></el-option>
                            <el-option label="指定群組" value="3"></el-option><!--需顯示縣民群組-->
                        </el-select>
                        <el-select v-model="messageSettingForm.send_group_id" placeholder="指定群組"
                                   v-if="messageSettingForm.send_object == 3">
                            <template v-for="item in group_sum">
                                <el-option :label="item.name" :value="item.id" :key="item.id"></el-option>
                            </template>
                        </el-select>

                    </el-form-item>
                    <el-form-item label="推播內容" :label-width="formLabelWidth" prop="content">
                        <el-input type="textarea" :autosize="{ minRows: 3, maxRows: 4}"
                                  v-model="messageSettingForm.content"></el-input>
                        <!--<i class="el-icon-upload2" style="color: #0000F0" @click="insertContent"></i>-->
                        <!--<el-select v-model="messageSettingForm.content_id" placeholder="">-->
                        <!--<el-option label="發起人" value="發起人"></el-option>-->
                        <!--<el-option label="申請人" value="申請人"></el-option>-->
                        <!--<el-option label="會議日期" value="會議日期"></el-option>-->
                        <!--</el-select>-->
                        <!--<div style="margin: 20px;"></div>-->
                        <!--<el-button size="small" type="primary" @click="saveExample">存成範本</el-button>-->
                        <!--（請編輯您所要推播內容，若選擇自動帶入，於發送訊息時，自動帶入將轉換為實際資料內容）-->
                    </el-form-item>
                    <el-form-item label="發送圖片" :label-width="formLabelWidth" prop="image">
                        <el-upload
                                action="#"
                                :multiple=false
                                :limit=1
                                accept=".jpg,.jpeg,.png,.PNG,.JPG,.JPEG,.gif"
                                ref="upload"
                                :on-remove="handleRemove"
                                :file-list="messageSettingForm.image"
                                :on-success="handleSuccess"
                                list-type="picture"
                                :http-request="HouImgFile"
                                ><!--:on-change="handleFileChange" -->
                            <el-button size="small" type="primary">選擇檔案</el-button>
                            <div slot="tip" class="el-upload__tip">
                                僅限.jpg .jpeg .gif .png格式，只能夾帶一個檔案，建議尺寸為 1110*663，檔案大小不可超過300K
                            </div>
                        </el-upload>
                    </el-form-item>
                </div>

                <div v-if="labelChoose === 'questionnaire'">
                    <el-form-item label="問卷設定" :label-width="formLabelWidth" prop="question_unit_id">
                        <el-checkbox v-model="messageSettingForm.is_question">夾帶問卷</el-checkbox>
                        <div style="margin: 20px;"></div>
                        <el-select v-model="messageSettingForm.question_unit_id" @change="changeQuestionUnit">
                            <template v-for="(item) in unit_sum_array">
                                <el-option :label="item.name" :value="item.id.toString()"
                                           :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                        <div style="margin: 20px;"></div>
                        <el-select v-model="messageSettingForm.question_id">
                            <el-option key="0" value="0" label="請選擇"></el-option>
                            <template v-for="(item) in question_sum">
                                <el-option :label="item.name" :value="item.id.toString()"
                                           :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                        <el-col :span="20" style="color: #409EFF;">
                            提醒您先至3.3建立問卷
                        </el-col>
                    </el-form-item>
                    <el-form-item label="活動設定" :label-width="formLabelWidth" prop="activity_unit_id">
                        <el-checkbox v-model="messageSettingForm.is_activity">夾帶活動</el-checkbox>
                        <div style="margin: 20px;"></div>
                        <el-select v-model="messageSettingForm.activity_unit_id" @change="changeActivityUnit">
                            <template v-for="(item) in unit_sum_array">
                                <el-option :label="item.name" :value="item.id.toString()"
                                           :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                        <div style="margin: 20px;"></div>
                        <el-select v-model="messageSettingForm.activity_id" placeholder="">
                            <el-option key="0" value="0" label="請選擇"></el-option>
                            <template v-for="(item) in activity_sum">
                                <el-option :label="item.name" :value="item.id.toString()"
                                           :key="item.id.toString()"></el-option>
                            </template>
                        </el-select>
                        <el-col :span="20" style="color: #409EFF;">
                            提醒您先至3.4建立活動
                        </el-col>
                    </el-form-item>
                    <el-form-item label="調查設定" :label-width="formLabelWidth" prop="is_survey">
                        <div>
                            <el-checkbox v-model="messageSettingForm.is_survey">夾帶滿意度調查</el-checkbox>
                            <el-button v-show='isShowGoldBut' style='margin:0 0 0 15px;' type="primary" size="mini"
                                       @click="showSurveySetting">滿意度金幣
                            </el-button>
                        </div>
                    </el-form-item>
                </div>

                <div v-if="labelChoose === 'period'">
                    <el-form-item label="推播時間與次數" :label-width="formLabelWidth" prop="send_time_type">
                        <template>
                            <!--<el-col :span="5">-->
                            <el-input v-model="messageSettingForm.send_count" auto-complete="off"
                                      style="width: 80px"></el-input>
                            次<span v-show="is_show">，每次間隔
                            <!--</el-col>-->
                            <!--<el-col :span="10">-->
                            <el-input v-model="messageSettingForm.space_time" auto-complete="off"
                                      style="width: 80px"></el-input>
                            <!--</el-col>-->
                            <el-select v-model="messageSettingForm.space_time_type" placeholder="" style="width: 80px">
                                <el-option label="天" value="1"></el-option>
                                <el-option label="時" value="2"></el-option>
                                <el-option label="分" value="3"></el-option>
                            </el-select></span>
                            <div style="margin: 10px;"></div>
                            <el-radio v-model="messageSettingForm.send_time_type" label="1">立即傳送</el-radio>
                            <div style="margin: 10px;"></div>
                            <!--<el-radio v-model="messageSettingForm.send_time_type" label="2">每天固定於-->
                            <!--<el-time-picker-->
                            <!--type="time"-->
                            <!--placeholder="選擇時間"-->
                            <!--v-model="messageSettingForm.everyday_time"-->
                            <!--@change="timeEverydayChange"-->
                            <!--format="HH:mm"-->
                            <!--value-format="HH:mm"-->
                            <!--style="width: 50%;">-->
                            <!--</el-time-picker>傳送-->
                            <!--</el-radio>-->
                            <!--<div style="margin: 10px;"></div>-->
                            <el-radio v-model="messageSettingForm.send_time_type" label="3">指定日期與時間
                                <el-date-picker
                                        v-model="messageSettingForm.set_datetime"
                                        type="datetime"
                                        placeholder="選擇日期時間"
                                        @change="timeDateTimeChange"
                                >
                                </el-date-picker>
                            </el-radio>
                        </template>
                    </el-form-item>
                    <!--<el-form-item label="推播次數" :label-width="formLabelWidth" prop="send_count">-->
                    <!--<el-col :span="10">-->
                    <!--<el-input v-model="messageSettingForm.send_count" auto-complete="off"></el-input>-->
                    <!--</el-col>次-->
                    <!--</el-form-item>-->
                    <!--<el-form-item v-show="messageSettingForm.send_time_type != '2'" label="間隔時間" :label-width="formLabelWidth" prop="space_time">-->
                    <!--<el-col :span="10">-->
                    <!--<el-input v-model="messageSettingForm.space_time" auto-complete="off"></el-input>-->
                    <!--</el-col>-->
                    <!--<el-select v-model="messageSettingForm.space_time_type" placeholder="">-->
                    <!--<el-option label="天" value="1" ></el-option>-->
                    <!--<el-option label="時" value="2" ></el-option>-->
                    <!--<el-option label="分" value="3" ></el-option>-->
                    <!--</el-select>-->
                    <!--</el-form-item>-->
                    <el-form-item label="有效時間" :label-width="formLabelWidth" prop="valid_time_type">
                        <template>
                            <el-radio v-model="messageSettingForm.valid_time_type" label="1">於
                                <el-input v-model="messageSettingForm.end_time" auto-complete="off"
                                          style="width: 30%"></el-input>
                                <el-select v-model="messageSettingForm.end_time_type" placeholder=""
                                           style="width: 80px">
                                    <el-option label="天" value="1"></el-option>
                                    <el-option label="時" value="2"></el-option>
                                    <el-option label="分" value="3"></el-option>
                                </el-select>
                                後停止
                            </el-radio>
                            <div style="margin: 10px;"></div>
                            <el-radio v-model="messageSettingForm.valid_time_type" label="2">於
                                <el-date-picker
                                        v-model="messageSettingForm.end_datetime"
                                        type="datetime"
                                        placeholder="選擇日期時間"
                                        @change="timeValidateChange"
                                >
                                </el-date-picker>
                                後停止
                            </el-radio>
                        </template>
                    </el-form-item>
                </div>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button type="primary" @click="submitSaveMessage('儲存')">儲存</el-button>
                <el-button type="primary" @click="submitSaveSendMessage('推播此則訊息')">儲存並推播</el-button>
                <el-button type="primary" @click="closeDialog">取消</el-button>
            </div>
        </el-dialog>

        <el-dialog title="金幣發放" :visible.sync="showGoldGrant" :modal-append-to-body="false"
                   :close-on-click-modal="false">
            <el-form label-width="200px">
                <el-form-item label="發放期別(發放日/有效期限)">
                    <el-select v-model="messageSettingForm.stage_id" @change="getRemainGold" style="width: 350px;"
                               v-bind:disabled="goldsEdit">
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
                        <el-input-number
                                v-model="messageSettingForm.person_gold"
                                :min="0"
                                v-bind:disabled="goldsEdit"
                        ></el-input-number>
                    </el-col>
                    <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                    <el-col :span="20"></el-col>
                    <el-col style="margin:5px 0 0 0;" class="line" :span="5" :offset="0" prop="fans_number">限制前</el-col>
                    <el-col style="margin:5px 0 0 0;" :span="6">
                        <el-input-number
                                v-model="messageSettingForm.person_limit"
                                auto-complete="off"
                                :min="goldsEdit ? messageSettingForm.person_limit : 0"
                                v-bind:disabled="overdue"
                        ></el-input-number>
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
                <el-button type="primary" @click="goldSubmit()">確 定</el-button>
            </div>
        </el-dialog>

        <el-dialog title="選擇對象" :visible.sync="sendMessageVisible" :label-width="formLabelWidth">
            <el-form ref="sendMessageForm" :model="searchForm">
                <div style="margin-top: 10px;">
                    <el-form-item label="推播對象:" :label-width="formLabelWidth">
                        <el-input placeholder="請輸入內容" v-model="sendMessageForm.receive_key" style="width: 90%;">
                            <el-select v-model="sendMessageForm.receive_type" slot="prepend" placeholder="請選擇"
                                       style="width: 130px;">
                                <el-option label="手機號碼" value="1"></el-option>
                                <el-option label="身份證字號" value="2"></el-option>
                            </el-select>
                            <el-button slot="append" icon="el-icon-search" @click="searchReceive"></el-button>
                        </el-input>
                        <el-form label-width="90px" v-show="receiveVisible">
                            <el-form-item label="姓名">
                                {{sendMessageForm.receive_name}}
                            </el-form-item>
                            <el-form-item label="身份證字號">
                                {{sendMessageForm.receive_id_number}}
                            </el-form-item>
                            <el-form-item label="手機號碼">
                                {{sendMessageForm.receive_phone}}
                            </el-form-item>
                        </el-form>
                    </el-form-item>
                </div>
                <div style="margin-top: 10px;" :visible.sync="agentVisible">
                    <el-form-item label="代    理   人     :" prop="receive_id" :label-width="formLabelWidth">
                        <el-input placeholder="請輸入內容" v-model="sendMessageForm.agent_key" style="width: 90%;">
                            <el-select v-model="sendMessageForm.agent_type" slot="prepend" placeholder="請選擇"
                                       style="width: 130px;">
                                <el-option label="手機號碼" value="1"></el-option>
                                <el-option label="身份證字號" value="2"></el-option>
                            </el-select>
                            <el-button slot="append" icon="el-icon-search" @click="searchAgent"></el-button>
                        </el-input>
                        <el-form label-width="90px" v-show="agentVisible">
                            <el-form-item label="姓名">
                                {{sendMessageForm.agent_name}}
                            </el-form-item>
                            <el-form-item label="身份證字號">
                                {{sendMessageForm.agent_id_number}}
                            </el-form-item>
                            <el-form-item label="手機號碼">
                                {{sendMessageForm.agent_phone}}
                            </el-form-item>
                        </el-form>
                    </el-form-item>
                </div>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="sendMessageVisible = false">取消</el-button>
                <el-button type="primary" @click="submitSendMessage('確認推播')">確定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import {MessageSettingRule, MessageSettingSearchRule} from '../../tools/element-ui-validate';
    import Tools from "../../tools/vue-common-tools";
    import lrz from 'lrz';

    export default {
        name: "message-setting",

        data: function () {
            return {
                total: 1,
                remain_gold: 0,
                send_stage: [],
                totalSendGold: 0,
                showGoldGrant: false,
                isShowGoldBut: false,
                isSubmit : false,
                messageSettings: [],
                sendMessageVisible: false,
                addMessageSettingVisible: false,
                dialogTitle: '新增訊息設定',
                formLabelWidth: '150px',
                labelChoose: 'message',
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
                    image: [],
                    send_time_type: 1,
                    everyday_time: '',
                    set_datetime: '',
                    send_count: 1,
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
                    survey_id: '1',
                    stage_id: '0',
                    person_gold: 0,
                    person_limit: 0,
                    sent_gold: 0,
                    setting_gold: 0
                },
                unit_sum: this.getUnitSum(),
                sendMessageForm: {
                    receive_type: '1',
                    receive_key: '',
                    receive_name: '',
                    receive_id_number: '',
                    receive_phone: '',
                    agent_type: '1',
                    agent_key: '',
                    agent_name: '',
                    agent_id_number: '',
                    agent_phone: '',
                    receive_user_id: '',
                    agent_user_id: ''
                },
                searchForm: {
                    page: 1,
                    time: [
                        new Date(new Date().getTime() - 3600 * 1000 * 24 * 30),
                        new Date()
                    ],
                    date_start: '',
                    date_end: '',
                    limit: 10,
                    send_object: '',
                    send_time_type: '',
                    search_type: '1',
                    search_type_key: '',
                    search_time_type: '1'
                },
                searchRules: MessageSettingSearchRule,
                rules: MessageSettingRule,
                multipleSelection: [],
                count: 0,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                importFlag: 1,
                name: 'file',
                fileList: [],
                processing: false,
                uploadTip: '選擇檔案',
                errorResults: [],
                importUrl: '/message/setting/file',
                except_width: 1110,
                except_height: 663,
                data: [],
                business_unit_sum: '',
                content_sum: [],
                receiveVisible: false,
                agentVisible: false,
                action: 1,
                message: '',
                loading: true,
                group_sum: [],
                question_sum: [],
                activity_sum: [],
                unit_sum_array: [],
                is_show: false,
                dialog: new Tools.Dialog(this),
                goldsEdit: false,
                clickSend : false,
                overdue : false
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

            'messageSettingForm.is_survey': function (value) {
                this.isShowGoldBut = value;
            },

            'messageSettingForm.person_gold': function (val) {
                let re = /^[0-9]+[0-9]*$/;
                if (this.showGoldGrant && !re.test(val)) {
                    this.messageSettingForm.person_gold = '';
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    return false;
                }

                this.setTotalSendGold('');
            },

            'messageSettingForm.person_limit': function (val) {
                let re = /^[0-9]+[0-9]*$/;
                if (this.showGoldGrant && !re.test(val)) {
                    this.messageSettingForm.person_limit = '';
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    return false;
                }

                this.setTotalSendGold('');
            },
            'messageSettingForm.send_count': function (value) {
                if (parseInt(value) > 1) {
                    this.is_show = true;
                } else {
                    this.is_show = false;
                }
            },
            'sendMessageForm.receive_key': function () {
                this.receiveVisible = false;
                this.sendMessageForm.receive_name = ''
                this.sendMessageForm.receive_id_number = ''
                this.sendMessageForm.receive_phone = ''
                this.sendMessageForm.receive_user_id = ''
            },
            'sendMessageForm.agent_key': function () {
                this.agentVisible = false;
                this.sendMessageForm.agent_name = ''
                this.sendMessageForm.agent_id_number = ''
                this.sendMessageForm.agent_phone = ''
                this.sendMessageForm.agent_user_id = ''
            }
        },

        created: function () {
            this.loadData(1);
        },

        methods: {
            getUnitSum: function () {
                let that = this;
                axios.get('/department/getAllUnit')
                    .then(function (response) {
                        for (let key in response.data.response) {
                            that.unit_sum_array.push({id: key, name: response.data.response[key]});
                        }

                        // 沒有選擇單位，不應有問卷和活動
                        // axios.get('/question/getAllName?department_id=' + parseInt(that.unit_sum_array[0].id))
                        //     .then(function (response) {
                        //         if (response.data.response !== []) {
                        //             that.question_sum = [];
                        //             that.messageSettingForm.question_id = '0';
                        //
                        //             for (let key in response.data.response) {
                        //                 that.question_sum.push({id:key,name:response.data.response[key]});
                        //             }
                        //             if(that.question_sum.length > 0 && that.messageSettingForm.question_id === '0') {
                        //                 that.messageSettingForm.question_id = that.question_sum[0].id;
                        //             }
                        //         } else {
                        //             that.question_sum.push({id:'0',name:''});
                        //         }
                        //     })
                        //     .catch(function (error) {
                        //         console.log(error);
                        //     });
                        //
                        // axios.get('/activity/getAllName?department_id=' + parseInt(that.unit_sum_array[0].id))
                        //     .then(function (response) {
                        //         if (response.data.response !== []) {
                        //             that.activity_sum = [];
                        //             that.messageSettingForm.activity_id = '0';
                        //
                        //             for (let key in response.data.response) {
                        //                 that.activity_sum.push({id:key,name:response.data.response[key]});
                        //             }
                        //             if(that.activity_sum.length > 0 && that.messageSettingForm.activity_id === '0') {
                        //                 that.messageSettingForm.activity_id = that.activity_sum[0].id;
                        //             }
                        //         } else {
                        //             that.activity_sum.push({id:'0',name:''});
                        //         }
                        //     })
                        //     .catch(function (error) {
                        //         console.log(error);
                        //     });

                        axios.get('/message/setting/getGroupId')
                            .then(function (response) {
                                if (response.data.response) {
                                    for (let key in response.data.response) {
                                        that.group_sum.push({id: key, name: response.data.response[key]});
                                    }
                                }
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    })
                    .catch(function (error) {
                        console.log(error);
                    });

            },

            changeQuestionUnit: function (val) {
                let that = this;

                axios.get('/question/getAllName?department_id=' + val)
                    .then(function (response) {
                        if (response.data.response != []) {
                            that.question_sum = [];
                            let questionId =  that.messageSettingForm.question_id
                            let contain = false
                            for (let key in response.data.response) {
                                if(questionId>0&&questionId==key){
                                    contain = true;
                                }
                                that.question_sum.push({id: key, name: response.data.response[key]});
                            }
                            if(questionId>0&&!contain){
                                that.messageSettingForm.question_id = '0'
                                that.dialog.openError(null, '夾帶的問卷已過期，請重新設定');
                            }
                        } else {
                            that.question_sum.push({id: '0', name: ''});
                            let questionId =  that.messageSettingForm.question_id
                            if(questionId>0){
                                that.messageSettingForm.question_id = '0'
                                that.dialog.openError(null, '夾帶的問卷已過期，請重新設定');
                            }
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },

            changeActivityUnit: function (val) {
                let that = this;

                axios.get('/activity/getAllName?department_id=' + val)
                    .then(function (response) {
                        if (response.data.response != []) {
                            that.activity_sum = [];
                            let activityId =  that.messageSettingForm.activity_id
                            let contain = false
                            for (let key in response.data.response) {
                                if(activityId>0&&activityId==key){
                                    contain=true
                                }
                                that.activity_sum.push({id: key, name: response.data.response[key]});
                            }
                            if(activityId>0&&!contain){
                                that.messageSettingForm.activity_id = '0'
                                that.dialog.openError(null, '夾帶的活動已過期，請重新設定');
                            }
                        } else {
                            that.activity_sum.push({id: '0', name: ''});
                            let activityId =  that.messageSettingForm.activity_id
                            if(activityId>0){
                                that.messageSettingForm.activity_id = '0'
                                that.dialog.openError(null, '夾帶的活動已過期，請重新設定');
                            }
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
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

                return true;
            },

            submitSaveMessage() {
                this.action = 1;
                this.message = '儲存';
                if (!this.verify()) {
                    return;
                }

                if (this.messageSettingForm.send_object == 2) {
                    this.sendMessageVisible = true;

                    return false;
                }

                this.save(this.message);
            },

            submitSaveSendMessage: function () {
                this.action = 2;
                this.message = '推播此則訊息';
                if (!this.verify()) {
                    return;
                }

                if (this.messageSettingForm.send_object == 2) {
                    this.sendMessageVisible = true;

                    return false;
                }

                this.send(this.message);
            },

            save: function (actionName) {
                const h = this.$createElement;
                let that = this
                this.$refs.messageSettingForm.validate(result => {
                    if (result) {
                        Tools.Dialog(this).openSelfDialog(callback => {
                            if(that.isSubmit){
                                return
                            }
                            that.isSubmit=true
                            axios.post('/message/setting/save', this.messageSettingForm).then(response => {
                                if (response.data.code === 0) {
                                    this.$refs.messageSettingForm.resetFields();
                                    this.dealMessageSuccess();
                                    this.closeDialog();

                                    callback();
                                } else {
                                    this.dealMessageWarning()
                                }
                            }).catch(() => {
                                this.dealMessageWarning();
                            });
                        }, h('p', null, [
                            h('span', null, '確定要' + actionName),
                            h('span', {style: 'color: teal'}, this.messageSettingForm.name),
                            h('span', null, ' 嗎？')
                        ]), '創建中...');
                    }
                });
            },

            send: function (actionName) {
                const h = this.$createElement;
                let that = this
                this.$refs.messageSettingForm.validate(result => {
                    if (result) {
                        console.log(this.messageSettingForm);
                        console.log(this.messageSettingForm.setting_gold);

                        if (this.messageSettingForm.setting_gold == undefined) {
                            this.messageSettingForm.setting_gold = 0;
                        }

                        this.messageSettingForm.is_empty_token = true;
                        axios.post('/message/setting/saveSend', this.messageSettingForm).then(response => {
                            console.log(response);
                            //群組若有未登入用戶加彈框提示
                            if (response.data.response.id === -2) {
                                Tools.Dialog(this).openSelfDialog(callback => {
                                    if (that.isSubmit){
                                        return
                                    }
                                    that.isSubmit=true
                                    this.messageSettingForm.is_empty_token = false;
                                    axios.post('/message/setting/saveSend', this.messageSettingForm).then(response => {
                                        if (response.data.response.id > 0) {
                                            this.$refs.messageSettingForm.resetFields();
                                            this.dealMessageSuccess();
                                            this.closeDialog();

                                            callback();
                                        } else if(response.data.response.id === -1) {
                                            Tools.Dialog(this).openError(null, '推播的縣民群組已刪除,請重新設定');
                                        } else {
                                            this.dealMessageWarning()
                                        }
                                    }).catch(() => {
                                        this.dealMessageWarning();
                                    });
                                }, h('p', null, [
                                    h('span', null, '您指定的發送對象中有用戶未登入APP，是否繼續發送？未登入的用戶將不會收到推播訊息。')
                                ]), '創建中...');
                            } else {
                                Tools.Dialog(this).openSelfDialog(callback => {
                                    if(that.isSubmit){
                                        return
                                    }
                                    that.isSubmit=true;
                                    this.messageSettingForm.is_empty_token = false;
                                    axios.post('/message/setting/saveSend', this.messageSettingForm).then(response => {
                                        if (response.data.response.id > 0) {
                                            this.$refs.messageSettingForm.resetFields();
                                            this.dealMessageSuccess();
                                            this.closeDialog();

                                            callback();
                                        } else if(response.data.response.id === -1) {
                                            Tools.Dialog(this).openError(null, '推播的縣民群組已刪除,請重新設定');
                                        } else {
                                            this.dealMessageWarning()
                                        }
                                    }).catch(() => {
                                        this.dealMessageWarning();
                                    });
                                }, h('p', null, [
                                    h('span', null, '確定要' + actionName),
                                    h('span', {style: 'color: teal'}, this.messageSettingForm.name),
                                    h('span', null, ' 嗎？')
                                ]), '創建中...');
                            }
                        }).catch(() => {
                            this.dealMessageWarning();
                        });
                    }
                });
            },

            submitSendMessage: function () {
                let that = this;
                let receive_key = that.sendMessageForm.receive_key;
                let agent_key = that.sendMessageForm.agent_key;

                if (receive_key == '' && agent_key == '') {
                    Tools.Dialog(this).openWarning(null, '推播對象為必填欄位');
                    return false;
                } else {
                    if (this.sendMessageForm.receive_user_id == '' && this.sendMessageForm.agent_user_id == '') {
                        Tools.Dialog(this).openWarning(null, '尚未選擇推播對象');
                        return false;
                    } else if (agent_key && this.sendMessageForm.agent_user_id == '') {
                        Tools.Dialog(this).openWarning(null, '請查詢代理人');
                        return false;
                    } else if (receive_key && this.sendMessageForm.receive_user_id == '') {
                        Tools.Dialog(this).openWarning(null, '請查詢推播對象');
                        return false;
                    } else {
                        this.messageSettingForm.send_person = [];
                        this.messageSettingForm.send_person.push(this.sendMessageForm.receive_user_id);
                        this.messageSettingForm.send_person.push(this.sendMessageForm.agent_user_id);
                        if (this.action <= 1) {
                            this.save(this.message);
                        } else {
                            this.send(this.message);
                        }
                    }
                }
            },

            dealMessageSuccess: function () {
                this.$emit('success', () => {
                    if (this.searchForm.time) {
                        this.searchForm.time = [
                            new Date(new Date().getTime() - 3600 * 1000 * 24 * 30),
                            new Date()
                        ]
                    }

                    this.loadData(1);
                });
            },

            dealMessageWarning: function () {
                this.$emit('warning', () => {
                    this.loadData(1);
                });
            },

            limitChange: function (limit) {
                this.searchForm.limit = limit;
                this.loadData(1);
            },

            loadData: function (page) {
                this.clickSend = false;

                if (this.searchForm.time && this.searchForm.time.length > 0) {
                    this.searchForm.date_start = this.searchForm.time[0].getTime().toString()
                    this.searchForm.date_end = this.searchForm.time[1].getTime().toString()
                } else {
                    this.searchForm.date_start = this.searchForm.date_end = ''
                }

                let queryString = Tools.BuildQueryString(this.searchForm, page);

                axios.get('/message/setting/count' + queryString)
                    .then((response) => {
                        this.total = response.data.response.count;
                    });

                this.loading = true;
                axios.get('/message/setting/select' + queryString).then((response) => {
                    this.loading = false;
                    this.messageSettings = response.data.response.list;
                    this.business_unit_sum = response.data.response.business_unit_sum;
                }).catch(() => {
                    this.loading = false;
                });
            },

            sendMessage: function () {
                this.clickSend = true;

                if (this.multipleSelection.length === 0) {
                    Tools.Dialog(this).openError(null, '請至少選擇一筆資料');
                    this.clickSend = false;
                    return;
                }

                axios.post('/message/setting/send', {'ids': this.multipleSelection}).then(response => {
                    if (response.data.response.id > 0) {
                        this.dealMessageSuccess();
                        this.closeDialog();
                        this.$message({
                            message: '訊息已傳送到3.2推播訊息排程推播',
                            type: 'success'
                        });
                        if (this.sendMessageForm.time) {
                            this.searchForm.time = [
                                new Date(new Date().getTime() - 3600 * 1000 * 24 * 30),
                                new Date()
                            ]
                        }

                        callback();
                    } else if(response.data.response.id === -1) {
                        Tools.Dialog(this).openError(null, '推播的縣民群組已刪除,請重新設定');
                    } else {
                        Tools.Dialog(this).openError(null, '設定有誤');
                    }
                }).catch(() => {
                    
                });
            },

            closeDialog() {
                this.isSubmit = false;
                this.showGoldGrant = false;
                this.sendMessageVisible = false;
                this.addMessageSettingVisible = false;
            },

            addMessageSetting: function () {
                let that = this;
                that.messageSettingForm = {
                    id: 0,
                    department_id: '',
                    name: '',
                    send_object: '1',
                    send_person: [],
                    send_group_id: '',
                    content: '',
                    content_id: '發起人',
                    send_content: '',
                    image: [],
                    send_time_type: "1",
                    everyday_time: '',
                    set_datetime: '',
                    send_count: 1,
                    space_time: '',
                    space_time_type: '1',
                    valid_time_type: '1',
                    end_time: 1,
                    end_time_type: '1',
                    end_datetime: '',
                    is_question: false,
                    is_activity: false,
                    is_survey: false,
                    question_unit_id: '',
                    question_id: '0',
                    activity_unit_id: '',
                    activity_id: '0',
                    survey_id: '1',
                    stage_id: '0',
                    person_gold: 0,
                    person_limit: 0,
                    sent_gold: 0,
                    setting_gold: 0
                };

                this.sendMessageForm = {
                    receive_type: '1',
                    receive_key: '',
                    receive_name: '',
                    receive_id_number: '',
                    receive_phone: '',
                    agent_type: '1',
                    agent_key: '',
                    agent_name: '',
                    agent_id_number: '',
                    agent_phone: '',
                    receive_user_id: '',
                    agent_user_id: ''
                };

                this.receiveVisible = false;
                this.agentVisible = false;
                this.goldsEdit = false;

                axios.get('/message/setting/getIndex')
                    .then(function (response) {
                        that.business_unit_sum = response.data.response.business_unit_sum;
                    }).catch(function (error) {
                    console.log(error);
                });

                this.dialogTitle = '新增訊息設定';
                this.addMessageSettingVisible = true;
            },

            deleteMessageSetting: function () {
                if (this.multipleSelection.length === 0) {
                    Tools.Dialog(this).openError(null, '請至少選擇一筆資料');
                    return;
                }
                let h = this.$createElement;
                let that = this
                this.dialog.openSelfDialog((closeCallback) => {
                    axios.delete('/message/setting/delete', {data: JSON.stringify({id: that.multipleSelection})}).then((response) => {
                        if (response.data.code === 0) {
                            if (response.data.response.row !== undefined && response.data.response.row > 0) {
                                that.dealMessageSuccess();
                                closeCallback();
                            }
                        } else {
                            that.dealMessageWarning();
                            closeCallback();
                        }
                    }).catch((error) => {
                        console.error(error)
                        that.dealMessageWarning();
                        closeCallback();
                    });
                }, h('p', null, [
                    h('span', null, '確定刪除嗎?資料刪除後無法復原。')
                ]), '執行中...', '確定');

            },

            handleSelectionChange: function (rows) {
                let ids = [];

                rows.forEach(function (item) {
                    ids.push(item.id);
                });

                this.multipleSelection = ids;
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

            // createReader: function (file, error, success) {
            //     let reader = new FileReader;
            //     let that = this;
            //     reader.onload = function (evt) {
            //         let image = new Image();
            //         image.onload = function () {
            //             let imageType = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            //             if (imageType.indexOf(file.type) < 0) {
            //                 error('上傳的文件是不正確的文件類型[image/jpg|image/jpeg|image/png|image/gif]');
            //                 return;
            //             }
            //
            //             if (file.size / 1024 > 300) {
            //                 error('上傳的圖片大小不能超過 300K!');
            //                 return;
            //             }
            //
            //             success();
            //         };
            //         image.src = evt.target.result;
            //     };
            //     reader.readAsDataURL(file);
            // },
            //
            // handleFileChange: function (file, files) {
            //     if (!("checked" in file)) {
            //         let that = this;
            //         this.createReader(file.raw, function (message) {
            //             that.$message.error(message);
            //             files.splice(-1);
            //         }, function () {
            //             file.checked = true;
            //             that.$refs.upload.submit();
            //         });
            //     }
            // },

            HouImgFile(e){
                let files = e.file;
                if (!files) return;

                this.createImage(files, e,'HouImg');
            },

            createImage (file, e, Name) {//lrz压缩
                let vm = this;
                console.log(file);
                // lrz图片先压缩在加载
                lrz(file, { width: 1000, height : 480, quality : 0.6}).then(function(rst) {
                    vm.postImage(rst.formData);
                    return rst;
                }).always(function() {
                    // 清空文件上传控件的值
                    e.target.value = null;
                })
            },

            postImage(file){
                let that = this;
                axios.post('/message/setting/image', file)
                    .then(function (res) {
                        that.messageSettingForm.image.push(res.data.response);
                    }).catch(function (error) {
                        alert('失敗');
                });
            },

            saveExample: function () {
                let exampleData = {
                    'department_id': this.messageSettingForm.department_id,
                    'name': this.messageSettingForm.name,
                    'content': this.messageSettingForm.content
                };

                axios.post('/message/setting/saveExample', exampleData)
                    .then(function (response) {
                        Tools.Dialog(this).openSuccess(null, '存成範本成功');
                    }).catch(function (error) {
                    Tools.Dialog(this).openSuccess(null, '存成範本失敗');
                });
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

            searchReceive: function () {
                let that = this;
                let receive_type = that.sendMessageForm.receive_type;
                let receive_key = that.sendMessageForm.receive_key;

                if (receive_key == '') {
                    Tools.Dialog(this).openWarning(null, '請輸入內容');
                    return false;
                }

                axios.get('/message/setting/getUuid?type=' + receive_type + '&key=' + receive_key).then(function (res) {
                    if (res.data.response != '') {
                        that.receiveVisible = true;
                        that.sendMessageForm.receive_name = res.data.response[0].name;
                        that.sendMessageForm.receive_id_number = res.data.response[0].id_number;
                        that.sendMessageForm.receive_phone = res.data.response[0].mobile;
                        that.sendMessageForm.receive_user_id = res.data.response[0].id;
                    } else {
                        Tools.Dialog(that).openWarning(null, '用戶不存在！');

                        that.receiveVisible = false;
                        that.sendMessageForm.receive_name = '';
                        that.sendMessageForm.receive_id_number = '';
                        that.sendMessageForm.receive_phone = '';
                        that.sendMessageForm.receive_user_id = '';
                    }
                }).catch(function (error) {

                });
            },

            searchAgent: function () {
                let that = this;
                let agent_type = that.sendMessageForm.agent_type;
                let agent_key = that.sendMessageForm.agent_key;

                if (agent_key == '') {
                    Tools.Dialog(this).openWarning(null, '請輸入內容');
                    return false;
                }

                axios.get('/message/setting/getUuid?type=' + agent_type + '&key=' + agent_key).then(function (res) {
                    if (res.data.response.length > 0) {
                        that.agentVisible = true;
                        that.sendMessageForm.agent_name = res.data.response[0].name;
                        that.sendMessageForm.agent_id_number = res.data.response[0].id_number;
                        that.sendMessageForm.agent_phone = res.data.response[0].mobile;
                        that.sendMessageForm.agent_user_id = res.data.response[0].id;
                    } else {
                        Tools.Dialog(this).openWarning(null, '用戶不存在！');

                        that.agentVisible = false;
                        that.sendMessageForm.agent_name = '';
                        that.sendMessageForm.agent_id_number = '';
                        that.sendMessageForm.agent_phone = '';
                        that.sendMessageForm.agent_user_id = '';
                    }
                }).catch(function (error) {

                });
            },

            editMessage: function (id) {
                this.dialogTitle = '編輯訊息設定'

                this.addMessageSettingVisible = true;

                this.sendMessageForm = {
                    receive_type: '1',
                    receive_key: '',
                    receive_name: '',
                    receive_id_number: '',
                    receive_phone: '',
                    agent_type: '1',
                    agent_key: '',
                    agent_name: '',
                    agent_id_number: '',
                    agent_phone: '',
                    receive_user_id: '',
                    agent_user_id: ''
                };

                this.receiveVisible = false;
                this.agentVisible = false;

                axios.get('/message/setting/messageInfo?id=' + id).then((res) => {
                    if (res.data.response.data) {
                        this.messageSettingForm = res.data.response.data;
                        if(this.messageSettingForm.stage_id !== undefined){
                            this.messageSettingForm.stage_id = this.messageSettingForm.stage_id.toString();
                            this.getRemainGold(this.messageSettingForm.stage_id);
                        }

                        this.messageSettingForm.id = id;
                        this.messageSettingForm.content_id = '發起人';

                        if (this.messageSettingForm.is_question) {
                            this.changeQuestionUnit(this.messageSettingForm.question_unit_id)
                        }
                        if (this.messageSettingForm.is_activity) {
                            this.changeActivityUnit(this.messageSettingForm.activity_unit_id)
                        }

                        if (this.messageSettingForm.is_survey) {
                            this.messageSettingForm.is_survey = true
                            this.goldsEdit = true;
                        }

                        let that = this;
                        that.setStageGold(this.messageSettingForm.department_id);
                    }
                });

                let that = this;
                axios.get('/message/setting/getGroupId?id=' + id)
                    .then(function (response) {
                        if (response.data.response) {
                            that.group_sum = [];
                            for (let key in response.data.response) {
                                that.group_sum.push({id: key, name: response.data.response[key]});
                            }
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },

            setStageGold: function (id) {
                if (id === undefined || id === '') {
                    return
                }
                let action = 'add'
                if(this.goldsEdit){
                    action = 'edit'
                }
                axios.get('/gold/account/departmentStage?id=' + id + '&event_id=' + this.messageSettingForm.id + '&action=' +action).then((response) => {
                    this.send_stage = response.data.response.list;
                });
            },

            getRemainGold: function (stageId) {
                if (parseInt(stageId) === 0) {
                    this.remain_gold = 0;
                    return;
                }
                axios.get('/gold/send/getRemainGold?stage_id=' + stageId + '&unit_id=' + this.messageSettingForm.department_id).then((response) => {
                    this.remain_gold = response.data.response.remain_gold;
                    if (this.remain_gold === undefined) {
                        this.remain_gold = 0;
                    }
                })
            },

            setTotalSendGold: function (action) {
                let personLimit = this.messageSettingForm.person_limit ? this.messageSettingForm.person_limit : 0;
                let personGold = this.messageSettingForm.person_gold ? this.messageSettingForm.person_gold : 0;

                this.totalSendGold = personLimit * personGold;

                if (!this.goldsEdit) {
                    if (this.remain_gold === 0 && this.totalSendGold > 0) {
                        Tools.Dialog(this).openWarning(null, '尚未選擇發放期別');
                        return false;
                    }
                }

                if (this.remain_gold > 0) {
                    if (this.totalSendGold > this.remain_gold) {
                        Tools.Dialog(this).openWarning(null, '總發放金幣不得高於可發放金幣');
                        return false;
                    }
                }

                this.messageSettingForm.setting_gold = this.totalSendGold;

                if (action === 'submit') {
                    this.showGoldGrant = false;
                }
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
            goldSubmit: function () {
                let that = this;
                that.setTotalSendGold('submit');
            },
            showSurveySetting: function () {
                this.showGoldGrant = true;
                if(this.sendMessageForm.stage_id !==undefined&&this.sendMessageForm.stage_id!=='0') {
                    if(!this.goldsEdit&&(!this.send_stage||this.send_stage.length===0)){
                        this.messageSettingForm.survey_id = '0';
                        this.dialog.openError(null, '金幣已過期，請重新設定');
                    }else if(!this.goldsEdit){
                        let contain= false;
                        this.send_stage.forEach(function (item) {
                            if(item.id.toString() === this.sendMessageForm.stage_id){
                                contain = true;
                            }
                        });
                        if(!contain){
                            this.messageSettingForm.survey_id = '0';
                            this.dialog.openError(null, '金幣已過期，請重新設定');
                        }
                    }
                }

                let that = this;
                if (this.goldsEdit) {
                    //判斷金幣期別是否過期
                    axios.get('/gold/account/getExpireTime/' + this.messageSettingForm.stage_id)
                        .then(function (response) {
                            if (response.data.response.row === true) {
                                that.overdue = true;
                            } else {
                                that.overdue = false;
                            }
                        });
                }
            }
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
