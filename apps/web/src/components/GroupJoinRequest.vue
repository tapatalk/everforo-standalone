<template>
    <FullScreenModal
        :content-max-width="isMobile? null:'660px'"
        :content-height="'auto'"
        :max-height="'70%'"
        :top="'20%'"
        :cha="true"
    >
        <template v-slot:header>
            <div
                v-if="title"
                class="title"
            >{{title}}</div>
        </template>

        <a-row>
            <div class="label">{{$t('your_personal_information')}}</div>
        </a-row>
        <a-row
            class="item"
        >
            <div
                class="desc"
            >
                {{$t('your_personal_information_desc')}}
            </div>
        </a-row>
        <a-row
            class='item'
        >
            <a-textarea
                :auto-size="{ minRows: 5, maxRows: 8 }"
                :maxLength="maxLength"
                class="textarea-text"
                v-model="join_msg"
            />
            <div
                class='word-count'
            >{{join_msg.length}} / {{maxLength}}</div>
        </a-row>
        <a-button
            type="primary"
            v-on:click="onSave"
            class="button-style"
        >
            {{$t('send')}}
        </a-button>
    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import {FlagInterface} from '@/helpers/Interfaces';
    import TransferAdmin from '@/components/TransferAdmin.vue';
    import QuestionMark from '@/components/QuestionMark.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import Username from '@/components/Username.vue';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            TransferAdmin,
            QuestionMark,
            FullScreenModal,
            UserAvatar,
            Username,
        },
    })
    export default class GroupJoinRequest extends Vue {

        protected title: string = this.$t('requerst_to_join') as string;
        protected isMobile: boolean = IS_MOBILE;
        protected showTransferConfirm: boolean = false;
        protected join_msg = '';
        protected changeAdminInfo: any = '';
        protected sendTransferMessage: string = '';
        protected adminId: number = 0;

        protected maxLength=140;

        protected created()
        {
        }

        protected onSave()
        {
            var formData = new FormData;
            formData.append('group_id', this.$store.state.Group.id);
            formData.append('user_id', this.$store.state.User.id);
            formData.append('join_msg', this.join_msg + '');
            if (this.join_msg.length > 140) {
                this.$message.info(this.$t('max_error') as string);
                return;
            }
            
            this.$store.dispatch('GroupPrivacy/joinRequest', formData)
                .then((response: any) => {
                    if (response.response.data.code == '40001') {
                        this.$message.info(this.$t('join_already_member') as string);
                        this.onCancel();
                        return;
                    }
                    this.$message.info(this.$t('request_submitted') as string);
                    this.onCancel();
                });
        }

        protected handleChange() {
            this.showTransferConfirm = false;
            this.onCancel();
        }

        @Emit('close-invite-member')
        protected onCancel() {

        }

        
    }
</script>
<style lang="scss" scoped>
    .label {
        @include form_label;
        padding-top: var(--p6);
        padding-bottom: var(--p2);
        button {
            width: 100px;
            border-radius: 4px;
        }
    }

    .item {
        textarea {
            line-height: 25px;
        }
        .desc {
            font-size: 0.9rem;
            color: var(--desc-color);
        }
        margin-bottom: 20px;
        .word-count {
            position: absolute;
            bottom: var(--p2);
            right: var(--p4);
            @include description_font;
            line-height:1;
        }
    }

    .button-style {
        margin-top: 20px;
        padding-left: 40px;
        padding-right: 40px;
    }
.mask .content .title {
        margin: 0 var(--p8) 0 0;
    }
    .textarea-text {
           line-height: 25px;
    }
</style>
