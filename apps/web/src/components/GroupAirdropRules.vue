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
        <div class="airdrop-rules">
            <div class="title-box">
                {{$t('customize_rules')}}
            </div>

            <a-table
                :columns="columns"
                :data-source="data"
                :loading="loading"
                :pagination="false"
                rowKet="name"
            >
                <template 
                    slot="action"
                    slot-scope="record"
                >
                    <div 
                        class="actions"
                        v-if="record.exec_status == 0"
                    >
                        <a
                            v-on:click="() => onResume(record)"
                        >{{$t('resume')}}</a>
                    </div>
                    <div
                        class="actions"
                        v-else
                    >
                        <a
                            v-on:click="() => onEdit(record)"
                        >{{$t('edit')}}</a>
                        <a
                            v-on:click="() => onDelete(record)"
                        >{{$t('delete')}}</a>
                        <a
                            v-on:click="() => onPause(record)"
                        >{{$t('pause')}}</a>
                    </div>
                </template>
            </a-table>

            <a-button
                type="primary"
                v-on:click="airdropRuleForm"
            >
                {{$t('add_rules')}}
            </a-button>

            <div class="desc">
                {{$t('airdrop_rules_desc')}}
            </div>
        </div>
        <GroupAirdropRulesForm
            v-if="showAirdropRuleForm"
            :default-value="defaultAirdropRule"
            v-on:new-airdrop-rules="onNewAirdropRule"
            v-on:edit-airdrop-rules="onEditAirdropRule"
            v-on:close-airdrop-rules-form="onAirdropRuleFormClose"
        />
        <ConfirmModal
            v-if="showDeleteRule"
            :reverse-button="true"
            :yes-text="$t('delete')"
            :no-text="$t('not_now')"
            v-on:confirm="confirmDeleteRule"
            v-on:cancel="showDeleteRule = false"
        >
            <div class="title">
                <QuestionMark/>
            </div>
            <div
                class="message"
            >
                <div><strong>Are you sure to delete this Airdrop rule?</strong></div>
            </div>
        </ConfirmModal>
    </a-modal>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue, Watch} from 'vue-property-decorator';
    import {AirdropRuleInterface} from '@/helpers/Interfaces';
    import {Response} from '@/http/Response';
    import {IS_MOBILE, thousandComma} from '@/helpers/Utils';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import GroupAirdropRulesForm from '@/components/GroupAirdropRulesForm.vue';
    import QuestionMark from '@/components/QuestionMark.vue';

    @Component({
        components: {
            ConfirmModal,
            GroupAirdropRulesForm,
            QuestionMark,
        },
    })
    export default class GroupAirdropRules extends Vue {

        @Prop()
        protected sourceAirdropList!: any[];

        protected isMobile: boolean = IS_MOBILE;

        protected model: boolean = true;
        protected showAirdropRuleForm: boolean = false;
        protected defaultAirdropRule: AirdropRuleInterface = {} as AirdropRuleInterface;

        protected data: any[] = [];

        protected loading = true;

        protected showDeleteRule: boolean = false;
        protected deleteRuleId: number = 0;

        protected columns = [
            {
                title: '',//this.$t('rule_name'),
                dataIndex: 'rule_name',
                key: 'rule_name',
            },
            {
                title: '',//this.$t('days30_dropped'),
                dataIndex: 'total_count',
                key: 'total_count',
            },
            {
                title: '',//this.$t('actions'),
                key: 'email',
                scopedSlots: {customRender: 'action'}
            },
        ];

        protected created() {
            this.columns[0].title = this.$t('rule_name') as string;
            this.columns[1].title = this.$t('days30_dropped') as string;
            this.columns[2].title = this.$t('actions') as string;
        }

        @Watch('sourceAirdropList', {immediate: true})
        protected onListChange(val: any[]) {
            
            if (val && val.length) {

                this.data = [];
                
                for (let i in this.sourceAirdropList) {
                    this.data.push(this.sourceAirdropList[i]);
                }
                
                this.loading = false;
            } else {
                this.data = [];

                this.loading = false;
            }
        }


        protected onEdit(record: AirdropRuleInterface) {
            this.showAirdropRuleForm = true;

            this.defaultAirdropRule = record;
        }

        protected onDelete(rule: AirdropRuleInterface) {
            this.showDeleteRule = true;
            this.deleteRuleId = rule.id;
        }

        protected confirmDeleteRule() {
            const data = new FormData;

            data.append('airdrop_job_id', this.deleteRuleId + '');

            this.$store.dispatch('AirdropRule/delete', data)
            .then(() => {
                this.showDeleteRule = false;
                this.onDeleteAirdrop(this.deleteRuleId);
            });
        }

        @Emit('delete_airdrop')
        protected onDeleteAirdrop(id: number): number {
            return id;
        }

        protected onPause(rule: AirdropRuleInterface) {

            const data = new FormData;


            data.append('airdrop_job_id', rule.id + '');

            this.$store.dispatch('AirdropRule/pause', data)
            .then(() => {
                rule.exec_status = 0;
            });
        }

        protected onResume(rule: AirdropRuleInterface) {

            const data = new FormData;

            data.append('airdrop_job_id', rule.id + '');

            this.$store.dispatch('AirdropRule/resume', data)
            .then(() => {
                rule.exec_status = 1;
            });
        }   

        protected airdropRuleForm() {
            this.defaultAirdropRule = {} as AirdropRuleInterface;
            this.showAirdropRuleForm = true;
        }

        protected onNewAirdropRule(airdropRule: AirdropRuleInterface) {
            this.data.unshift(airdropRule);

            this.airdropRuleChange();
        }

        protected onEditAirdropRule(airdropRule: AirdropRuleInterface) {
            for (let i in this.data) {
                
                if (airdropRule.id == this.data[i].id) {
                    Object.assign(this.data[i], airdropRule);
                }
            }

            this.airdropRuleChange();
        }


        protected onAirdropRuleFormClose() {
            this.showAirdropRuleForm = false;
        }

        @Emit('close-airdrop-rules')
        protected onClose() {

        }

        @Emit('airdrop-rules-change')
        protected airdropRuleChange() {
            return this.data;
        }
        
    }
</script>
<style lang="scss" scoped>
    .airdrop-rules {
        .title-box {
            @include modal_title_box;
        }

        .ant-table {
            .actions {
                a {
                    @include capitalize;
                    text-decoration: underline;
                    margin-right: var(--p2);
                }
            }
        }

        .ant-btn {
            margin-top: var(--p6);
        }

        .desc {
            @include description_font;
            font-size: $upload-desc-font-size;
            line-height: 1.3rem;
            //line-height: 20px;
            margin-top: var(--p4);
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
