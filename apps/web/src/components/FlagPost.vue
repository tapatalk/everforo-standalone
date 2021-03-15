<template>
    <ConfirmModal
        :reverse-button="true"
        :yes-text="$t('submit')"
        :no-text="$t('cancel')"
        v-on:confirm="onSubmit"
        v-on:cancel="onCancel"
    >
        <div class="title">
            <QuestionMark/>
            <p><strong>{{$t('flag_post_confirm_title')}}</strong></p>
        </div>
        <div
            class="options"
            v-for="reason in reasonMapping"
            :key="reason.value"
        >
            <div
                :class="{'selected': seletedReason == reason.value}"
                v-on:click="selectReason(reason.value)"
            >{{$t(reason.text)}}</div>
        </div>
        <template v-slot:sub_btn_desc>
            <div
                v-html="$t('support_email')"
            >
            </div>
        </template>
    </ConfirmModal>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import {FLAG_REASON_MAPPING} from '@/helpers/Utils';
    import {FlagInterface} from '@/helpers/Interfaces';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import QuestionMark from '@/components/QuestionMark.vue';

    @Component({
        components: {
            ConfirmModal,
            QuestionMark,
        }
    })
    export default class FlagPost extends Vue {

        private reasonMapping = FLAG_REASON_MAPPING;

        protected seletedReason: number = 0;

        protected disabled: boolean = false;

        protected selectReason(reason_value: number) {
            this.seletedReason = reason_value;
        }

        protected onSubmit() {
            
            if (this.disabled) {
                return;
            }

            if (!this.seletedReason) {
                this.$message.error(this.$t('select_reason') as string);
                return;
            }

            this.disabled = true;

            const data = new FormData();

            data.append('post_id', this.$store.state.Flag.flagPostId + '');
            data.append('reason', this.seletedReason + '');
            
            this.$store.dispatch('Flag/submit', data)
            .then((flag: FlagInterface | any) => {
                // show block user popup
                if (flag && flag.response && flag.response.data
                && flag.response.data.code == 40003){
                    this.$store.commit('Flag/setFlagPostId', 0);
                    this.disabled = false;
                    this.$message.error(this.$t('join_error') as string);
                } else if (flag.poster && flag.poster.user_id) {
                    this.$store.commit('Flag/setBlockUser', flag);
                }
                
            })
            .finally(() => {
                this.$store.commit('Flag/setFlagPostId', 0);
            });
        }

        protected onCancel() {
            this.$store.commit('Flag/setFlagPostId', 0);
        }
    }
</script>
<style lang="scss" scoped>

    .title {

        text-align: center;
        padding: var(--p4) 0 0;

        p {
            @include popup_title_font();
            margin-top: var(--p4);
            // font-size: $font-size2;
        }
    }

    .options {
        position: relative;

        div {
            @include content_font();
            $h: 60px;
            width: 80%;
            height: $h;
            line-height: $h;
            margin: 0 auto var(--p4);
            text-align: center;
            border: 1px solid var(--border-color3);
            cursor: pointer;
            transition: all .3s;
            border-radius: $border-radius1;
            
            &:hover, &.selected {
                color: var(--theme-color);
                border-color: var(--theme-color);
            }
        }
    }
</style>
