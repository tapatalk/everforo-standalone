<template>
    <FullScreenModal
        v-on:close="onClose"
        :contentMaxWidth="isMobile ? null:'660px'"
    >
        <template v-slot:header>
            <div
                class="title"
            >
                <span>{{$t('create_group')}}</span>
                
            </div>
        </template>

        <GroupForm
            v-on:can-save="onCanSave"
            v-on:submit-status="onSubmitStatus"
            v-on:save="(data) => onSave(data)"
            :address-popup="showAddressPopup"
            :reset-button="resetButton"
            :submit="submit"
        />
        <a-button
            v-if="canSave"
            class="button-style"
            type="primary"
            ref="save"
            v-on:click="onSubmit"
        >{{$t('create')}}
        </a-button>
        <a-button
            v-else
            :disabled="true"
            class="button-style"
            type="primary"
        >{{$t('create')}}
        </a-button>
    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {IS_MOBILE} from '@/helpers/Utils';
    import {CategoryInterface, GroupInterface} from "@/helpers/Interfaces";
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import GroupExtension from '@/components/GroupExtension.vue';
    import GroupForm from '@/components/GroupForm.vue';

    @Component({
        components: {
            FullScreenModal,
            GroupExtension,
            GroupForm,
        },
    })
    export default class GroupCreate extends Vue {

        protected resetButton:boolean = false;
        protected isMobile: boolean = IS_MOBILE;
        protected canSave: boolean = false;

        protected showAddressPopup: boolean = false;

        protected submit: boolean = false;

        @Emit('close-create-group')
        protected onClose() {

        }

        protected onCanSave(flag: boolean) {
            this.canSave = flag;
        }

        protected onSubmit() {
            this.submit = true;
        }

        protected onSubmitStatus() {
            this.submit = false;
        }

        /**
         * create new group
         */
        protected async onSave(data: FormData) {

            // this.saveBtnText = this.$t('creating') as string;

            this.canSave = false;
            this.resetButton = false;

            try{

                // const createdGroup: { group: GroupInterface, categories: CategoryInterface[] }
                //     = await this.$store.dispatch('Group/create', data);
                   const response = await this.$store.dispatch('Group/create', data);


                // if http status is not 200
                if (response.getStatus() != 200) {
                    this.$message.info(this.$t('network_error') as string);
                    this.onSubmitStatus();
                    return;
                }

                const responseCode: string = response.getCode();

                // 40002 is group taken, 40003 is group name taken, 40019 is group name contains reserved words
                if(responseCode == '40002' || responseCode == '40003' || responseCode == '40019') {
                    
                    if (responseCode == '40002') {
                        this.showAddressPopup = true;

                        this.$nextTick(() => {
                            this.showAddressPopup = false;
                        });
                    }

                    if(response.getDescription()){
                        this.$message.info(response.getDescription() as string);
                    } else {
                        this.$message.info(this.$t('network_error') as string);
                    }
                    this.onSubmitStatus();
                    return;
                }

                const createdGroup: { group: GroupInterface, categories: CategoryInterface[] } = response.getData();

                if (createdGroup && createdGroup.group && createdGroup.group.id) {

                    this.$store.commit('User/addGroup', createdGroup.group);
                    // close the modal
                    this.onClose();

                    await this.$router.push({
                        name: 'groups',
                        params: {group_name: createdGroup.group.name, type: 'create'},
                    } as unknown as RawLocation);
                }

            }catch (e){
                if(e.response &&  e.response.response.data && e.response.response.status == 422){

                    for (const error_name in e.response.response.data.errors){
                        if (e.response.response.data.errors[error_name]){
                            this.$message.error(e.response.response.data.errors[error_name] as string);
                            break;
                        }
                    }
                }
            } finally {
                this.canSave = true;
                this.resetButton = true;
                this.submit = false;
            }
        }
    }
</script>
<style lang="scss" scoped>
    .mask {
        .content {
            .title {
                margin-bottom:  0;
                border-bottom: 0;
                border-bottom: 1px solid var(--border-color5);
            }
        }
    }
    .scroll-content {
        padding-bottom: var(--p6);
    }
    .button-style {
        height: 40px;
        border-radius: 8px;
        margin-top: 24px;
        padding-left: 40px;
        padding-right: 40px;
    }
</style>