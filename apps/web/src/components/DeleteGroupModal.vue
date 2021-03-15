<template>
    <ConfirmModal
            :reverse-button="true"
            :yes-text="$t('Yes')"
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
            <div><strong>{{$t('delete_group_confirm')}}</strong></div>
        </div>
        <div
            class="message message2"
        >
            {{$t('delete_group_confirm_desc')}}
        </div>
    </ConfirmModal>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {GroupInterface} from '@/helpers/Interfaces';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import QuestionMark from '@/components/QuestionMark.vue';

    @Component({
        components: {
            ConfirmModal,
            QuestionMark,
        },
    })
    export default class DeleteGroupModal extends Vue {

        @Prop()
        public defaultGroup!: GroupInterface;

        protected visible: boolean = true;

        protected disabled: boolean = false;

        protected onSubmit(): void {

            if (this.disabled) {
                return;
            }

            this.disabled = true;

            const data = new FormData();

            const group_id = this.defaultGroup.id;

            data.append('group_id', group_id + '' );

            this.$store.dispatch('Group/delete', data)
                .then((response: any) => {

                    if(response.getCode() != '20000'){
                        if(response.getDescription() != ''){
                            this.$message.info(response.getDescription() as string);
                        } else {
                            this.$message.info(this.$t('network_error') as string);
                        }
                    } else {

                        this.$store.commit('User/deleteGroup', group_id);
                        this.$store.commit('ThreadList/setThreadList', []);
                        this.$message.success(this.$t('delete_group_success') as string, 2, () => {
                                                this.$router.push({
                                                    name: 'home',
                                                } as unknown as RawLocation);
                                            });
                    }

                })
                .finally(() => {
                    // restore the flag
                    this.disabled = false;
                    this.onCancel();
                });

        }

        protected onCancel() {
            this.deleteGroupFinished();
        }

        @Emit('delete-group-finish')
        protected deleteGroupFinished() {

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
    }

    .message2 {
        @include description_font;
        font-size: $upload-desc-font-size;
    }

</style>
