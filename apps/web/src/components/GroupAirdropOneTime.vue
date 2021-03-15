<template>
    <a-modal
        v-model="model"
        width="660px"
        :closable="false"
        :footer="null"
        :centered="true"
        v-on:cancel="onClose"
    >
        <div
            :class="['modal-close-btn', {'mobile': isMobile}]"
            v-on:click="onClose"
        >
            <Icons
                type="chacha"
            />
        </div>
        <div class="airdrop-onetime">
            <div class="title-box">
                {{$t('one_time_airdrop')}}
            </div>

            <div class="onetime-airdrop-form">
                <div class="label">{{$t('amount')}}</div>
                <div class="item amount">
                    <a-input
                        type="number"
                        v-model.lazy="onetimeRuleModel.award_count"
                    ></a-input>
                </div>
                <div class="label">{{$t('to_member')}}</div>
                <div class="item">
                    <a-radio-group
                        name="all_member"
                        v-on:change="onSwitchCriteria"
                        v-model.lazy="onetimeRuleModel.all_member"
                    >
                        <a-radio
                            :value="1"
                        >
                        {{$t('all_mmeber')}}
                        </a-radio>
                        <a-radio
                            :value="2"
                        >
                        {{$t('met_criterias')}}
                        </a-radio>
                    </a-radio-group>
                </div>
                <div 
                    :class="['options-block', {'show-cretiria': showCretiria}]"
                >
                    <div class="label">{{$t('execute_when_member')}}</div>
                    <div>
                        <div
                            class="options"
                            v-for="item in options"
                            :key="item.key"
                        >
                            <a-checkbox
                                v-on:change="selectOption"
                                :checked="item.value"
                                :value="item.key"
                            ></a-checkbox>
                            <span>{{$t(item.text1)}}</span>
                            <a-input
                                v-model.lazy="item.num"
                            />
                            <span
                                class='lower'
                            >{{$tc(item.text2, 0)}}</span>
                        </div>
                    </div>
                    <div class="label">{{$t('date_range')}}</div>
                    <div class="item date-range">
                        <span>{{$t('in_last')}}</span>
                        <a-input
                            v-model.lazy="onetimeRuleModel.days"
                        ></a-input>
                        <span>{{$t('days')}}</span>
                    </div>
                </div>
            </div>

            <a-button
                type="primary"
                :disabled="!canSave"
                v-on:click="showConfirmExecute = true"
            >
                {{$t('execute')}}
            </a-button>
        </div>
        <ConfirmModal
            v-if="showConfirmExecute"
            :yes-text="$t('execute')"
            :no-text="$t('not_now')"
            v-on:confirm="onExecute"
            v-on:cancel="showConfirmExecute = false"
        >
            <div class="title">
                <QuestionMark/>
            </div>
            <div
                class="message"
            >
                <div><strong>{{$t('onetime_airdrop_confirm')}}</strong></div>
            </div>
        </ConfirmModal>
    </a-modal>
