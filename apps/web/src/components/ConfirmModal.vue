<template>
    <a-modal
        v-model="visible"
        v-on:ok="onConfirm"
        v-on:cancel="onCancel"
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
        <slot></slot>
        <div
            v-if="!reverseButton"
            class="confirm-footer"
        >
            <a-button
                type="link"
                class="secondary"
                v-on:click="onCancel"
            >
                {{noText ? noText : $t('no')}}
            </a-button>
            <a-button
                type="link"
                class="primary"
                v-on:click="onConfirm"
            >
                {{yesText ? yesText : $t('yes')}}
            </a-button>
        </div>
        <div
            v-else
            class="confirm-footer"
        >
            <a-button
                type="link"
                class="secondary"
                v-on:click="onConfirm"
            >
                {{yesText ? yesText : $t('yes')}}
            </a-button>
            <a-button
                type="link"
                class="primary"
                v-on:click="onCancel"
            >
                {{noText ? noText : $t('no')}}
            </a-button>
        </div>
        <div
            class="desc"
        >
            <slot name="sub_btn_desc"></slot>
        </div>
    </a-modal>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {

        },
    })
    export default class ConfirmModal extends Vue {

        @Prop()
        public reverseButton!: boolean;
        @Prop()
        public yesText!: string;
        @Prop()
        public noText!: string;
        
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

        .ant-btn {
            @include capitalize();
            padding: var(--p4);
            height: auto;
            border-radius: $border-radius1;

            &.secondary {
                min-width:  110px;
                margin-right: var(--p8);
                color: var(--font-color2);
            }
            
            &.primary {
                min-width: 110px;
                background: var(--daiload-color);
                color: var(--font-color7);
                box-shadow: $popup-primary-btn-shadow;
            }
        }
    }

    .desc {
        @include description_font;
        font-size: $upload-desc-font-size;
        line-height: 1.3rem;
        text-align: center;
        width: 80%;
        margin: 0 auto;
        margin-top: 8px;
    }
</style>