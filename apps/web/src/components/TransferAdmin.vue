<template>
    <a-modal
        style="top: 20px"
        v-model="visible"
        v-on:ok="onConfirm"
        v-on:cancel="onCancel"
        :centered="true"
        :closable="false"
        :footer="null"
        width="660px"
        
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
                src="/img/transfer_admin.png" 
            >
        </div>

        <div class="send-title">
            {{$t('group_transfer')}}
        </div>

        <div class="send-desc">
            {{transferMessage}}
        </div>

        <div
            class="confirm-footer"
        >
            <a-button
                class="primary ant-btn-primary"
                v-on:click="onConfirm"
            >
                {{$t('Yes')}}
            </a-button>
            <br>
            <a-button
                class="secondary ant-btn ant-btn-link"
                v-on:click="onCancel"
            >
                {{$t('cancel')}}
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
    export default class TransferAdmin extends Vue {
        @Prop()
        public transferMessage!: string;

        @Prop()
        public adminId!: number;
        
        protected visible: boolean = true;
        protected isMobile: boolean = IS_MOBILE;

        @Emit('confirm')
        protected onConfirm() {
            this.$store.dispatch('GroupAdmin/changeOwner',  {admin_id: this.adminId})
                    .then((response: Response) => {
                        window.location.reload(true);
                    });
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
        .secondary {
            color: var(--font-color2);
        }
    }
     .ant-ban-image {
        text-align: center;
        margin-top: 24px;
    }
    .send-title {
        text-align: center;
        font-size: 1.3rem;
        margin-top: var(--p5);
        margin-bottom: var(--p1);
        font-weight: 500;
        color: var(--font-color1);
    }
    .send-desc {
        text-align: center;
        @include description_font;
        font-size: $upload-desc-font-size;
        margin-top: var(--p3);
        margin-bottom: var(--p1);
        line-height: 16px;
        font-size: 1.1rem;
        color: var(--font-color1);
    }
    .send-success {
        width: 80px;
    }
</style>