</template>
<script lang="ts">
    import {Component, Emit, Vue, Watch} from 'vue-property-decorator';
    import {Response} from '@/http/Response';
    import {IS_MOBILE} from '@/helpers/Utils';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import QuestionMark from '@/components/QuestionMark.vue';

    @Component({
        components: {
            ConfirmModal,
            QuestionMark,
        },
    })
    export default class GroupAirdropOneTime extends Vue {

        protected showCretiria: boolean = true;

        protected isMobile: boolean = IS_MOBILE;
        
        protected model: boolean = true;

        protected showConfirmExecute = false;

        protected canSave: boolean = false;

        protected options: any[] = [
            {key: 'receive_likes', value: false, num: 0, text1: 'received', text2: 'likes'},
            {key: 'topics', value: false, num: 0, text1: 'created', text2: 'topics'},
            {key: 'topic_receive_reply', value: false, num: 0, text1: 'topic_received', text2: 'replies'},
        ];

        protected onetimeRuleModel = {
            id: 0,
            award_count: 0,
            all_member: 2,
            type: '',
            condition: 0,
            days: 30,
        }

        @Watch('onetimeRuleModel', {deep: true})
        protected onRuleModelChange() {
            if (this.onetimeRuleModel.award_count < 0) {
                this.onetimeRuleModel.award_count = this.onetimeRuleModel.award_count * -1;
            }

            if (this.onetimeRuleModel.days <= 0) {
                this.onetimeRuleModel.days = 30;
            }

            this.onCanSave();
        }

        @Watch('options', {deep: true})
        protected onOptionsChange() {
            for (let i = 0; i < this.options.length; i++) {
                if (this.options[i].num < 0) {
                    this.options[i].num = this.options[i].num * -1;
                }

                this.options[i].num = parseInt(this.options[i].num);

                if (typeof this.options[i].num !== 'number' || isNaN(this.options[i].num)) {
                    this.options[i].num = 1;
                }
            }

            this.onCanSave();
        }

        protected onCanSave() {

            if (this.onetimeRuleModel.award_count <= 0) {
                this.canSave = false;
                return;
            }

            if(this.onetimeRuleModel.all_member == 2){

                for (let i = 0; i < this.options.length; i++) {

                    if (this.options[i].value && Number(this.options[i].num) > 0) {
                        this.canSave = true;
                        return;
                    }
                }
            
                this.canSave = false;
            } else {
                this.canSave = true;
            }
        }

        protected selectOption(e: {target: any}) {

            for (let i in this.options) {
                if (this.options[i].key == e.target.value) {
                    this.options[i].value = true;
                    this.onetimeRuleModel.type = this.options[i].key;
                } else {
                    this.options[i].value = false;
                    this.options[i].num = 0;
                }
            }
        }

        protected onSwitchCriteria(e: {target: {value: any}}) {
            if (e.target.value == 1) {
                this.showCretiria = false;
            } else {
                this.showCretiria = true;
            }
        }

        protected onExecute() {
            const data = new FormData;

            data.append('award_count', this.onetimeRuleModel.award_count + '');

            if (this.onetimeRuleModel.all_member == 2) {
                data.append('require_count', this.onetimeRuleModel.condition + '');
                data.append('type', this.onetimeRuleModel.type + '');
                data.append('days', this.onetimeRuleModel.days + '');
            }

            this.$store.dispatch('AirdropRule/oneTimeDrop', data)
            .then((response: Response) => {
                
                if (Number(response.getCode()) != 20000 ){
                    if(response.getDescription()){
                        this.$message.error(response.getDescription() as string);
                    } else {
                        this.$message.error(this.$t('network_error') as string);
                    }
                } else {

                    const data: {airdrop: any} = response.getData();

                    if (data && data.airdrop) {
                        this.$message.success(this.$t('onetime_airdrop_success') as string);
                    }
                }
            })
            .finally(() => {
                this.onClose();
            });
        }


        @Emit('close-airdrop-onetime')
        protected onClose() {

        }
    }
</script>
<style lang="scss" scoped>
    .airdrop-onetime {
        .title-box {
            @include modal_title_box;
        }

        .onetime-airdrop-form {
            
            .label {
                @include form_label;
                padding: var(--p6) 0 var(--p2);
            }

            .item {
                @include form_item;
            }

            .amount {
                .ant-input {
                    width: 200px;
                    height: 40px;
                }
            }

            .options {
                margin-bottom: var(--p4);
                color: var(--font-color1);

                .ant-checkbox-wrapper {
                    margin-right: var(--p2);
                }

                .ant-input {
                    width: 80px;
                    margin: 0 var(--p2);
                }

                .lower {
                    text-transform: lowercase;
                }
            }
            
            .options:last-child {
                margin-bottom: 0;
            }

            .date-range {
                color: var(--font-color1);
                flex-direction: row;
                justify-content: flex-start;
                align-items: center;

                .ant-input {
                    margin: 0 var(--p2);
                    width: 60px;
                }
            }

            .options-block {
                display: none;
                &.show-cretiria {
                    display: block;
                }
            }
        }

        .ant-btn {
            margin-top: var(--p6);
        }
    }

    .ant-modal {
        .title {
            text-align: center;
        }
        .message {
            margin: var(--p6) 0;
            text-align: center;
        }
    }
</style>
