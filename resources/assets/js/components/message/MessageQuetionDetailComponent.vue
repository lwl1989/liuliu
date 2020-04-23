<template>
    <div id="app">
        <el-dialog title="問卷編輯" :close-on-click-modal='false' :visible.sync="QuestionVisible" width="70%">

            <el-steps :active="active" finish-status="success" style="margin-top:20px;">
                <el-step title="填寫問卷基本資料" description="">
                </el-step>
                <el-step title="設計問卷" description="">
                </el-step>
                <el-step title="設置問卷金幣" description="">
                </el-step>
            </el-steps>

            <el-table v-loading="loading" :data="data" >
                <el-table-column>
                    <template slot-scope="scope">
                        <!-- 填寫問卷基本資料 -->
                        <template v-if="active === 1" style="margin-top:20px;">
                            <el-form :model="question" ref="question" :rules="questionRule" label-position="top">
                                <el-form-item label="業務單位" prop="department_id">

                                    <el-select v-model="question.department_id" placeholder="" :disabled="disabled">
                                        <el-option key="0" value="0" label="請選擇"></el-option>
                                        <template v-for="(item) in departments">
                                            <el-option :label="item.name" :value="item.id.toString()"
                                                       :key="item.id.toString()"></el-option>
                                        </template>
                                    </el-select>
                                </el-form-item>
                                <el-form-item label="發放期別(發放日/有效期限)" prop="stage_id">
                                    <el-select v-model="question.stage_id" @change="setRemainGold" style="width: 350px;"
                                               :disabled="disabled">
                                        <el-option key="0" value="0" label="請選擇"></el-option>
                                        <template v-for="item in stage_golds">
                                            <el-option :key="item.id.toString()" :value="item.id.toString()"
                                                       :label="item.id + '(' + item.issue_time+' ~ '+ item.expire_time + ')'">
                                            </el-option>
                                        </template>
                                    </el-select>
                                </el-form-item>
                                <el-form-item>
                                    帳號目前可設定發放金幣為 {{remain_gold}}
                                </el-form-item>
                                <el-form-item label="問卷名稱" prop="title">
                                    <el-col :span="15">
                                        <el-input v-model="question.title" auto-complete="off" :disabled="recycle_status"></el-input>
                                    </el-col>
                                </el-form-item>

                                <el-form-item label="問卷開放填寫時間">
                                    <el-date-picker
                                            v-model="questionTime"
                                            type="datetimerange"
                                            range-separator="~"
                                            format="yyyy-MM-dd HH:mm"
                                            start-placeholder="開始日期"
                                            end-placeholder="結束日期"
                                            align="right"
                                            :picker-options="timeOptions"
                                            :disabled="disabled">
                                    </el-date-picker>
                                </el-form-item>

                                <el-form-item label="是否開啟重新填寫" prop="refresh">
                                    <el-select v-model="question.refresh" placeholder="" :disabled="disabled">
                                        <el-option key="1" value="1" label="是"></el-option>
                                        <el-option key="0" value="0" label="否"></el-option>
                                    </el-select>
                                </el-form-item>

                                <el-form-item label="可答錯次數（1-5）" prop="attempt">
                                    <el-input-number :disabled="disabled" v-model="question.attempt" placeholder="" :min="min+1"
                                                     :max="attemptMax"></el-input-number>
                                </el-form-item>
                            </el-form>
                        </template>

                        <!-- 問題 -->
                        <template v-else-if="active === 2" style="margin-top:20px;">
                            <el-row>
                                <el-col :span="6">
                                    <el-select v-model="questionType" placeholder="">
                                        <el-option key="1" value="1" label="問卷調查"></el-option>
                                        <el-option key="2" value="2" label="有獎徵答"></el-option>
                                        <!--<el-option key="3" value="3" label="簡答題"></el-option>-->
                                        <el-option key="4" value="4" label="會員資料"></el-option>
                                        <!--<el-option key="5" value="5" label="上傳附件"></el-option>-->
                                    </el-select>
                                </el-col>
                                <el-col :span="6" v-if="optionTypes.length > 0">
                                    <el-select v-model="questionOptionType" placeholder="">
                                        <el-option v-for="option in optionTypes" :key="option.type" :value="option.type"
                                                   :label="option.name"></el-option>
                                    </el-select>
                                </el-col>
                                <el-col :span="6">
                                    <el-button type="primary" @click="addQuestion" v-if="!disabled">新增題目</el-button>
                                </el-col>
                            </el-row>

                            <el-row>
                                <template v-for="(question,index) in params">

                                    <template v-if="question.type === '1' || question.type === '2'">
                                        <div class="answer_box" v-if="question.optionType === '3'">
                                            <div class="question">
                                                <div class="q_i">●</div>
                                                <div class="q_w">{{question.title}}</div>
                                                <el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small"
                                                           @click="question.edit = true" circle></el-button>
                                                <el-button v-if="!disabled" type="info" icon="el-icon-delete" size="small"
                                                           @click="removeQuestion(index)" circle></el-button>
                                            </div>

                                            <div class="correct" v-if="question.type === '2'&&question.correct" >【正確答案】{{question.correct}}</div>
                                        </div>
                                        <div class="answer_box" v-else>
                                            <div class="question">
                                                <div class="q_i">●</div>
                                                <div class="q_w">{{question.title}}</div>
                                                <el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small"
                                                           @click="question.edit = true" circle></el-button>
                                                <el-button v-if="!disabled" type="info" icon="el-icon-delete" size="small"
                                                           @click="removeQuestion(index)" circle></el-button>
                                            </div>
                                            <div class="a_item">
                                                <span v-for="(value,key) in question.options">
                                                    <span v-if="value.option">
                                                        {{key+1}}. {{value.option}}
                                                    </span>
                                                </span>
                                            </div>
                                            <div v-if="question.type === '2'">
                                                <div class="correct" v-if="question.optionType === '1'">
                                                    【正確答案】{{question.options[question.correct].option}}
                                                </div>
                                                <div class="correct" v-else-if="question.correct">【正確答案】<span v-for="(cor) in question.correct"> {{question.options[cor].option}}</span>
                                                </div>
                                            </div>
                                        </div>

                                    </template>

                                    <!--<template v-else-if="question.type === '3'">-->
                                    <!---->
                                    <!--</template>-->

                                    <template v-else-if="question.type === '4'">
                                        <div class="answer_box">
                                            <div class="question">
                                                <div class="q_i">●</div>
                                                <div class="q_w">{{question.title}}</div>
                                                <el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small"
                                                           @click="question.edit = true" circle></el-button>
                                                <el-button v-if="!disabled" type="info" icon="el-icon-delete" size="small"
                                                           @click="removeQuestion(index)" circle></el-button>
                                            </div>

                                            <!--<div class="correct">【正確答案】{{question.correct}}</div>-->
                                        </div>
                                    </template>

                                    <template v-else-if="question.type === '5'">
                                        <div class="answer_box">
                                            <div class="question">
                                                <div class="q_i">●</div>
                                                <div class="q_w">{{question.title}}</div>
                                                <el-button v-if="!disabled" type="warning" icon="el-icon-edit" size="small"
                                                           @click="question.edit = true" circle></el-button>
                                                <el-button v-if="!disabled" type="info" icon="el-icon-delete" size="small"
                                                           @click="removeQuestion(index)" circle></el-button>
                                            </div>

                                            <!--<div class="correct">【正確答案】{{question.correct}}</div>-->
                                        </div>
                                    </template>
                                </template>
                            </el-row>

                        </template>

                        <!-- 金幣 -->
                        <template v-else style="margin-top:20px;">
                            <el-form :model="golds" ref="golds" :rules="questionGoldsRule" label-position="top"
                                     v-if="question.id === 0">
                                <el-form-item>
                                    帳號目前可設定發放金幣為 {{remain_gold}}
                                </el-form-item>
                                <el-form-item>
                                    問卷已發送金幣數量為 {{golds.sent_gold}}
                                </el-form-item>
                                <el-form-item prop="person_gold">
                                    <el-col class="line" :span="5" :offset="0">每個人發放</el-col>
                                    <el-col :span="6">
                                        <el-input-number v-model="golds.person_gold" auto-complete="off"
                                                         :min="min"></el-input-number>
                                    </el-col>
                                    <el-col class="line" :span="3" :offset="1">枚金幣</el-col>
                                </el-form-item>
                                <el-form-item prop="person_limit">
                                    <el-row>
                                        <el-col class="line" :span="5" :offset="0">限制前</el-col>
                                        <el-col :span="6">
                                            <el-input-number v-model="golds.person_limit" auto-complete="off"
                                                             :min="min"></el-input-number>
                                        </el-col>
                                        <el-col class="line" :span="3" :offset="1">名領取</el-col>
                                    </el-row>
                                </el-form-item>

                                <el-form-item>
                                    總計 {{golds.setting_gold}} 枚
                                </el-form-item>
                                <el-row>
                                    <el-col :span="12">
                                        &nbsp;
                                    </el-col>
                                    <el-col :span="12">
                                        <el-button type="primary" @click="submitQuestion"
                                                   style="float: right;margin-left: 30px;">確 定
                                        </el-button>
                                        <el-button @click="QuestionVisible = false" style="float: right;margin-left: 30px;">取
                                            消
                                        </el-button>
                                    </el-col>
                                </el-row>
                            </el-form>
                            <el-form :model="goldsUpdate" ref="golds" :rules="questionGoldsRule" label-position="top" v-else>
                                <el-form-item>
                                    帳號目前可設定發放金幣為 {{remain_gold}}
                                </el-form-item>
                                <el-form-item>
                                    已設定人數為 {{golds.person_limit}}, 每人金幣數為 {{golds.person_gold}}，已設定總數為 <span v-if="recycle_status">{{setting_sum}}</span><span v-else>{{golds.setting_gold}}</span>,
                                    問卷已發送金幣數量為 {{golds.sent_gold}} <span v-show="recycle_status">, 已回收金幣數量為{{recycle_gold}} </span>
                                </el-form-item>
                                <el-form-item prop="person_gold">
                                    <el-col class="line" :span="5" :offset="0">每個人發放</el-col>
                                    <el-col :span="6">
                                        <el-input-number v-model="golds.person_gold" :disabled="disabled"></el-input-number>
                                    </el-col>
                                    <el-col class="line" :span="3" :offset="1" prop="fans_number">枚金幣</el-col>
                                </el-form-item>
                                <el-form-item prop="person_limit">
                                    <el-row>
                                        <el-col class="line" :span="5" :offset="0">限制前</el-col>
                                        <el-col :span="6">
                                            <el-input-number v-model="goldsUpdate.person_limit" auto-complete="off"
                                                             :min="golds.person_limit" :disabled="recycle_status||golds.person_gold==0"></el-input-number>
                                        </el-col>
                                        <el-col class="line" :span="3" :offset="1" prop="fans_number">名領取</el-col>
                                    </el-row>
                                </el-form-item>

                                <el-form-item>
                                    總計 {{goldsUpdate.setting_gold}} 枚
                                    <span v-if="!recycle_status">
                                        - 新增 <span style="color: red">{{goldsUpdate.setting_gold-golds.setting_gold}} </span>枚
                                    </span>
                                </el-form-item>
                                <el-row>
                                    <el-col :span="12">
                                        &nbsp;
                                    </el-col>
                                    <el-col :span="12">
                                        <el-button type="primary" @click="submitQuestion"
                                                   style="float: right;margin-left: 30px;">確 定
                                        </el-button>
                                        <el-button @click="QuestionVisible = false" style="float: right;margin-left: 30px;">取
                                            消
                                        </el-button>
                                    </el-col>
                                </el-row>
                            </el-form>
                        </template>

                        <template>
                            <el-row :span="15" v-if="question.updater !== ''">
                                <el-input :value="question.update_time+' '+question.updater" auto-complete="off" disabled
                                          style="border: none;"></el-input>
                            </el-row>
                            <el-row>
                                &nbsp;
                            </el-row>
                            <el-row>
                                &nbsp;
                            </el-row>
                            <el-row>
                                <el-col :span="12">
                                    <el-button v-if="active > 1" type="primary" @click="pre">上一步</el-button>
                                    <template v-else>&nbsp;</template>
                                </el-col>
                                <el-col :span="12">
                                    <el-button v-if="active < 3" style="float: right" type="primary" @click="next">下一步
                                    </el-button>
                                </el-col>

                            </el-row>
                        </template>
                    </template>
                </el-table-column>

            </el-table>


            <template v-for="(question,index) in params">
                <el-dialog :close-on-click-modal="false" :visible.sync="question.edit" v-if="question.optionType === '1'" :show-close="showClose" :close-on-press-escape="showClose" append-to-body>
                    <el-form :model="question" ref="reference" :rules="questionRadioRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="question.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>

                        <template v-for="(value,key) in question.options">
                            <el-row style="margin-top: 2px;">
                                <el-col :span="2">
                                    <template v-if="question.type === '1'">
                                        <el-radio disabled v-model="question.correct" :label="key">{{key+1}}</el-radio>
                                    </template>
                                    <template v-else>
                                        <el-radio v-model="question.correct" :label="key">{{key+1}}</el-radio>
                                    </template>
                                </el-col>
                                <el-col :span="16">
                                    <el-form-item label="問題選項" :prop="'options['+key+'].option'">
                                        <el-input v-model="value.option" placeholder="請輸入選項"></el-input>
                                        <el-button v-if="key>0" type="text" @click="removeRadio(index,key)">移除選項
                                        </el-button>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </template>

                        <el-form-item>
                            <el-button type="text" @click="addRadioOption(index)">新增選項</el-button>
                        </el-form-item>

                        <el-form-item v-if="question.type === '1'">
                            <el-select v-model="question.required" placeholder="">
                                <el-option key="1" value="1" label="必填"></el-option>
                                <el-option key="0" value="0" label="非必填"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-button @click="saveQuestion(index)" type="warning" style="margin-top: 50px;" round>儲 存
                        </el-button>
                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="question.edit" v-else-if="question.optionType === '2'"  :show-close="showClose" :close-on-press-escape="showClose" append-to-body>
                    <el-form :model="question" ref="reference" :rules="questionRadioRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="question.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>
                        <template v-for="(value,key) in question.options">
                            <el-row style="margin-top: 2px;">
                                <el-col :span="2">
                                    <template v-if="question.type === '1'">
                                        <el-checkbox v-model="question.correct" :label="key" :key="key"
                                                     disabled>{{key+1}}</el-checkbox>
                                    </template>
                                    <template v-else>
                                        <el-checkbox v-model="question.correct" :label="key" :key="key">{{key+1}}</el-checkbox>
                                    </template>
                                </el-col>
                                <el-col :span="16">
                                    <el-form-item label="問題選項" :prop="'options['+key+'].option'">
                                        <el-input v-model="value.option" placeholder="請輸入選項"></el-input>
                                        <el-button v-if="key>0" type="text" @click="removeRadio(index,key)">移除選項
                                        </el-button>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </template>

                        <el-form-item>
                            <el-button type="text" @click="addRadioOption(index)">新增選項</el-button>
                        </el-form-item>

                        <el-form-item label="問題性質" prop="title" v-if="question.type === '1'">
                            <el-select v-model="question.required" placeholder="">
                                <el-option key="1" value="1" label="必填"></el-option>
                                <el-option key="0" value="0" label="非必填"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-button @click="saveQuestion(index)" type="warning" style="margin-top: 50px;" round>儲 存
                        </el-button>
                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="question.edit" v-else-if="question.optionType === '3'"
                           :show-close="showClose" :close-on-press-escape="showClose" append-to-body>
                    <el-form :model="question" ref="reference" :rules="questionTextRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="question.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>
                        <el-form-item label="正確答案" v-if="question.type === '2'">
                            <el-checkbox v-model="question.judgement" label="1" @change="question.judgement.length==0?question.correct='':''">有正確答案否</el-checkbox>
                            <template v-if="question.judgement.length > 0">
                                <el-input v-model="question.correct"></el-input>
                            </template>
                            <template v-else>
                                <el-input disabled></el-input>
                            </template>
                        </el-form-item>

                        <el-form-item label="問題性質">

                            <template v-if="question.judgement.length > 0 && question.type === '2'">
                                <el-select v-model="question.required" placeholder="" disabled>
                                    <el-option key="1" value="1" label="必填"></el-option>
                                    <el-option key="0" value="0" label="非必填"></el-option>
                                </el-select>
                            </template>
                            <template v-else>
                                <el-select v-model="question.required" placeholder="">
                                    <el-option key="1" value="1" label="必填"></el-option>
                                    <el-option key="0" value="0" label="非必填"></el-option>
                                </el-select>
                            </template>
                        </el-form-item>

                        <el-form-item>
                            <el-button @click="saveQuestion(index)" type="warning" style="margin-top: 50px;" round>儲 存
                            </el-button>
                        </el-form-item>

                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="question.edit" append-to-body v-else-if="question.type === '4'" :show-close="showClose" :close-on-press-escape="showClose">
                    <el-form :model="question" ref="reference" :rules="questionProfileRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="question.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>

                        <el-form-item label="卡片類型">
                            <el-select v-model="question.profileType" placeholder="卡片類型">
                                <el-option v-for="(value) in profileTypes" :key="value.type" :value="value.type"
                                           :label="value.name"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="問題性質">
                            <el-select v-model="question.required" placeholder="">
                                <el-option key="1" value="1" label="必填"></el-option>
                                <el-option key="0" value="0" label="非必填"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item>
                            <el-button @click="saveQuestion(index)" type="warning" style="margin-top: 50px;" round>儲 存
                            </el-button>
                        </el-form-item>

                    </el-form>
                </el-dialog>

                <el-dialog :close-on-click-modal="false" :visible.sync="question.edit" append-to-body v-else-if="question.type === '5'" :show-close="showClose" :close-on-press-escape="showClose">
                    <el-form :model="question" ref="reference" :rules="questionProfileRule" label-position="top">
                        <el-form-item label="您的問題" prop="title">
                            <el-input v-model="question.title" placeholder="請輸入1-40字數"></el-input>
                        </el-form-item>

                        <el-form-item label="問題性質">
                            <el-select v-model="question.required" placeholder="">
                                <el-option key="1" value="1" label="必填"></el-option>
                                <el-option key="0" value="0" label="非必填"></el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item>
                            <el-button @click="saveQuestion(index)" type="warning" style="margin-top: 50px;" round>儲 存
                            </el-button>
                        </el-form-item>

                    </el-form>
                </el-dialog>
            </template>
        </el-dialog>
    </div>
