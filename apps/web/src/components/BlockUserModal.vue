<template>
    <ConfirmModal
        :reverse-button="true"
        :yes-text="$t('block')"
        :no-text="$t('not_now')"
        v-on:confirm="onSubmit"
        v-on:cancel="onCancel"
    >
        <div class="title">
            <QuestionMark/>
        </div>
        <div
            class="message"
        >
            <div><strong>{{msg1}}</strong></div>
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
    import {FlagInterface} from '@/helpers/Interfaces';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import QuestionMark from '@/components/QuestionMark.vue';

    @Component({
        components: {
            ConfirmModal,
            QuestionMark,
        },
    })
    export default class BlockUserModal extends Vue {

        protected visible: boolean = true;

        protected msg1: string = '';
        protected msg2: string = '';

        protected disabled: boolean = false;

        // the post get flagged, with the poster info 
        get flag(): FlagInterface {
            return this.$store.state.Flag.blockUser;
        }

        protected created() {
            if (this.flag.poster && this.flag.poster!.user_id) {
                this.msg1 = this.$t('confirm_block_user', {username: this.flag.poster!.name}) as string;
            }
        }

        protected onSubmit(): void {

            if (this.disabled) {
                return;
            }

            this.disabled = true;
            
            const data = new FormData();

            data.append('block_user_id', this.flag.poster!.user_id + '');
            
            this.$store.dispatch('Flag/blockUser', data)
            .then((blocked_users: number[]) => {
                // todo, show block user popup
                this.$store.commit('User/updateBlockedUser', blocked_users);
            })
            .finally(() => {
                this.$store.commit('Flag/setBlockUser', undefined);
            });
        }

        protected onCancel() {
            this.$store.commit('Flag/setBlockUser', undefined);
        }
        
    }
</script>
<style lang="scss" scoped>
    .title {
        text-align: center;
        padding: var(--p4) 0;
    }
        
    .message {
        text-align: center;

        div {
            @include content_font;
        }

        // p:first-child {
        //     margin: var(--p4) 0;
        //     line-height: 1.5rem;
        // }
    }
</style>
