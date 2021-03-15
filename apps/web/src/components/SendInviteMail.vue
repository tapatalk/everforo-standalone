<template>
    <a-modal
        style="top: 20px;"
        v-model="visible"
        v-on:ok="onConfirm"
        v-on:cancel="onCancel"
        :centered="true"
        :closable="false"
        :footer="null"
    >
        <div
            :class="['modal-close-btn', {'mobile': isMobile}]"
            v-on:click="onCancel"
        >
            <Icons
                type="chacha"
            />
        </div>

        <div class="ant-ban-image">
             <img
                class='send-success'
                src="/img/send_success.png" 
                alt=""
            >
        </div>

        <div class="send-text">
            {{sendInviteMessage}}
        </div>

        <div
            class="confirm-footer"
        >
            <a-button
                class="primary ant-btn-primary"
                v-on:click="onConfirm"
            >
                {{$t('ok')}}
            </a-button>
        </div>
    </a-modal>
</template>
<script lang="ts">
    import { IS_MOBILE } from '@/helpers/Utils';
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';


    @Component({
        components: {
        }
    })
    export default class SendInviteMail extends Vue {
        @Prop()
        public showSendInviteMail!: boolean;

        @Prop()
        public sendInviteMessage!: string;
        
        protected visible: boolean = true;
        protected isMobile: boolean = IS_MOBILE;

        @Emit('confirm')
        protected onConfirm() {

        }

        @Emit('cancel')
        protected onCancel() {

        }
    }
</script>
<style lang="scss" scoped>
    .confirm-footer {
        text-align: center;
        padding: var(--p4) 0;
        padding-top: 24px;
        .ant-btn {
            @include capitalize();
            height: 40px;
            border-radius: 8px;

            &.primary {
                min-width: 110px;
                box-shadow: $popup-primary-btn-shadow;
            }
        }
    }
     .ant-ban-image {
        margin-top: 40px;
        text-align: center;
    }
    .send-text {
        text-align: center;
        font-size: $font-size3;
        margin-top: var(--p5);
        margin-bottom: var(--p1);
        font-weight: 500;
        color: var(--font-color1);
    }
    .send-success {
        width: 80px;
    }
</style>
