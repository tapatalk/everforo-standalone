<template>
    <FullScreenModal
        v-on:close="onCancel"
        :content-max-width="isMobile? null:'520px'"
        :content-height="'auto'"
        :max-height="'70%'"
        :top="'20%'"
    >
        <template v-slot:header>
            <div
                v-if="title"
                class="title"
            >{{title}}</div>
        </template>
        <div
            v-on:click="onCancel"
            :class="['modal-close-btn']"
        >
            <Icons
                type="chacha"
            />
        </div>

        <a-row>
            <div class="label">{{$t('email_address')}}</div>
        </a-row>
        <a-row
            class="item"
        >
            <a-input
                :placeholder="$t('email_example')"
                size="large"
                id = "id"
                :maxLength="ADDRESS_MAX"
                v-model="email"
            />
        </a-row>

        <a-row>
            <div class="label">{{$t('customize_your_invite_message')}}</div>
        </a-row>
        <a-row
            class="item"
        >
            <a-textarea 
                :auto-size="{minRows: 4}"
                :maxLength="MESSAGE_MAX"
                :placeholder="$t('customize_example')"
                v-model="message"
            />
        </a-row>
        <a-row
        >
            <div class="label">
                <a-button v-on:click="onSubmit" type="primary">
                    {{$t('send')}}
                </a-button>
            </div>
        </a-row>
        <SendInviteMail
            v-if="showSendMailConfirm"
            v-on:confirm="onInviteSuccessCancel"
            v-on:cancel="onInviteSuccessCancel"
            :send-invite-message = "sendInviteMessage"
        />
    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import {FlagInterface} from '@/helpers/Interfaces';
    import SendInviteMail from '@/components/SendInviteMail.vue';
    import QuestionMark from '@/components/QuestionMark.vue';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            SendInviteMail,
            QuestionMark,
            FullScreenModal,
        },
    })
    export default class InviteMember extends Vue {

        protected title: string = this.$t('invite_other') as string;
        protected isMobile: boolean = IS_MOBILE;
        readonly MESSAGE_MAX: number = 140;
        readonly ADDRESS_MAX: number = 100;
        protected email: string = '';
        protected message: string = '';
        protected showSendMailConfirm: boolean = false;

        get sendInviteMessage():string
        {
            return this.$t("invitation_has_send", {email : this.email}) as string;
        }

        protected mounted () {
            var input = document.getElementById('id') as HTMLDivElement;
            input.focus();
        }

        protected onSubmit(): void {
            if (!this.email) {
                this.$message.error(this.$t('invite_email_valid') as string);
                return;
            }

            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if (!re.test(this.email.toLowerCase())) {
                this.$message.error(this.$t('email_invalid') as string);
                return;
            }
            this.$store.dispatch('Members/sendMail', {email:this.email,message:this.message})
            .then((response) => {
                const data = response.response.data;
                if (data && data.data && data.data.success) {
                    //send invite mail
                    this.showSendMailConfirm = true;
                } else if (data.code == '40001') {
                    this.$message.error(this.$t('invite_follow_valid') as string);
                } else if (data.code == '40002') {
                    this.$message.error(this.$t('invite_followed_valid') as string);
                } else if (data.code == '40003') {
                    this.$message.error(this.$t('invite_invited_valid') as string);
                } else if (data.code == '40004') {
                    this.$message.error(this.$t('invite_limit_valid') as string);
                }
            });
        }

        protected onInviteSuccessCancel() {
                this.showSendMailConfirm = false;
                this.onCancel();
        }

        @Emit('close-invite-member')
        protected onCancel() {

        }
        
    }
</script>
<style lang="scss" scoped>
    .mask {
        .content {
            .title {
                text-align: center;
                padding: var(--p4) 0;
                margin: 0 var(--p8) 0 0;
            }
        }
    }
    

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
    }

    .confirm-footer {
        button {
            height: 32px;
        }
    }

</style>
