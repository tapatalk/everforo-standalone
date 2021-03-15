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
                src="/img/create_success.png" 
            >
        </div>

        <div class="send-title">
            {{$t('congrats')}}
        </div>

        <div class="send-desc">
            {{$t('congrats_desc')}}
        </div>

        <div
            class="confirm-footer"
        >
            <a-button
                class="primary ant-btn-primary"
                v-on:click="onConfirm"
            >
                {{$t('Customize_Group')}}
            </a-button>
            <br>
            <a-button
                class="secondary ant-btn ant-btn-link"
                v-on:click="onCancel"
            >
                {{$t('skip')}}
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
    export default class CreateGroupSuccess extends Vue {
        
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
        line-height: 21px;
        font-size: 1.1rem;
        color: var(--font-color1);
    }
    .send-success {
        width: 80px;
    }
</style>