</template>

<script>

    import NewDialog from '../../tools/element-ui-dialog';
    import {
        QuestionProfileRule,
        QuestionRadioRule,
        QuestionRule,
        QuestionTextRule,
        QuestionGoldsRule
    } from '../../tools/element-ui-validate';
    import Tools from "../../tools/vue-common-tools";
    //import { Loading } from 'element-ui'
    //let now = new Date();
    export default {
        name: "data-detail-component",
        data: function () {
            return {
                data:[{id:0}],
                showClose: true,
                loading: false,
                loadings: 0,
                disabled: false,
                isAdd: true, // false編輯
                timeOptions: {
                    disabledDate(time) {
                        return time.getTime() < Date.now() - 8.64e7;
                    }
                },
                questionRule: QuestionRule,
                questionRadioRule: QuestionRadioRule,
                questionTextRule: QuestionTextRule,
                questionProfileRule: QuestionProfileRule,
                questionGoldsRule: QuestionGoldsRule,
                min: 0,
                attemptMax: 5,
                questionTime: [
                    // now,
                    // now.setDate(now.getDate() + 2)
                ],
                questionType: "1",
                questionOptionType: "1",
                optionTypes: [
                    {type: '1', name: '單選'},
                    {type: '2', name: '複選'},
                    {type: '3', name: '問答題'}
                ],
                profileTypes: [
                    // {type: '1', name: '身份證號'},
                    // {type: '2', name: '居住證號'},
                    // {type: '3', name: '護照號'},
                    {type: '4', name: 'email'}
                ],
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                },
                formLabelWidth: '300px',
                QuestionVisible: false,
                question: {
                    id: 0,
                    title: '',
                    attempt: 3,
                    refresh: '0',
                    open_time: '',
                    close_time: '',
                    update_time: '',
                    updater: '',
                    stage_id: "0",
                    department_id: "0"
                },
                golds: {
                    department_id: "0",
                    stage_id: "0",
                    setting_gold: 0,
                    person_gold: 0,
                    sent_gold: 0,
                    person_limit: 0
                },
                goldsUpdate: {
                    setting_gold: 0,
                    person_limit: 0,
                },
                params: [],
                stage_golds: [],
                dialog: NewDialog(this),
                active: 1,
                updater: '',
                departments: [],
                send_stage_sum: {},
                remain_gold: 0,
                sum: 0,
                loadingDialog:null,
                recycle_gold : 0,
                recycle_status : false,
                setting_sum : 0
            }
        },
        computed: {
            departmentChange() {
                return this.question.department_id;
            },
            stageChange() {
                return this.question.stage_id;
            }
            // goldsLimit() {
            //     return this.golds.person_limit;
            // },
            // goldsSingle() {
            //     return this.golds.person_gold;
            // },
            // goldsUpdateChange() {
            //     return this.goldsUpdate.person_limit;
            // },
        },
        watch: {
            'golds.person_gold' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.golds.person_gold = '';
                    return false;
                }
                this.checkGoldNumber('golds');
            },
            'golds.person_limit' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.golds.person_limit = '';
                    return false;
                }
                this.checkGoldNumber('golds');
            },
            'goldsUpdate.person_gold' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.goldsUpdate.person_gold = '';
                    return false;
                }
                this.checkGoldNumber('goldsUpdate');
            },
            'goldsUpdate.person_limit' : function (val) {
                var re = /^[0-9]+[0-9]*$/;
                if (!re.test(val)) {
                    Tools.Dialog(this).openWarning(null, '請輸入整數');
                    this.goldsUpdate.person_limit = '';
                    return false;
                }
                this.checkGoldNumber('goldsUpdate');
            },
            loadings(current) {
                if(current < 0) {
                    this.loadings = 0;
                    this.outLoading();
                }else if (current === 0) {
                    this.outLoading();
                } else {
                    this.inLoading();
                }
            },
            QuestionVisible(current, old) {
                if (current === false) {
                    this.resetQuestion();
                }
            },
            questionType(current, old) {
                current = parseInt(current);
                if (current === 1 || current === 2) {
                    this.optionTypes = [
                        {type: '1', name: '單選'},
                        {type: '2', name: '複選'},
                        {type: '3', name: '問答題'}
                    ]
                } else {
                    this.optionTypes = [];
                }
            },
            stageChange(current, old) {
                if (current !== old) {
                    this.golds.stage_id = current;
                    if (current === 0) {
                        this.remain_gold = 0;
                    }

                    this.setRemainGold();
                    this.checkExpireTime()
                }
            },
            departmentChange(current, old) {
                //判斷是編輯頁面還是添加頁面
                let action = 'add';
                if (this.disabled) {
                    action = 'edit';
                }

                if (parseInt(current) !== 0) {
                    if (current !== old) {
                        this.golds.department_id = current;
                        this.getRemainGold(action);
                        this.setRemainGold();
                    }
                }
            },
            questionTime(current, old) {
                this.checkExpireTime()
            },
        },
        created: function () {
            let that = this
            this.$watch('params', function () {
                that.params.forEach(function (item) {
                    if(!item.edit&&!item.title) {
                        that.params.splice(that.params.indexOf(item),1)
                    }
                })
            }, {
                deep: true,
                immediate: true
            })
        },
        methods: {
            checkExpireTime() {
                if (this.questionTime&&this.questionTime.length > 1) {
                    let start = this.questionTime[0].toString();
                    let end = this.questionTime[1].toString();

                    if (start === end) {
                        this.dialog.openWarning(() => {

                        }, '問卷填寫時間(起)不得等於問卷填寫時間(迄)');
                        return;
                    }

                    let stageId = parseInt(this.question.stage_id);
                    if (stageId !== 0 && this.loadings <= 0) {
                        this.stage_golds.forEach((item) => {
                            if (stageId === parseInt(item.id)) {
                                if (new Date(start) < new Date(item.issue_time) || new Date(end) > new Date(item.expire_time)) {
                                    this.dialog.openWarning(() => {
                                        this.question.stage_id = "0";
                                    }, '您所設定的金幣發放期別不符合問卷填寫時間，請選擇其他發放期別');
                                    return;
                                }
                            }
                        });
                    }
                    this.question.open_time = start;
                    this.question.close_time = end;
                }
            },
            submitQuestion() {
                if(this.question.id === 0) {
                    this.question.open_time = this.questionTime[0];
                    this.question.close_time = this.questionTime[1];
                }
                this.$refs.golds.validate((result) => {
                    if (result) {
                        let data = {
                            question: this.question,
                            golds: this.golds,
                            params: this.params,
                            goldsUpdate: this.goldsUpdate,
                            recycleStatus : this.recycle_status
                        };

                        let attr = 'golds'
                            if(this.isAdd){
                                attr = 'golds'
                            }else{
                                attr = 'goldsUpdate'
                            }

                        let result = this.checkGoldNumber(attr);
                        if (result !== true) {
                            return;
                        }

                        this.loadings++;
                        axios.post('/question/save?id=' + data.question.id, {data: data})
                            .then((response) => {
                                this.loadings--;
                                if (response.data.code === 0) {
                                    if(data.question.id === 0) {
                                        if (response.data.response.id > 0) {
                                            this.dialog.openSuccess(() => {
                                                this.QuestionVisible = false;
                                                window.location.reload(true);
                                            }, '操作成功');
                                        } else if (response.data.response.id === -2) {
                                            this.dialog.openWarning(() => {

                                            }, '金幣已被使用，請刷新重新選擇期別');
                                        }else{
                                            this.dialog.openSuccess(() => {
                                            }, '操作失敗');
                                        }
                                    }else{
                                        if (response.data.response.row > 0) {
                                            this.dialog.openSuccess(() => {
                                                this.QuestionVisible = false;
                                                window.location.reload(true);
                                            }, '操作成功');
                                        }else{
                                            this.dialog.openSuccess(() => {
                                            }, '操作失敗');
                                        }
                                    }
                                } else {
                                    this.dialog.openSuccess(() => {
                                    }, '操作失敗');
                                }
                            })
                            .catch((error) => {
                                this.loadings--;
                                this.dialog.openWarning(() => {
                                }, '操作失敗');
                            });
                    }
                });
            },
            checkGoldNumber(key) {
                if (!this.QuestionVisible) {
                    return true;
                }
                if (!this.recycle_status) {
                    if (!this.judgeStage()) {
                        return false;
                    }
                }
                if (this.golds.person_gold > 0){

                    let settingGold = this.golds.person_gold * this[key].person_limit;
                    this[key].setting_gold = settingGold;
                    if(this.isAdd){
                        // 新增
                        if (this.golds.setting_gold > this.remain_gold) {
                            this.dialog.openWarning(function () {}, '總發放金幣不得高於可發放金幣');
                            return false;
                        }
                    }else{
                        // 編輯
                        if (this.goldsUpdate.setting_gold > this.golds.setting_gold) {
                            let online_change  = this.goldsUpdate.setting_gold - this.golds.setting_gold;
                            if (online_change > this.remain_gold) {
                                this.dialog.openWarning(function () {}, '總發放金幣不得高於可發放金幣');
                                return false;
                            }
                        }
                    }
                }

                return true;
            },
            setRemainGold() {
                if(undefined === this.golds.stage_id||this.golds.stage_id===''){
                    return
                }
                let that = this;
                axios.get('/gold/send/getRemainGold?stage_id=' + parseInt(that.golds.stage_id) + '&unit_id=' + parseInt(that.golds.department_id))
                    .then(function (response) {
                        if (response.data.response.remain_gold === undefined) {
                            that.remain_gold = 0;
                        } else {
                            that.remain_gold = response.data.response.remain_gold;
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            judgeStage() {
                if (this.stage_golds.length > 0) {
                    if (this.question.id == 0) {
                        if (this.remain_gold === 0) {
                            this.dialog.openWarning(() => {

                            }, '請選選擇可用的金幣期別');
                            return false;
                        }
                    }
                }
                return true;
            },
            addQuestion() {
                if (this.questionType === '1' || this.questionType === '2') {
                    if (parseInt(this.questionOptionType) === 1) {
                        this.createRadio(this.questionType);
                    } else if(parseInt(this.questionOptionType) === 2) {
                        this.createCheck(this.questionType);
                    } else {
                        this.createAnswer(this.questionType);
                    }
                }
                // else if (this.questionType === '3') {
                //     this.createAnswer(this.questionType);
                // }
                else if (this.questionType === '4') {
                    this.createProfile(this.questionType);
                }
                else if (this.questionType === '5') {
                    this.createUpload(this.questionType);
                }
            },
            checkParamsLength() {
                if (this.params.length > 49) {
                    this.dialog.openWarning(function () {

                    }, '最多可設定50題');
                    return false;
                }
                this.params.forEach(function (value) {
                    value.edit = false;
                });
                return true;
            },
            addRadioOption(index) {
                if (this.params[index].options.length > 9) {
                    this.dialog.openWarning(function () {

                    }, '最多可設定10個選項');
                    return;
                }

                let len = this.params[index].options.length;
                let key = 'options[' + (len-1) + '].option';
                this.questionRadioRule[key] = this.questionRadioRule['options[0].option'];
                this.params[index].options.push({
                    id: 0,
                    option: '',
                    type: '1'
                });

            },
            removeQuestion(index) {
                this.params.splice(index, 1);
            },
            removeRadio(index, key) {
                this.params[index].options.splice(key, 1);
                //this.questionRadioRule['options['+key+'].option'] = this.questionRadioRule['options[0].option'];
            },
            createCheck(type) {
                if (!this.checkParamsLength()) {
                    return;
                }

                this.params.forEach(function (value) {
                    value.edit = false;
                });
                this.params.push({
                    id: 0,
                    title: "",
                    type: type,
                    optionType: '2',
                    required: parseInt(type) !== 1 ? '1' : '0',
                    options: [
                        {
                            id: 0,
                            option: '',
                            type: '1'
                        },
                    ],
                    correct: [],
                    edit: true
                });
            },
            createProfile(type) {
                if (!this.checkParamsLength()) {
                    return;
                }
                this.params.push({
                    id: 0,
                    title: "",
                    type: type,
                    required: parseInt(type) !== 1 ? '1' : '0',
                    profileType: '4',
                    correct: '',
                    edit: true
                });
            },

            createUpload(type) {
                if (!this.checkParamsLength()) {
                    return;
                }
                this.params.push({
                    id: 0,
                    title: "",
                    type: type,
                    required: parseInt(type) !== 1 ? '1' : '0',
                    profileType: '5',
                    correct: '',
                    edit: true
                });
            },

            createAnswer(type) {
                if (!this.checkParamsLength()) {
                    return;
                }
                this.params.push({
                    id: 0,
                    title: "",
                    type: type,
                    optionType: '3',
                    required: parseInt(type) !== 1 ? '1' : '0',
                    judgement: ['1'],
                    correct: '',
                    edit: true
                });
            },
            createRadio(type) {
                if (!this.checkParamsLength()) {
                    return;
                }
                this.params.push({
                    id: 0,
                    title: "",
                    type: type,
                    required: parseInt(type) !== 1 ? '1' : '0',
                    optionType: '1',
                    options: [
                        {
                            id: 0,
                            option: '',
                            type: '1'
                        },
                    ],
                    correct: 0,
                    edit: true
                });
            },
            getUnitSum() {
                this.loadings++;
                axios.get('/department/getAllUnit')
                    .then((response) => {
                        this.loadings--;
                        for (let key in response.data.response) {
                            this.departments.push({id: key, name: response.data.response[key]});
                        }
                    })
                    .catch( (error)=> {
                        this.loadings--;
                    });
            },
            next() {
                if (this.active === 3) {
                    this.dialog.openWarning(() => {
                    }, '沒有下一步了');
                    return;
                }

                if (this.active === 1) {
                    this.$refs.question.validate((result) => {
                        if (result) {
                            if (this.questionTime && this.questionTime.length > 1) {
                                let start = this.questionTime[0].toString();
                                let end = this.questionTime[1].toString();

                                if (start === end) {
                                    this.dialog.openWarning(() => {

                                    }, '問卷填寫時間(起)不得等於問卷填寫時間(迄)');
                                    return;
                                }
                            } else {
                                this.dialog.openWarning(() => {

                                }, '請選擇時間');
                                return;
                            }

                            if (result) {
                                this.active++;
                                //return;
                            }
                        } else {
                            this.dialog.openWarning(() => {

                            }, '請輸入必填欄位');
                        }
                    });
                } else if (this.active === 2) {
                    //this.active++;
                    if (this.params.length === 0) {
                        this.dialog.openWarning(() => {
                        }, '問卷必須包含問題');
                        return;
                    }
                    this.active++;
                }

            },
            pre() {
                if (this.active === 1) {
                    this.dialog.openWarning(() => {
                    }, '沒有上一步了');
                    return;
                }
                this.active--;
            },
            setUploader(index) {
                this.uploader = index
            },
            resetQuestion() {
                this.params = [];
                this.question = {
                    id: 0,
                    title: '',
                    attempt: 3,
                    refresh: '0',
                    open_time: '',
                    close_time: '',
                    update_time: '',
                    updater: '',
                    department_id: "0",
                    stage_id : '0'
                };
                this.golds = {
                    department_id: "0",
                    stage_id: "0",
                    setting_gold: 0,
                    person_gold: 0,
                    sent_gold: 0,
                    person_limit: 0
                };
                this.goldsUpdate = {
                    department_id: "0",
                    stage_id: "0",
                    setting_gold: 0,
                    person_gold: 0,
                    sent_gold: 0,
                    person_limit: 0
                };
                this.questionTime = [];
                this.recycle_gold = 0;
                this.recycle_status = false
            },
            inLoading(){
                this.loading = true;
            },
            outLoading(){
                this.loading = false;
            },
            editQuestion(id) {
                this.active = 1;
                if (this.departments.length < 1) {
                    this.getUnitSum();
                }
                //question_page

                this.disabled = false;
                if (id > 0) {
                    this.disabled = true;
                    this.isAdd = false;
                    this.loadings++;
                    this.QuestionVisible = true;
                    axios.get('/question/get?id=' + id)
                        .then((response) => {
                            console.log(response);
                            this.loadings--;
                            this.params = [];
                            let question = response.data.response.data;
                            this.question.id = question.id;
                            this.recycle_gold = question.recycle_gold;
                            this.recycle_status = question.recycle_status.toString() === '2' ? true : false;
                            //this.question.stage_id = question.stage_id.toString();

                            let qKeys = ['id', 'title', 'refresh', 'attempt', 'stage_id', 'department_id', 'open_time', 'close_time'];
                            let gKeys = ['setting_gold', 'person_gold', 'sent_gold', 'person_limit', 'department_id', 'stage_id'];
                            let toStringKey = [
                                'title', 'stage_id', 'department_id', 'type', 'profileType', 'refresh'
                            ];
                            let toNumberKey = ['setting_gold', 'person_gold', 'sent_gold', 'person_limit'];

                            qKeys.forEach((value) => {
                                if (question.hasOwnProperty(value)) {
                                    if (toStringKey.indexOf(value) !== -1) {
                                        this.question[value] = question[value].toString();
                                    } else {
                                        this.question[value] = question[value];
                                    }
                                }
                            });

                            gKeys.forEach((value) => {
                                if (question.hasOwnProperty(value)) {
                                    if (toStringKey.indexOf(value) !== -1) {
                                        this.golds[value] = question[value].toString();
                                    } else if (toNumberKey.indexOf(value) !== -1) {
                                        if(value === 'person_limit') {
                                            this.goldsUpdate['person_limit'] = parseInt(question[value]);
                                            this.goldsUpdate['setting_gold'] = parseInt(question[value]) * parseInt(question['person_gold']);
                                        }
                                        this.golds[value] = parseInt(question[value]);
                                    } else {
                                        this.golds[value] = question[value];
                                    }
                                }
                            });

                            this.setting_sum = parseInt(this.golds['sent_gold']) + parseInt(this.recycle_gold);

                            if (question.hasOwnProperty('questions')) {
                                question.questions.forEach((item) => {
                                    let obj = {edit: false};
                                    let keys = ['title', 'id', 'required', 'type', 'profileType', 'correct'];
                                    keys.forEach((value) => {
                                        if (item.hasOwnProperty(value)) {
                                            if (toStringKey.indexOf(value) !== -1) {
                                                obj[value] = item[value].toString();
                                            } else {
                                                obj[value] = item[value];
                                            }
                                        }
                                    });

                                    if (obj.type === '1' || obj.type === '2') {
                                        obj.options = item.options;
                                        let type = item.options[0].type.toString();
                                        let correct = [];
                                        item.options.forEach((value, key) => {
                                            if (parseInt(type) === 1 && parseInt(value.correct) === 1) {
                                                obj.correct = key;
                                                obj.optionType = '1';
                                            }
                                            if (parseInt(type) === 2 && parseInt(value.correct) === 1) {
                                                correct.push(key);
                                                obj.optionType = '2';
                                            }

                                            if (parseInt(type) === 3) {
                                                obj.correct = value.correct;
                                                obj.optionType = '3';
                                                obj.judgement = value.judgement;
                                            }
                                        });

                                        if (correct.length > 0) {
                                            obj.correct = correct;
                                        }
                                    } else {
                                        let keysOption = ['title', 'id', 'correct'];
                                        keysOption.forEach((value) => {
                                            if (item.hasOwnProperty(value)) {
                                                obj[value] = item[value];
                                            }
                                        });
                                    }
                                    this.params.push(obj);
                                });
                            }
                            this.questionTime = [
                                new Date(new Date(question.open_time).getTime() + 8 * 3600 * 1000),
                                new Date(new Date(question.close_time).getTime() + 8 * 3600 * 1000)
                            ];

                        }).catch((error)=> {
                        this.loadings--;
                    });
                } else {
                    this.QuestionVisible = true;
                    this.isAdd = true;
                    this.remain_gold = 0;
                    this.question.department_id = '0';
                    this.question.stage_id = '0';
                    this.question.name = '';

                    return;
                }
            },
            getRemainGold(action) {
                if (this.golds.department_id > 0) {
                    this.loading++;
                    axios.get('/gold/account/departmentStage?id=' + this.golds.department_id + '&event_id=' + this.question.id + '&action=' + action)
                        .then((response) => {
                            this.loadings--;
                            this.stage_golds = response.data.response.list;
                            if (this.question.stage_id > 0) {
                                this.setRemainGold();
                            }
                        })
                        .catch((error) => {
                            this.loadings--;
                        });
                }
            },
            saveQuestion(index) {

                //有獎徵da
                if (this.params[index].type === '2') {

                    if(this.params[index].optionType !== '3') {
                        if (this.params[index].correct === '' || this.params[index].correct.length === 0) {
                            this.dialog.openWarning(() => {

                            }, '請選擇正確答案');
                            return;
                        }
                    }else{
                            if (this.params[index].judgement.length === 1) {
                                if(this.params[index].required === '0') {
                                    this.params[index].required = '1';
                                    this.dialog.openWarning(() => {

                                    }, '有正確答案的爲必填項目');
                                    return;
                                }
                                if (this.params[index].correct === '' ) {
                                    this.dialog.openWarning(() => {

                                    }, '請輸入正確答案');
                                    return;
                                }
                            }

                    }
                }

                if(this.params[index].edit) {
                    this.$refs.reference[index].validate((result) => {
                        if (result) {
                            this.params[index].edit = false;
                            this.resetRule();
                        }
                    });
                }
            },
            resetRule() {
                this.questionRadioRule = QuestionRadioRule;
                this.questionTextRule = QuestionTextRule;
                this.questionProfileRule = QuestionProfileRule;
            }
        }
    }
</script>

<style scoped>
    .answer_box {
        margin: 10px 0 30px 0;
        overflow: hidden;
        color: #606266;
        font-size: 14px;
        border-bottom: 1px dotted #CCCCCC;
    }

    .question {
        overflow: hidden;
        margin-bottom: 5px;
    }

    .q_i {
        float: left;
        font-size: 16px;
        line-height: 40px;
        color: #67c23a;
    }

    .q_w {
        float: left;
        max-width: calc(100% - 130px);
        margin-left: 10px;
        margin-right: 10px;
        font-size: 18px;
        line-height: 40px;
        font-weight: bold;
    }

    .a_item {
        float: left;
        margin: 0 20px;
    }

    .a_item span {
        margin-right: 30px;
        line-height: 32px;
    }

    .correct {
        clear: both;
        margin-left: 20px;
        line-height: 32px;
        color: #67c23a;
    }
</style>